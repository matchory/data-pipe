<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Integration\Symfony;

use Matchory\DataPipe\Integration\Symfony\DependencyInjection\Compiler\AbstractNodeCollectingCompilerPass as NodePass;
use Matchory\DataPipe\Integration\Symfony\DependencyInjection\Compiler\CollectorCollectingCompilerPass as CollectorPass;
use Matchory\DataPipe\Integration\Symfony\DependencyInjection\Compiler\TransformerCollectingCompilerPass as TransformerPass;
use Matchory\DataPipe\Interfaces\CollectorInterface;
use Matchory\DataPipe\Interfaces\TransformerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder as Builder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use function dirname;

final class DataPipeBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $this->tagCollectors($container);
        $this->tagTransformers($container);
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    /**
     * Tag all collectors we can find in the application definitions.
     * This allows us to add them to the application automatically.
     *
     * @param Builder $container
     */
    private function tagCollectors(Builder $container): void
    {
        $container
            ->registerForAutoconfiguration(CollectorInterface::class)
            ->addTag(CollectorPass::TAG)
            ->addTag(NodePass::TAG, [
                'type' => CollectorInterface::class,
            ]);

        $container->addCompilerPass(new CollectorPass());
    }

    /**
     * Tag all transformers we can find in the application definitions.
     * This allows us to add them to the application automatically.
     *
     * @param Builder $container
     */
    private function tagTransformers(Builder $container): void
    {
        $container
            ->registerForAutoconfiguration(TransformerInterface::class)
            ->addTag(TransformerPass::TAG)
            ->addTag(NodePass::TAG, [
                'type' => TransformerInterface::class,
            ]);

        $container->addCompilerPass(new TransformerPass());
    }
}
