<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Events;

use JetBrains\PhpStorm\Pure;
use Matchory\DataPipe\Interfaces\CollectorInterface;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface;
use Matchory\DataPipe\Interfaces\TransformerInterface;
use Matchory\DataPipe\PipelineRegistry;
use Symfony\Component\EventDispatcher\GenericEvent;

class BeforeNodeRegistrationEvent extends GenericEvent
{
    use SkipTrait;

    #[Pure]
    public function __construct(
        protected PipelineNodeInterface $node,
        protected PipelineRegistry $registry
    ) {
        parent::__construct($node);
    }

    #[Pure]
    public function getNode(): PipelineNodeInterface
    {
        return $this->node;
    }

    #[Pure]
    public function getRegistry(): PipelineRegistry
    {
        return $this->registry;
    }

    #[Pure]
    public function isCollector(): bool
    {
        return $this->node instanceof CollectorInterface;
    }

    #[Pure]
    public function isTransformer(): bool
    {
        return $this->node instanceof TransformerInterface;
    }
}
