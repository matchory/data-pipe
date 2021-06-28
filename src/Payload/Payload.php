<?php

/**
 * This file is part of data-processing-pipelines, a Matchory application.
 *
 * Unauthorized copying of this file, via any medium, is strictly prohibited.
 * Its contents are strictly confidential and proprietary.
 *
 * @copyright 2020–2021 Matchory GmbH · All rights reserved
 * @author    Moritz Friedrich <moritz@matchory.com>
 */

declare(strict_types=1);

namespace Matchory\DataPipe\Payload;

use Matchory\DataPipe\Interfaces\PayloadInterface;

class Payload implements PayloadInterface
{
    use PayloadTrait;

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $this->original = $attributes;
    }
}
