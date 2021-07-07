<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Interfaces;

use JetBrains\PhpStorm\Pure;
use JsonSerializable;

interface PayloadInterface extends JsonSerializable
{
    /**
     * Retrieves the current attributes and their values as a map.
     *
     * @return array<string, mixed>
     */
    #[Pure]
    public function getAttributes(): array;

    /**
     * Retrieves the names of the attributes the payload currently has set.
     *
     * @return string[]
     */
    #[Pure]
    public function getAttributeNames(): array;

    /**
     * Retrieves the names of all attributes the payload may have.
     *
     * @return string[]
     */
    public function getAllAttributeNames(): array;

    /**
     * Retrieves the values of all attributes set on the payload.
     *
     * @return array<int, mixed>
     */
    #[Pure]
    public function getAttributeValues(): array;

    /**
     * Retrieves a single attribute value by name. If the attribute isn't set,
     * `null` MUST be returned.
     *
     * @param string $name Name of the attribute to retrieve
     *
     * @return mixed Attribute value if found, `null` otherwise
     */
    #[Pure]
    public function getAttribute(string $name): mixed;

    /**
     * Checks whether a single attribute is set by name.
     *
     * @param string $name Name of the attribute to check
     *
     * @return bool `true` if the attribute is set, `false` otherwise
     */
    #[Pure]
    public function hasAttribute(string $name): bool;

    /**
     * Sets an attribute to a new value. If the attribute wasn't set before, it
     * MUST be created.
     *
     * @param string $name Name of the attribute to set
     * @param mixed  $value New attribute value
     */
    public function setAttribute(string $name, mixed $value): void;
}
