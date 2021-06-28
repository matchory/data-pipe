<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Events;

use JetBrains\PhpStorm\Pure;
use Matchory\DataPipe\PipelineContext;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class BeforePipelineItemEvent extends GenericEvent
{
    /**
     * @param PipelineContext         $context
     * @param PipelineNodeInterface[] $nodes
     * @param int|null                $maximumCost
     */
    #[Pure]
    public function __construct(
        protected     PipelineContext $context,
        protected array $nodes,
        protected int|null $maximumCost = null
    ) {
        parent::__construct($context);
    }

    #[Pure]
    public function getContext(): PipelineContext
    {
        return $this->context;
    }

    /**
     * @return PipelineNodeInterface[]
     */
    #[Pure]
    public function getNodes(): array
    {
        return $this->nodes;
    }

    #[Pure]
    public function getMaximumCost(): int|null
    {
        return $this->maximumCost;
    }
}
