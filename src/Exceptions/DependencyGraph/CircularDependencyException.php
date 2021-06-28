<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Exceptions\DependencyGraph;

use JetBrains\PhpStorm\Pure;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface;
use RuntimeException;

use function count;
use function implode;

class CircularDependencyException extends RuntimeException
{
    /**
     * @var PipelineNodeInterface[]
     */
    protected array $nodes;

    protected string $path;

    /**
     * CircularDependencyException constructor.
     *
     * @param PipelineNodeInterface[] $nodes
     */
    #[Pure]
    public function __construct(array $nodes)
    {
        $this->nodes = $nodes;
        $this->path = implode(' → ', $nodes);

        parent::__construct("Circular dependency found: {$this->path}");
    }

    #[Pure]
    public function getCount(): int
    {
        return count($this->nodes);
    }

    #[Pure]
    public function getFirstNode(): PipelineNodeInterface
    {
        return $this->nodes[0];
    }

    #[Pure]
    public function getLastNode(): PipelineNodeInterface
    {
        return $this->nodes[$this->getCount() - 1];
    }

    #[Pure]
    public function getNodes(): array
    {
        return $this->nodes;
    }

    #[Pure]
    public function getPath(): string
    {
        return $this->path;
    }
}
