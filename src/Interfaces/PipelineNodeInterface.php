<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Interfaces;

use Matchory\DataPipe\PipelineContext;

interface PipelineNodeInterface
{
    /**
     * Pipes a context through the pipeline node and modifies it as necessary.
     *
     * @param PipelineContext $context
     *
     * @return PipelineContext
     */
    public function pipe(
        PipelineContext $context
    ): PipelineContext;

    /**
     * Retrieves all dependencies of a pipeline node as a list of class strings.
     *
     * @return class-string<PipelineNodeInterface>[]
     */
    public function getDependencies(): array;

    /**
     * Serializes the pipeline node to a human-readable name. This is mainly
     * helpful when translating from command line input, or for printing status
     * information to the command line output or log files.
     *
     * @return string
     */
    public function __toString(): string;

}
