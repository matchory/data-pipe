<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Exceptions\DependencyGraph;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface;

use function sprintf;

class DependencyNotFoundException extends InvalidArgumentException
{
    #[Pure]
    public function __construct(
        protected string $dependency,
        protected PipelineNodeInterface $node
    ) {
        parent::__construct(sprintf(
            "Dependency '%s' not found, required by '%s'",
            $dependency,
            $node::class
        ));
    }

    #[Pure]
    public function getDependency(): string
    {
        return $this->dependency;
    }

    #[Pure]
    public function getNode(): PipelineNodeInterface
    {
        return $this->node;
    }
}
