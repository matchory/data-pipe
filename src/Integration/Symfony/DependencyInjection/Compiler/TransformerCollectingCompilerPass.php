<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Integration\Symfony\DependencyInjection\Compiler;

/**
 *
 */
class TransformerCollectingCompilerPass extends AbstractNodeCollectingCompilerPass
{
    public const TAG = 'pipeline.transformer';

    protected function getTag(): string
    {
        return self::TAG;
    }
}
