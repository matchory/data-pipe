<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Events;

use JetBrains\PhpStorm\Pure;
use Matchory\DataPipe\Interfaces\CollectorInterface;
use Matchory\DataPipe\PipelineContext;
use Symfony\Component\EventDispatcher\GenericEvent;

class NodeTooExpensiveEvent extends GenericEvent
{
    use SkipTrait;

    #[Pure]
    public function __construct(
        protected CollectorInterface $node,
        protected PipelineContext $context,
        protected int $maximumCost
    ) {
        parent::__construct($node);

        $this->skip = true;
    }

    #[Pure]
    public function getNode(): CollectorInterface
    {
        return $this->node;
    }

    #[Pure]
    public function getContext(): PipelineContext
    {
        return $this->context;
    }

    #[Pure]
    public function getMaximumCost(): int
    {
        return $this->maximumCost;
    }
}
