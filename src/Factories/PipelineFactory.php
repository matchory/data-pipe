<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Factories;

use Matchory\DataPipe\Exceptions\DependencyGraph\CircularDependencyException;
use Matchory\DataPipe\Exceptions\DependencyGraph\DependencyNotFoundException;
use Matchory\DataPipe\Pipeline;
use Matchory\DataPipe\PipelineRegistry;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Pipeline Factory
 * ================
 * Generic factory to resolve a pipeline from the node registry.
 *
 * @bundle Matchory\DataPipe\Factories
 */
class PipelineFactory
{
    public function __construct(
        protected PipelineRegistry $pipelineRegistry,
        protected EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * Creates a new pipeline
     *
     * @return Pipeline
     * @throws CircularDependencyException
     * @throws DependencyNotFoundException
     */
    public function createPipeline(): Pipeline
    {
        $nodes = $this->pipelineRegistry->resolvePipeline();

        return new Pipeline(
            $nodes,
            $this->eventDispatcher
        );
    }
}
