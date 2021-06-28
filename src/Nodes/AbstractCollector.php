<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Nodes;

use Matchory\DataPipe\Interfaces\CollectorInterface;

use function array_pop;
use function explode;
use function str_ireplace;
use function strtolower;

abstract class AbstractCollector extends AbstractPipelineNode implements CollectorInterface
{
    public function __toString(): string
    {
        $segments = explode('\\', strtolower(
            static::class
        ));

        return str_ireplace(
            'Collector',
            '',
            array_pop($segments)
        );
    }
}
