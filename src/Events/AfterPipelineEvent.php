<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Events;

use DusanKasan\Knapsack\Collection;
use Iterator;
use JetBrains\PhpStorm\Pure;
use Matchory\DataPipe\Interfaces\PayloadInterface;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AfterPipelineEvent extends GenericEvent
{
    /**
     * @param Iterator<PayloadInterface> $originalPayloads
     * @param Collection                 $updatedPayloads
     * @param PipelineNodeInterface[]    $nodes
     * @param int|null                   $maximumCost
     */
    #[Pure]
    public function __construct(
        protected Iterator $originalPayloads,
        protected Collection $updatedPayloads,
        protected array $nodes,
        protected int|null $maximumCost = null
    ) {
        parent::__construct($updatedPayloads);
    }

    /**
     * @return Iterator<PayloadInterface>
     */
    #[Pure]
    public function getOriginalPayloads(): Iterator
    {
        return $this->originalPayloads;
    }

    #[Pure]
    public function getUpdatedPayloads(): Collection
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
