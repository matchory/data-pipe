<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Events;

use JetBrains\PhpStorm\Pure;
use Matchory\DataPipe\Interfaces\PayloadInterface;
use Matchory\DataPipe\Interfaces\ProposedChangeInterface;
use Matchory\DataPipe\PipelineContext;
use Symfony\Component\EventDispatcher\GenericEvent;

class BeforeCommitEvent extends GenericEvent
{
    /**
     * @param PipelineContext                        $context
     * @param array<string, ProposedChangeInterface> $changeSet
     * @param PayloadInterface                       $payload
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
     * @return array<string, ProposedChangeInterface>
     */
    #[Pure]
    public function getChangeSet(): array
    {
        return $this->changeSet;
    }

    /**
     * @param array<string, ProposedChangeInterface> $changeSet
     */
    public function setChangeSet(array $changeSet): void
    {
        $this->changeSet = $changeSet;
    }

    #[Pure]
    public function getPayload(): PayloadInterface
    {
        return $this->payload;
    }
}
