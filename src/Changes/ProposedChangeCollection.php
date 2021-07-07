<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Changes;

use DusanKasan\Knapsack\Collection;
use Matchory\DataPipe\Interfaces\PayloadInterface;
use Matchory\DataPipe\Interfaces\ProposedChangeInterface;

/**
 * @method ProposedChangeInterface last(bool $convertToCollection = false)
 * @method self filter(callable|null $function = null)
 * @method self sort(callable $function)
 * @method self values()
 * @method ProposedChangeInterface[] toArray()
 */
class ProposedChangeCollection extends Collection
{
    /**
     * Clears the collection.
     *
     * @return $this
     */
    public function clear(): static
    {
        $this->drop($this->size());

        return $this;
    }

    /**
     * Resolves proposed changes in a context to the single proposal with
     * the highest confidence level for each field.
     *
     * @return array<string, ProposedChangeInterface>
     */
    public function resolve(): array
    {
        /** @var array<string, ProposedChangeInterface> $changes */
        $changes = [];

        foreach ($this->toArray() as $change) {
            $field = $change->getField();

            // Keep the existing change if its confidence is higher, or override
            // it with the new change
            if (
                isset($changes[$field]) &&
                $changes[$field]->getConfidence() > $change->getConfidence()
            ) {
                continue;
            }

            $changes[$field] = $change;
        }

        return $changes;
    }

    public function apply(PayloadInterface $payload): PayloadInterface
    {
        $payload = clone $payload;

        $this->reduce(
            fn(
                PayloadInterface $payload,
                ProposedChangeInterface $change
            ) => $change->apply($payload),
            $payload
        );

        $this->clear();

        return $payload;
    }
}
