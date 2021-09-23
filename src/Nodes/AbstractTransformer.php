<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Nodes;

use Matchory\DataPipe\Interfaces\TransformerInterface;

use function array_pop;
use function explode;
use function str_ireplace;
use function strtolower;

abstract class AbstractTransformer extends AbstractPipelineNode implements TransformerInterface
{
    public function __toString(): string
    {
        $segments = explode('\\', strtolower(static::class));
        $className = array_pop($segments);

        return str_ireplace(
            'Transformer',
            '',
            $className
        );
    }
}
