<?php

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
