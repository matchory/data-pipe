<?php

/**
 * This file is part of data-pipe, a Matchory application.
 *
 * Unauthorized copying of this file, via any medium, is strictly prohibited.
 * Its contents are strictly confidential and proprietary.
 *
 * @copyright 2020–2021 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

namespace Matchory\DataPipe\Payload;

use JetBrains\PhpStorm\Pure;

use function array_diff;
use function array_filter;
use function array_keys;
use function array_map;
use function array_merge;
use function array_values;

trait PayloadTrait
{
    /**
     * @var array<string, mixed>
     */
    protected array $original = [];

    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    #[Pure]
    public function __get(string $name): mixed
    {
        return $this->getAttribute($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->setAttribute($name, $value);
    }

    #[Pure]
    public function __isset(string $name): bool
    {
        return $this->hasAttribute($name);
    }

    #[Pure]
    public function getOriginalAttributes(): array
    {
        return $this->original;
    }

    #[Pure]
    public function getOriginalAttribute(string $name): mixed
    {
        return $this->original[$name] ?? null;
    }

    public function getChangedAttributes(): array
    {
        // Find names of attributes that have been changed
        $changed = array_filter(array_map(
            fn(string $name) => $this->wasChanged($name)
                ? $name
                : null,
            $this->getAttributeNames()
        ));

        // Find attribute names in the current attributes not present in the
        // original attribute list
        $added = array_diff(
            array_keys($this->getOriginalAttributes()),
            $this->getAttributeNames(),
        );

        return array_merge($changed, $added);
    }

    #[Pure]
    public function wasChanged(string $name): bool
    {
        /** @var mixed $a */
        $a = $this->attributes[$name] ?? null;

        /** @var mixed $b */
        $b = $this->original[$name] ?? null;

        return $a !== $b;
    }

    /**
     * @inheritdoc
     */
    #[Pure]
    public function getAllAttributeNames(): array
    {
        return $this->getAttributeNames();
    }

    /**
     * @inheritdoc
     */
    #[Pure]
    public function getAttribute(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @inheritdoc
     */
    #[Pure]
    public function getAttributeNames(): array
    {
        return array_keys($this->getAttributes());
    }

    /**
     * @inheritdoc
     */
    #[Pure]
    public function getAttributeValues(): array
    {
        return array_values($this->getAttributes());
    }

    /**
     * @inheritdoc
     */
    #[Pure]
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    #[Pure]
    public function hasAttribute(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @inheritdoc
     */
    #[Pure]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @inheritdoc
     */
    public function setAttribute(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    #[Pure]
    public function toArray(): array
    {
        return $this->getAttributes();
    }
}
