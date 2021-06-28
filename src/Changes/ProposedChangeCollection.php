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

namespace Matchory\DataPipe\Changes;

use Matchory\DataPipe\AbstractCollection;
use Matchory\DataPipe\Interfaces\ProposedChangeInterface;

/**
 * @template-extends AbstractCollection<ProposedChangeInterface>
 */
class ProposedChangeCollection extends AbstractCollection
{
    public function getType(): string
    {
        return ProposedChangeInterface::class;
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

        foreach ($this->data as $change) {
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
}
