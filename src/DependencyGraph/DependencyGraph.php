<?php

declare(strict_types=1);

namespace Matchory\DataPipe\DependencyGraph;

use Matchory\DataPipe\Exceptions\DependencyGraph\CircularDependencyException;
use Matchory\DataPipe\Exceptions\DependencyGraph\DependencyNotFoundException;
use Matchory\DataPipe\Interfaces\PipelineNodeInterface as PNode;
use MJS\TopSort\CircularDependencyException as CircularDependency;
use MJS\TopSort\ElementNotFoundException;
use MJS\TopSort\Implementations\FixedArraySort;
use MJS\TopSort\TopSortInterface;

use function array_map;
use function count;

/**
 * Dependency Graph
 * ================
 * Represents a dependency graph for pipeline nodes. This allows to resolve
 * arbitrarily complex dependency issues rather quickly.
 *
 * @package Matchory\DataPipe\DependencyGraph
 */
class DependencyGraph
{
    private const IMPLEMENTATION = FixedArraySort::class;

    /**
     * @var PNode[]
     */
    protected array $nodes = [];

    protected TopSortInterface $graph;

    public function __construct()
    {
        $implementation = self::IMPLEMENTATION;
        $this->graph = new $implementation();
    }

    /**
     * Retrieves the size of the graph.
     *
     * @return int Number of nodes on the graph
     */
    public function getSize(): int
    {
        return count($this->nodes);
    }

    /**
     * Connects a node to the graph.
     *
     * @param PNode $node
     */
    public function connect(PNode $node): void
    {
        // Store a reference to the node, so we can retrieve it from its ID
        $this->nodes[$node::class] = $node;

        // Add the node and its dependencies to the graph
        $this->graph->add(
            $node::class,
            $node->getDependencies()
        );
    }

    /**
     * Resolves the dependency graph to a linear acyclic graph.
     *
     * @return array
     * @throws CircularDependencyException
     * @throws DependencyNotFoundException
     */
    public function resolve(): array
    {
        // Map the node IDs we stored earlier back to their instances
        try {
            return array_map(
                fn(string $id): PNode => $this->nodes[$id],
                $this->graph->sort()
            );
        } catch (CircularDependency $exception) {
            /** @var string[] $nodes */
            $nodes = $exception->getNodes();

            throw new CircularDependencyException(array_map(
                fn(string $nodeId): PNode => $this->nodes[$nodeId],
                $nodes
            ));
        } catch (ElementNotFoundException $exception) {
            $dependency = $exception->getTarget();
            $requiredBy = $this->nodes[$exception->getSource()];

            throw new DependencyNotFoundException(
                $dependency,
                $this->nodes[$requiredBy::class]
            );
        }
    }
}
