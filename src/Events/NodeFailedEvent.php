<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Events;

use JetBrains\PhpStorm\Pure;
use Matchory\DataPipe\PipelineContext;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Throwable;

class NodeFailedEvent extends GenericEvent
{
    use SkipTrait;

    #[Pure]
    public function __construct(
        protected Throwable $exception,
        protected PipelineNodeInterface $node,
        protected PipelineContext $context,
        protected float $duration
    ) {
        parent::__construct($exception);
    }

    #[Pure]
    public function getException(): Throwable
    {
        return $this->exception;
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
    public function getDuration(): float
    {
        return $this->duration;
    }
}
