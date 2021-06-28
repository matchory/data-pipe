<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Events;

use JetBrains\PhpStorm\Pure;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface;
use Matchory\DataPipe\PipelineContext;
use Symfony\Component\EventDispatcher\GenericEvent;

use function array_search;
use function array_slice;

/**
 * @property array<int, PipelineNodeInterface> $nodes
 */
class BeforeNodeEvent extends GenericEvent
{
    use SkipTrait;

    /**
     * @param PipelineNodeInterface   $node
     * @param PipelineContext         $context
     * @param array<int, PipelineNodeInterface> $nodes
     */
    #[Pure]
    public function __construct(
        protected PipelineNodeInterface $node,
        protected PipelineContext $context,
        protected array $nodes
    ) {
        parent::__construct($context);
    }

    #[Pure]
    public function getNode(): PipelineNodeInterface
    {
        return $this->node;
    }

    #[Pure]
    public function getContext(): PipelineContext
    {
        return $this->context;
    }

    #[Pure]
    public function getAllNodes(): array
    {
        return $this->nodes;
    }

    /**
     * Retrieves the previous node before the current. If the current node is
     * the first node, will return `null`.
     *
     * @return PipelineNodeInterface|null
     */
    #[Pure]
    public function getPreviousNode(): PipelineNodeInterface|null
    {
        $index = $this->getNodeIndex();

        if ($index === null) {
            return null;
        }

        return $this->nodes[$index - 1] ?? null;
    }

    /**
     * Retrieves the next node after the current. If the current node is the
     * last node, will return `null`.
     *
     * @return PipelineNodeInterface|null
     */
    #[Pure]
    public function getNextNode(): PipelineNodeInterface|null
    {
        $index = $this->getNodeIndex();

        if ($index === null) {
            return null;
        }

        return $this->nodes[$index + 1] ?? null;
    }

    /**
     * Retrieves all completed nodes
     *
     * @return array
     */
    #[Pure]
    public function getCompletedNodes(): array
    {
        $index = $this->getNodeIndex();

        if ($index === null) {
            return [];
        }

        return array_slice(
            $this->nodes,
            0,
            $index
        );
    }

    /**
     * Retrieves all pending nodes after the current. If the current node is the
     * last node, will return an empty array.
     *
     * @return PipelineNodeInterface[]
     */
    #[Pure]
    public function getPendingNodes(): array
    {
        $index = $this->getNodeIndex();

        if ($index === null) {
            return [];
        }

        return array_slice(
            $this->nodes,
            $index,
        );
    }

    #[Pure]
    private function getNodeIndex(): int|null
    {
        $offset = array_search(
            $this->node,
            $this->nodes,
            true
        );

        return $offset === false
            ? null
            : $offset;
    }
}
