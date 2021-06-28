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

use Matchory\DataPipe\Interfaces\PayloadInterface;
use Ramsey\Collection\AbstractCollection;

class PayloadCollection extends AbstractCollection
{
    public function getType(): string
    {
        return PayloadInterface::class;
    }
}
