<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Interfaces;

interface CollectorInterface extends PipelineNodeInterface
{
    /**
     * Retrieves the relative cost of using this source. As different sources
     * have different access restrictions or billing schemes, they must be
     * treated accordingly.
     *
     * @return int
     */
    public function getCost(): int;

    /**
     * Lists all fields provided by this data source. This is important during
     * determining the cost: A more expensive data source will only be invoked
     * if it provides fields not yet present in the intermediate representation,
     * and all other, less expensive sources providing those fields have been
     * queried already.
     * Fields themselves have a maximum justifiable cost to acquire them.
     *
     * @return string[]
     */
    public function getProvidedFields(): array;
}
