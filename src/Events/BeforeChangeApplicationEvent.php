<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Events;

use JetBrains\PhpStorm\Pure;
use Matchory\DataPipe\PipelineContext;
use Matchory\DataPipe\Interfaces\ProposedChangeInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class BeforeChangeApplicationEvent extends GenericEvent
{
    use SkipTrait;

    #[Pure]
    public function __construct(
        protected PipelineContext $context,
        protected ProposedChangeInterface $proposedChange
    ) {
        parent::__construct($context);
    }

    #[Pure]
    public function getContext(): PipelineContext
    {
        return $this->context;
    }

    #[Pure]
    public function getProposedChange(): ProposedChangeInterface
    {
        return $this->proposedChange;
    }
}
