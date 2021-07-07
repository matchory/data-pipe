<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Integration\Symfony\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\OutOfBoundsException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DataPipeExtension extends ConfigurableExtension
{
    /**
     * @param array            $mergedConfig
     * @param ContainerBuilder $container
     *
     * @throws OutOfBoundsException
     * @throws ServiceNotFoundException
     * @throws Exception
     */
    protected function loadInternal(
        array $mergedConfig,
        ContainerBuilder $container
    ): void {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yaml');
        $loader->load('commands.yaml');

        #$definition = $container->getDefinition('matchory.pipeline.registry');
        #$definition = $container->getDefinition('matchory.pipeline.twitter_client');
        #$definition->replaceArgument(0, $mergedConfig['twitter']['client_id'] ?? null);
    }
}
