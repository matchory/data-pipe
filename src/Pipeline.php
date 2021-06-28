<?php

declare(strict_types=1);

namespace Matchory\DataPipe;

use JetBrains\PhpStorm\Pure;
use Matchory\DataPipe\Events\AfterCommitEvent;
use Matchory\DataPipe\Events\AfterNodeEvent;
use Matchory\DataPipe\Events\AfterPipelineItemEvent;
use Matchory\DataPipe\Events\BeforeChangeApplicationEvent;
use Matchory\DataPipe\Events\BeforeCommitEvent;
use Matchory\DataPipe\Events\BeforeNodeEvent;
use Matchory\DataPipe\Events\BeforePipelineItemEvent;
use Matchory\DataPipe\Events\NodeFailedEvent;
use Matchory\DataPipe\Events\NodeRedundantEvent;
use Matchory\DataPipe\Events\NodeSucceededEvent;
use Matchory\DataPipe\Events\NodeTooExpensiveEvent;
use Matchory\DataPipe\Interfaces\CollectorInterface;
use Matchory\DataPipe\Interfaces\PayloadInterface;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface;
use Matchory\DataPipe\Payload\Payload;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

use function array_diff;
use function count;
use function is_null;
use function microtime;

class Pipeline
{
    /**
     * @param array<int, PipelineNodeInterface> $nodes
     * @param EventDispatcherInterface          $dispatcher
     */
    #[Pure]
    public function __construct(
        protected array $nodes,
        protected EventDispatcherInterface $dispatcher
    ) {
    }

    /**
     * Processes the pipeline and applies all nodes to the given payload
     * iterator.
     *
     * @param Payload  $payload
     * @param int|null $maximumCost
     *
     * @return PayloadInterface
     * @throws Throwable
     */
    public function process(
        PayloadInterface $payload,
        int|null $maximumCost = null
    ): PayloadInterface {
        $context = new PipelineContext($payload);

        $this->dispatcher->dispatch(new BeforePipelineItemEvent(
            $context,
            $this->nodes,
            $maximumCost
        ));

        // Apply data sources to the context
        foreach ($this->nodes as $node) {
            $context = $this->apply(
                $node,
                $context,
                $maximumCost
            );
        }

        $payload = $this->commitChanges($context);

        $this->dispatcher->dispatch(new AfterPipelineItemEvent(
            $context,
            $this->nodes,
            $maximumCost
        ));

        return $payload;
    }

    /**
     * @param PipelineNodeInterface $node
     * @param PipelineContext       $context
     * @param int|null              $maximumCost
     *
     * @return PipelineContext
     * @throws Throwable
     */
    protected function apply(
        PipelineNodeInterface $node,
        PipelineContext $context,
        int|null $maximumCost = null
    ): PipelineContext {
        $event = new BeforeNodeEvent(
            $node,
            $context,
            $this->nodes
        );

        /** @var BeforeNodeEvent $event */
        $event = $this->dispatcher->dispatch($event);

        if ($event->shouldSkip()) {
            return $context;
        }

        if ($node instanceof CollectorInterface) {
            // If the source is too expensive, we skip it
            if ($maximumCost && $node->getCost() > $maximumCost) {
                $event = new NodeTooExpensiveEvent(
                    $node,
                    $context,
                    $maximumCost,
                );

                /** @var NodeTooExpensiveEvent $event */
                $event = $this->dispatcher->dispatch($event);

                // Unless the event advises to use the pipeline regardless of
                // it being too expensive, we skip it. This hook allows an
                // external assessment of the risk situation taking more
                // sophisticated mechanisms into account.
                if ($event->shouldSkip()) {
                    return $context;
                }
            }

            // If this is an expensive source, and it doesn't provide any fields
            // we're still missing, querying it would be redundant. Unless an
            // event listener advises to query it regardless, we skip it.
            if (
                $node->getCost() > 0 &&
                ! $this->providesMissingFields($node, $context)
            ) {
                $event = new NodeRedundantEvent(
                    $node,
                    $context
                );

                /** @var NodeRedundantEvent $event */
                $event = $this->dispatcher->dispatch($event);

                if ($event->shouldSkip()) {
                    return $context;
                }
            }
        }

        // Capture the start time of the node pipe for statistical purposes.
        // The timing values are only available via events.
        $startTime = microtime(true);

        /**
         * To make Psalm happy
         *
         * @noinspection PhpUnusedLocalVariableInspection
         */
        $duration = 0.0;
        $exception = null;

        try {
            // Scope the context to the next node
            $context = $context->forNode($node);

            // Try to pipe the context through the node
            $context = $node->pipe($context);

            // Measure execution duration
            $duration = microtime(true) - $startTime;

            $this->dispatcher->dispatch(new NodeSucceededEvent(
                $node,
                $context,
                $duration
            ));
        } catch (Throwable $exception) {
            $duration = microtime(true) - $startTime;
            $event = new NodeFailedEvent(
                $exception,
                $node,
                $context,
                $duration
            );

            /** @var NodeFailedEvent $event */
            $event = $this->dispatcher->dispatch($event);

            // If an event listener advises to continue regardless of the
            // error, we continue. Otherwise, we'll throw the exception,
            // probably crashing the process (on purpose!).
            if ($event->shouldSkip()) {
                return $context;
            }

            throw $exception;
        } finally {
            $this->dispatcher->dispatch(new AfterNodeEvent(
                $node,
                $context,
                $this->nodes,
                $duration,
                is_null($exception)
            ));
        }

        return $context;
    }

    /**
     * Commits all changes
     *
     * @param PipelineContext $context
     *
     * @return PayloadInterface
     */
    protected function commitChanges(PipelineContext $context): PayloadInterface
    {
        // Resolve the proposed changes with the highest confidence level
        $changeSet = $context->getProposedChanges()->resolve();
        $payload = $context->getPayload();

        $event = new BeforeCommitEvent(
            $context,
            $changeSet,
            $payload
        );

        $this->dispatcher->dispatch($event);

        // Override the change set with the potentially updated change set from
        // the event instance
        $changeSet = $event->getChangeSet();

        // Apply all changes
        foreach ($changeSet as $proposedChange) {
            $event = new  BeforeChangeApplicationEvent(
                $context,
                $proposedChange
            );

            $this->dispatcher->dispatch($event);

            if ($event->shouldSkip()) {
                continue;
            }

            $payload = $proposedChange->apply($payload);
        }

        $this->dispatcher->dispatch(new AfterCommitEvent(
            $context,
            $changeSet,
            $payload
        ));

        return $payload;
    }

    protected function providesMissingFields(
        CollectorInterface $source,
        PipelineContext $context
    ): bool {
        $provided = $source->getProvidedFields();
        $missing = $context->getMissingFields();

        return count(array_diff($missing, $provided)) > 0;
    }
}
