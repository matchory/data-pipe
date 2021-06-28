<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Integration\Symfony\DependencyInjection\Compiler;

use Matchory\DataPipe\Integration\Symfony\DataPipeBundle;
use Matchory\DataPipe\PipelineRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Abstract Node Collecting Compiler Pass
 * ======================================
 * Collects all pipeline nodes in the project tagged with a given tag. The tag
 * should be automatically assigned to all classes implementing the base node
 * interface, so they can be discovered during compilation.
 * The tagging happens in the {@see DataPipeBundle}.
 *
 * @package Matchory\DataPipe\Integration\Symfony\DependencyInjection\Compiler
 */
abstract class AbstractNodeCollectingCompilerPass implements CompilerPassInterface
{
    public const ATTRIBUTE_DISABLED = 'disabled';

    public const TAG = 'pipeline.node';

    /**
     * @inheritdoc
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     */
    public function process(ContainerBuilder $container): void
    {
        $registry = $container->findDefinition(PipelineRegistry::class);
        $nodes = $this->resolveNodes($container);

        foreach ($nodes as $node => $attributes) {
            if ($this->isNodeDisabled($node, $attributes)) {
                continue;
            }

            $this->registerNode($registry, $node);
        }
    }

    /**
     * Retrieves the tag all matching node classes are tagged with.
     *
     * @return string
     */
    abstract protected function getTag(): string;

    /**
     * Retrieve all services tagged as a node. We added that tag to all classes
     * implementing the pipeline node interface automatically in the
     * service definition.
     *
     * @param ContainerBuilder $container
     *
     * @return array<string, array<int, array<string, bool|string>>>
     * @throws InvalidArgumentException
     * @psalm-suppress MixedReturnTypeCoercion
     * @noinspection   PhpDocSignatureInspection
     */
    protected function resolveNodes(ContainerBuilder $container): array
    {
        $tag = $this->getTag();

        return $container->findTaggedServiceIds($tag);
    }

    /**
     * Checks whether a node has been disabled by setting the "disabled"
     * attribute to a truthy value.
     *
     * @param string                                 $node
     * @param array<int, array<string, bool|string>> $attributes
     *
     * @return bool
     */
    protected function isNodeDisabled(string $node, array $attributes): bool
    {
        if ( ! isset($attributes[0])) {
            return false;
        }

        $disabled = $attributes[0][self::ATTRIBUTE_DISABLED] ?? false;

        return (bool)$disabled;
    }

    /**
     * Adds a node to the registry service definition.
     *
     * @param Definition $registryDefinition
     * @param string     $node
     *
     * @throws InvalidArgumentException
     */
    protected function registerNode(
        Definition $registryDefinition,
        string $node
    ): void {
        $registryDefinition->addMethodCall('addNode', [
            new Reference($node),
        ]);
    }
}
