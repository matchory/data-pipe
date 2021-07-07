<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Changes;

use DusanKasan\Knapsack\Collection;
use Matchory\DataPipe\Interfaces\PayloadInterface;
use Matchory\DataPipe\Interfaces\ProposedChangeInterface;

use function DusanKasan\Knapsack\append;
use function DusanKasan\Knapsack\filter;
use function DusanKasan\Knapsack\sort;

/**
 * @method ProposedChangeInterface last(bool $convertToCollection = false)
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

    /**
     * Adds a proposed change to the collection.
     *
     * @param mixed $value
     *
     * @return ProposedChangeCollection
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function add(ProposedChangeInterface $value): ProposedChangeCollection
    {
        $collection = append($this->getItems(), $value);

        return new self($collection->getItems());
    }

    /**
     * @inheritdoc
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function filter(
        callable|null $function = null
    ): ProposedChangeCollection {
        $collection = filter($this->getItems(), $function);

        return new self($collection);
    }

    /**
     * @inheritdoc
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function sort(callable $function): ProposedChangeCollection
    {
        $collection = sort($this->getItems(), $function);

        return new self($collection);
    }
}
