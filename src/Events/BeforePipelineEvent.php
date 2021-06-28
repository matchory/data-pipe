<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Events;

use Iterator;
use JetBrains\PhpStorm\Pure;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class BeforePipelineEvent extends GenericEvent
{
    /**
     * @param Iterator                $payloads
     * @param PipelineNodeInterface[] $nodes
     * @param int|null                $maximumCost
     */
    #[Pure]
    public function __construct(
        protected Iterator $payloads,
        protected array $nodes,
        protected int|null $maximumCost = null
    ) {
        parent::__construct($payloads);
    }

    #[Pure]
    public function getPayloads(): Iterator
    {
        return $this->payloads;
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
