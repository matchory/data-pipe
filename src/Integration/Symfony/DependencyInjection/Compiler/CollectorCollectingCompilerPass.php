<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Integration\Symfony\DependencyInjection\Compiler;

/**
 * Collector Collecting Compiler Pass
 * ==================================
 * This compiler pass will iterate all definitions and add all nodes it can find
 * to the application automatically.
 *
 * It resolves those nodes by looking for classes tagged with a collector
 * tag as attached in {@see Kernel::build()}.
 *
 * @package Matchory\DataProcessor\DependencyInjection
 * @internal
 */
class CollectorCollectingCompilerPass extends AbstractNodeCollectingCompilerPass
{
    public const TAG = 'pipeline.collector';

    protected function getTag(): string
    {
        return self::TAG;
    }
}
