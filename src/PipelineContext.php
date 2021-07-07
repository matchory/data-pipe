<?php

declare(strict_types=1);

namespace Matchory\DataPipe;

use DusanKasan\Knapsack\Exceptions\InvalidArgument;
use DusanKasan\Knapsack\Exceptions\InvalidReturnValue;
use DusanKasan\Knapsack\Exceptions\ItemNotFound;
use Matchory\DataPipe\Changes\ProposedChange;
use Matchory\DataPipe\Changes\ProposedChangeCollection;
use Matchory\DataPipe\Interfaces\PayloadInterface;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface;
use Matchory\DataPipe\Interfaces\ProposedChangeInterface;

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
    protected ProposedChangeCollection $proposedChanges;

    protected PipelineNodeInterface|null $node = null;

    /**
     * @throws InvalidReturnValue
     * @throws InvalidArgument
     */
    public function __construct(
        protected PayloadInterface $payload
    ) {
        $this->proposedChanges = new ProposedChangeCollection([]);
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

    public function commit(): PayloadInterface
    {
        return $this
            ->getProposedChanges()
            ->apply($this->getPayload());
    }

    /**
     * Retrieves all fields missing from the payload at the current point in
     * pipeline processing time. This method must return a list of field names
     * as they occur in the payload itself.
     *
     * @return string[]
     */
    public function getMissingFields(): array
    {
        $fieldNames = $this->payload->getAllAttributeNames();

        $fields = [];

        foreach ($fieldNames as $name) {
            /** @psalm-var mixed $value */
            $value = $this->payload->getAttribute($name);

            if ($value !== null && $value !== '') {
                continue;
            }

            if ($this->getProposedChangesToField($name)->isNotEmpty()) {
                continue;
            }

            $fields[] = $name;
        }

        return $fields;
    }

    /**
     * Retrieves the most trusted value for a field. This may include both the
     * payload value or any proposed changes.
     *
     * @param string $field Name of the field to retrieve
     *
     * @return mixed Value if found, null otherwise.
     * @throws ItemNotFound
     */
    public function getMostTrustedValueForField(string $field): mixed
    {
        $proposedChanges = $this->getProposedChangesToField($field);

        // If there are no proposed changes for this field, return the payload
        // attribute. This will be null if the field is not set or unknown.
        if ($proposedChanges->isEmpty()) {
            return $this->payload->getAttribute($field);
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
     * Retrieves all proposed changes to a given field.
     *
     * @param string $field
     *
     * @return ProposedChangeCollection
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function getProposedChangesToField(
        string $field
    ): ProposedChangeCollection {
        $changes = $this->proposedChanges->filter(static fn(
            ProposedChangeInterface $change
        ): bool => $change->getField() === $field);

        return new ProposedChangeCollection($changes);
    }

    /**
     * Proposes a change to the payload data. A change consists of a field to be
     * changed, its new value, and a self-assessed level of confidence the node
     * attaches to this change proposal. Depending on how the value has been
     * derived, a node might assign a lower confidence if there's reason to
     * believe another node might be able to obtain a better value.
     * To improve upon an existing proposed change, a node might add up all
     * existing confidence levels to ensure its value takes precedence.
     *
     * @param string $field      Name of the field to be updated. Note that this
     *                           MUST be a field resolvable in the payload
     *                           itself, otherwise the proposal will
     *                           be discarded.
     * @param mixed  $value      Proposed, changed field value.
     * @param int    $confidence Level of confidence the node self-assessed for
     *                           this proposal. This value has no lower or upper
     *                           bounds but must be set according to other
     *                           pipeline nodes in the application.
     */
    public function proposeChange(
        string $field,
        mixed $value,
        int $confidence = 0
    ): void {
        /** @var PipelineNodeInterface $node */
        $node = $this->node;

        $this->proposedChanges->append(new ProposedChange(
            $node,
            $field,
            $value,
            $this->payload->{$field} ?? null,
            $confidence
        ));
    }
}
