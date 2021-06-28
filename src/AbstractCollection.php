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

namespace Matchory\DataPipe;

use Ramsey\Collection\AbstractCollection as Collection;

use function array_reduce;

/**
 * @template         T
 * @template-extends Collection<T>
 * @psalm-suppress   ImplementedReturnTypeMismatch
 * @method $this filter(callable $callback)
 * @method $this sort(string $propertyOrMethod, string $order = self::SORT_ASC)
 */
abstract class AbstractCollection extends Collection
{
    /**
     * @param callable(U, T): U $callback A callable to apply to each item of
     *                                    the collection to reduce it to a
     *                                    single value.
     * @param U|null            $initial  If provided, this is the initial value
     *                                    provided to the callback.
     *
     * @return U
     * @template       U
     * @psalm-suppress MixedInferredReturnType
     * @psalm-suppress MixedReturnStatement
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce(
            $this->data,
            $callback,
            $initial
        );
    }
}
