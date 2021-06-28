<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Nodes;

use Matchory\DataPipe\Interfaces\PipelineNodeInterface;

use function in_array;
use function is_string;

abstract class AbstractPipelineNode implements PipelineNodeInterface
{
    /**
     * @var class-string<PipelineNodeInterface>[]
     */
    protected array $dependencies = [];

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * @param PipelineNodeInterface|class-string<PipelineNodeInterface> $node
     *
     * @return bool
     */
    public function dependsOn(PipelineNodeInterface|string $node): bool
    {
        $className = is_string($node)
            ? $node
            : $node::class;

        return in_array(
            $className,
            $this->dependencies,
            true
        );
    }
}
