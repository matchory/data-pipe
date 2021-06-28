<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Events;

use Iterator;
use JetBrains\PhpStorm\Pure;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface;
use Matchory\DataPipe\PayloadCollection;
use Symfony\Component\EventDispatcher\GenericEvent;

class AfterPipelineEvent extends GenericEvent
{
    /**
     * @param Iterator                $originalPayloads
     * @param PayloadCollection       $updatedPayloads
     * @param PipelineNodeInterface[] $nodes
     * @param int|null                $maximumCost
     */
    #[Pure]
    public function __construct(
        protected Iterator $originalPayloads,
        protected PayloadCollection $updatedPayloads,
        protected array $nodes,
        protected int|null $maximumCost = null
    ) {
        parent::__construct($updatedPayloads);
    }

    #[Pure]
    public function getOriginalPayloads(): Iterator
    {
        return $this->originalPayloads;
    }

    #[Pure]
    public function getUpdatedPayloads(): PayloadCollection
    {
        return $this->updatedPayloads;
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
