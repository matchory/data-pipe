<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Integration\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     * @psalm-suppress MixedMethodCall
     * @psalm-suppress PossiblyUndefinedMethod
     * @noinspection   GrazieInspection
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        /* @formatter:off */
        $treeBuilder = new TreeBuilder('pipeline');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('nodes')
                    ->children()
                        ->integerNode('client_id')->end()
                        ?->scalarNode('client_secret')?->end()
                    ?->end()
                ?->end() // nodes
            ?->end();
        /* @formatter:on */

        return $treeBuilder;
    }
}
