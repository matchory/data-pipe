<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Events;

use JetBrains\PhpStorm\Pure;
use Matchory\DataPipe\Interfaces\PayloadInterface;
use Matchory\DataPipe\Interfaces\ProposedChangeInterface;
use Matchory\DataPipe\PipelineContext;
use Symfony\Component\EventDispatcher\GenericEvent;

class AfterCommitEvent extends GenericEvent
{
    /**
     * @param PipelineContext           $context
     * @param ProposedChangeInterface[] $changeSet
     * @param PayloadInterface          $payload
     */
    #[Pure]
    public function __construct(
        protected PipelineContext $context,
        protected array $changeSet,
        protected PayloadInterface $payload
    ) {
        parent::__construct($context);
    }

    #[Pure]
    public function getContext(): PipelineContext
    {
        return $this->context;
    }

    /**
     * @return ProposedChangeInterface[]
     */
    #[Pure]
    public function getChangeSet(): array
    {
        return $this->changeSet;
    }

    #[Pure]
    public function getPayload(): PayloadInterface
    {
        return $this->payload;
    }
}
