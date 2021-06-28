<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Interfaces;

/**
 * Proposed Change
 * ===============
 * Proposed changes hold modifications to field values on payloads.
 * They describe the entire proposed modification, including the origin, field
 * to change, old and new value, as well as a level of confidence the pipeline
 * step puts into the change itself.
 *
 * @package Matchory\DataPipe\Contracts
 */
interface ProposedChangeInterface
{
    /**
     * Retrieves the node that proposed the change. This may be used both for
     * documenting the change process by the application, and other nodes in
     * assessing the necessity of changes.
     *
     * @return PipelineNodeInterface
     */
    public function getNode(): PipelineNodeInterface;

    /**
     * Retrieves the name of the payload field this change proposal refers to.
     *
     * @return string
     */
    public function getField(): string;

    /**
     * Retrieves the proposed new value of the field.
     *
     * @return mixed
     */
    public function getNewValue(): mixed;

    /**
     * Retrieves the previous value of the field, that is, the value the field
     * had before the node *issuing this change proposal* proposed this change.
     * Depending on the order of pipeline nodes, this change proposal might be
     * evaluated at a later point in time, at which the old value may no longer
     * have a reference.
     *
     * @return mixed
     */
    public function getOldValue(): mixed;

    /**
     * Retrieves the level of confidence the node has put into this change
     * proposal. Allowing nodes to assess this value by themselves makes it
     * possible to encourage other nodes to take precedence over values derived
     * from incomplete or ambiguous data.
     * The confidence level has no set scale; it's always relative to usage by
     * other nodes in the application.
     *
     * @return int
     */
    public function getConfidence(): int;

    /**
     * Applies the proposed change to a payload instance.
     *
     * @param PayloadInterface $payload
     *
     * @return PayloadInterface
     */
    public function apply(PayloadInterface $payload): PayloadInterface;
}
