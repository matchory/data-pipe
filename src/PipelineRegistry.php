<?php

declare(strict_types=1);

namespace Matchory\DataPipe;

use Matchory\DataPipe\DependencyGraph\DependencyGraph;
use Matchory\DataPipe\Events\BeforeNodeRegistrationEvent;
use Matchory\DataPipe\Exceptions\DependencyGraph\CircularDependencyException;
use Matchory\DataPipe\Exceptions\DependencyGraph\DependencyNotFoundException;
use Matchory\DataPipe\Interfaces\CollectorInterface;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface;
use Matchory\DataPipe\Interfaces\TransformerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

use function array_filter;
use function array_merge;
use function strtolower;
use function usort;

class PipelineRegistry
{
    /** @var PipelineNodeInterface[] $nodes */
    protected array $nodes = [];

    protected DependencyGraph $dependencyGraph;

    protected EventDispatcherInterface $dispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        DependencyGraph $dependencyGraph
    ) {
        $this->dependencyGraph = $dependencyGraph;
        $this->dispatcher = $eventDispatcher;
    }

    /**
     * Resolves the pipeline to a fixed order of steps
     *
     * @return array<int, PipelineNodeInterface>
     * @throws CircularDependencyException
     * @throws DependencyNotFoundException
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function resolvePipeline(): array
    {
        // Retrieve the enriching nodes in the order they were added by the DI
        // container and thus unstructured.
        // That is due to the order loaded depending on the naming of the PHP
        // classes, or some other DI optimization, doesn't matter here.
        // We will fix this order in the following steps. To make it easier to
        // understand what's happening, we'll reuse these three example sources:
        //  - S1: FooSource, Cost 5
        //  - S2: BarSource, Cost 1, depends on P1
        //  - S3: BazSource, Cost 2
        //
        // Starting off, the list will look like this:
        //     [ S2, S3, S1 ]
        $collectors = $this->getCollectors();

        // Sort sources by their cost, in ascending order. While dependency
        // resolution might shuffle that order a bit, in general it will make
        // sure less expensive sources are queried first.
        // Our list looks like the following now:
        //     [ S2 (Cost 1), S3 (Cost 2), S1 (Cost 5) ]
        usort($collectors, static fn(
            CollectorInterface $a,
            CollectorInterface $b
        ): int => $a->getCost() <=> $b->getCost());

        $transformers = $this->getTransformers();

        // Append post processors to the data sources to end up with the raw
        // pipeline node list before resolving inter-node dependencies.
        // This yields the following list:
        //     [ S2, S3, S1, P1, P2, P3 ]
        $nodes = array_merge($collectors, $transformers);

        // Connect all nodes to the dependency graph.
        foreach ($nodes as $node) {
            $this->dependencyGraph->connect($node);
        }

        // Resolve the graph to an actual array of nodes. This unravels all
        // dependencies and sorts the list into its final order:
        //     [ S3, P1 (dependency of S2), S2, S1, P2, P3  ]
        return $this->dependencyGraph->resolve();
    }

    /**
     * Adds a node to the pipeline
     *
     * @param PipelineNodeInterface $node
     */
    public function addNode(PipelineNodeInterface $node): void
    {
        $event = new BeforeNodeRegistrationEvent($node, $this);

        $this->dispatcher->dispatch($event);

        if ($event->shouldSkip()) {
            return;
        }

        $this->nodes[] = $node;
    }

    /**
     * @return CollectorInterface[]
     */
    public function getCollectors(): array
    {
        return array_filter($this->nodes, static fn(
            PipelineNodeInterface $node
        ) => $node instanceof CollectorInterface);
    }

    /**
     * @return TransformerInterface[]
     */
    public function getTransformers(): array
    {
        return array_filter($this->nodes, static fn(
            PipelineNodeInterface $node
        ) => $node instanceof TransformerInterface);
    }

    /**
     * @return PipelineNodeInterface[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function resolveCollector(
        string $name
    ): CollectorInterface|null {
        $name = strtolower($name);

        foreach ($this->getCollectors() as $node) {
            if ((string)$node === $name) {
                return $node;
            }
        }

        return null;
    }
}
