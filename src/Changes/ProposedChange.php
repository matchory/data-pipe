<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Changes;

use JetBrains\PhpStorm\Pure;
use Matchory\DataPipe\Interfaces\PayloadInterface;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface;
use Matchory\DataPipe\Interfaces\ProposedChangeInterface;

/**
 * ProposedChange
 *
 * @template T
 * @implements ProposedChangeInterface<T>
 * @bundle   Matchory\DataPipe\Changes
 */
final class ProposedChange implements ProposedChangeInterface
{
    private string $attribute;

    private int $confidence;

    /**
     * @var T|null
     */
    private mixed $newValue;

    private PipelineNodeInterface $node;

    /**
     * @var T|null
     */
    private mixed $oldValue;

    /**
     * @param PipelineNodeInterface $node
     * @param string                $attribute
     * @param T                     $newValue
     * @param T|null                $oldValue
     * @param int                   $confidence
     */
    #[Pure]
    public function __construct(
        PipelineNodeInterface $node,
        string $attribute,
        mixed $newValue,
        mixed $oldValue = null,
        int $confidence = 0
    ) {
        $this->node = $node;
        $this->attribute = $attribute;
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
            $this->getAttribute(),
            $this->getNewValue()
        );

        return $payload;
    }

    #[Pure]
    public function getAttribute(): string
    {
        return $this->attribute;
    }

    #[Pure]
    public function getConfidence(): int
    {
        return $this->confidence;
    }

    /**
     * @return T
     */
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

    /**
     * @return T
     */
    #[Pure]
    public function getOldValue(): mixed
    {
        return $this->oldValue;
    }
}
