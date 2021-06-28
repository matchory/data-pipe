<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Changes;

use JetBrains\PhpStorm\Pure;
use Matchory\DataPipe\Interfaces\PayloadInterface;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface;
use Matchory\DataPipe\Interfaces\ProposedChangeInterface;

final class ProposedChange implements ProposedChangeInterface
{
    private mixed $oldValue;

    private int $confidence;

    private PipelineNodeInterface $node;

    private string $field;

    private mixed $newValue;

    #[Pure]
    public function __construct(
        PipelineNodeInterface $node,
        string $field,
        mixed $newValue,
        mixed $oldValue = null,
        int $confidence = 0
    ) {
        $this->node = $node;
        $this->field = $field;
        $this->newValue = $newValue;
        $this->oldValue = $oldValue;
        $this->confidence = $confidence;
    }

    /**
     * @inheritdoc
     */
    public function apply(PayloadInterface $payload): PayloadInterface
    {
        $payload->setAttribute(
            $this->getField(),
            $this->getNewValue()
        );

        return $payload;
    }

    #[Pure]
    public function getConfidence(): int
    {
        return $this->confidence;
    }

    #[Pure]
    public function getField(): string
    {
        return $this->field;
    }

    #[Pure]
    public function getNewValue(): mixed
    {
        return $this->newValue;
    }

    #[Pure]
    public function getNode(): PipelineNodeInterface
    {
        return $this->node;
    }

    #[Pure]
    public function getOldValue(): mixed
    {
        return $this->oldValue;
    }
}
