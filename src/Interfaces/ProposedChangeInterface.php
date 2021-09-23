<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Interfaces;

/**
 * Proposed Change
 * ===============
 * Proposed changes hold modifications to attribute values on payloads.
 * They describe the entire proposed modification, including the origin,
 * attribute to change, old and new value, as well as a level of confidence the
 * pipeline step attaches to the change itself.
 *
 * @template T
 * @package Matchory\DataPipe\Contracts
 */
interface ProposedChangeInterface
{
    /**
     * Applies the proposed change to a payload instance.
     *
     * @param PayloadInterface $payload
     *
     * @return PayloadInterface
     */
    public function apply(PayloadInterface $payload): PayloadInterface;

    /**
     * Retrieves the name of the payload attribute this change proposal
     * refers to.
     *
     * @return string
     */
    public function getAttribute(): string;

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
     * Retrieves the proposed new value of the attribute.
     *
     * @return T
     */
    public function getNewValue(): mixed;

    /**
     * Retrieves the node that proposed the change. This may be used both for
     * documenting the change process by the application, and other nodes in
     * assessing the necessity of changes.
     *
     * @return PipelineNodeInterface
     */
    public function getNode(): PipelineNodeInterface;

    /**
     * Retrieves the previous value of the attribute, that is, the value the
     * attribute had before the node *issuing this change proposal* proposed
     * this change.
     * Depending on the order of pipeline nodes, this change proposal might be
     * evaluated at a later point in time, at which the old value may no longer
     * have a reference.
     *
     * @return T
     */
    public function getOldValue(): mixed;
}
