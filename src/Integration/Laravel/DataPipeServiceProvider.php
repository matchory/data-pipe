<?php

declare(strict_types=1);

namespace Matchory\DataPipe\Integration\Laravel;

use Illuminate\Support\ServiceProvider;
use JetBrains\PhpStorm\Pure;

use function dirname;
use function implode;

use const DIRECTORY_SEPARATOR as DS;

final class DataPipeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->configure();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerPipelineRegistry();
        $this->registerPipeline();
    }

    private function registerPipelineRegistry(): void
    {
    }

    private function registerPipeline(): void
    {
    }

    #[Pure]
    private function basePath(): string
    {
        return dirname(__DIR__) . DS;
    }

    #[Pure]
    private function configPath(): string
    {
        return implode(DS, [
                $this->basePath(),
                'Resources',
                'config',
            ]) . DS;
    }

    private function configure(): void
    {
        $this->mergeConfigFrom(
            $this->configPath() . 'pipeline.php',
            'pipeline'
        );

        $this->publishes([
            $this->configPath() => $this->app->configPath(),
        ], 'pipeline.config');
    }
}
