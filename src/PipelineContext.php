<?php

declare(strict_types=1);

namespace Matchory\DataPipe;

use DusanKasan\Knapsack\Exceptions\InvalidArgument;
use DusanKasan\Knapsack\Exceptions\InvalidReturnValue;
use Matchory\DataPipe\Changes\ProposedChange;
use Matchory\DataPipe\Changes\ProposedChangeCollection;
use Matchory\DataPipe\Interfaces\PayloadInterface;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface;
use Matchory\DataPipe\Interfaces\ProposedChangeInterface;

use function assert;
use function ceil;

/**
 * Pipeline Context
 * ================
 * The pipeline context provides access to the payload and allows proposing
 * changes without applying them right away.
 *
 * @package Matchory\DataPipe
 */
final class PipelineContext
{
    protected PipelineNodeInterface|null $node = null;

    protected ProposedChangeCollection $proposedChanges;

    /**
     * @throws InvalidReturnValue
     * @throws InvalidArgument
     * @internal
     */
    public function __construct(
        protected PayloadInterface $payload
    ) {
        $this->proposedChanges = new ProposedChangeCollection([]);
    }

    /**
     * Commits the context changes on the payload.
     *
     * @return PayloadInterface
     */
    public function commit(): PayloadInterface
    {
        return $this
            ->getProposedChanges()
            ->apply($this->getPayload());
    }

    /**
     * Returns a new context based on the current one, scoped to a new node.
     *
     * @internal
     */
    public function forNode(PipelineNodeInterface $node): self
    {
        $context = clone $this;
        $context->node = $node;

        return $context;
    }

    /**
     * Retrieves all attributes missing from the payload at the current point in
     * pipeline processing time. This method must return a list of attribute
     * names as they occur in the payload itself.
     *
     * @return string[]
     */
    public function getMissingAttributes(): array
    {
        $attributeNames = $this->payload->getAllAttributeNames();
        $attributes = [];

        foreach ($attributeNames as $name) {
            /** @psalm-var mixed $value */
            $value = $this->payload->getAttribute($name);

            if ($value !== null && $value !== '') {
                continue;
            }

            if ($this->getProposedChangesToAttribute($name)->isNotEmpty()) {
                continue;
            }

            $attributes[] = $name;
        }

        return $attributes;
    }

    /**
     * Retrieves the most trusted value for an attribute. This may include both the
     * payload value or any proposed changes.
     *
     * @param string $attribute Name of the attribute to retrieve
     *
     * @return mixed Value if found, null otherwise.
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function getMostTrustedValueForAttribute(string $attribute): mixed
    {
        $proposedChanges = $this->getProposedChangesToAttribute($attribute);

        // If there are no proposed changes for this attribute, return the
        // unmodified value from the payload itself. This will be null if the
        // attribute is not set or unknown.
        if ($proposedChanges->isEmpty()) {
            return $this->payload->getAttribute($attribute);
        }

        return $proposedChanges

            // Sort the changes ascending by confidence
            ->sort(fn(
                ProposedChangeInterface $a,
                ProposedChangeInterface $b
            ) => $a->getConfidence() <=> $b->getConfidence())

            // Retrieve the change with the highest confidence
            ->last()

            // Retrieve the proposed new value
            ->getNewValue();
    }

    /**
     * Retrieves the payload contained in the context. As the payload is stored
     * read-only, this method retrieves a cloned instance of the
     * existing payload.
     *
     * @return PayloadInterface
     */
    public function getPayload(): PayloadInterface
    {
        return clone $this->payload;
    }

    /**
     * Retrieves all proposed changes.
     *
     * @return ProposedChangeCollection
     */
    public function getProposedChanges(): ProposedChangeCollection
    {
        return $this->proposedChanges;
    }

    /**
     * Retrieves all proposed changes to a given attribute.
     *
     * @param string $attribute
     *
     * @return ProposedChangeCollection
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function getProposedChangesToAttribute(
        string $attribute
    ): ProposedChangeCollection {
        return $this->proposedChanges->filter(static fn(
            ProposedChangeInterface $change
        ): bool => $change->getAttribute() === $attribute);
    }

    /**
     * Proposes a change to the payload data. A change consists of an attribute
     * to be changed, its new value, and a self-assessed level of confidence the
     * node attaches to this change proposal. Depending on how the value has
     * been derived, a node might assign a lower confidence if there's reason to
     * believe another node might be able to obtain a better value.
     * To improve upon an existing proposed change, a node might add up all
     * existing confidence levels to ensure its value takes precedence.
     *
     * @param string $attribute  Name of the attribute to be updated. Note that
     *                           this MUST be an attribute resolvable in the
     *                           payload itself, otherwise the proposal will
     *                           be discarded.
     * @param mixed  $value      Proposed, updated attribute value.
     * @param int    $confidence Level of confidence the node self-assessed for
     *                           this proposal. This value has no lower or upper
     *                           bounds but must be set according to other
     *                           pipeline nodes in the application.
     */
    public function proposeChange(
        string $attribute,
        mixed $value,
        int $confidence = 0
    ): void {
        // The assertion cannot fail at this point: Every context is created
        // with a node scope before the application can propose changes.
        assert($this->node instanceof PipelineNodeInterface);

        $this->proposedChanges = $this->proposedChanges->add(new ProposedChange(
            $this->node,
            $attribute,
            $value,
            $this->payload->getAttribute($attribute),
            $confidence
        ));
    }
}
