<?php

namespace Suzunone\Hibana\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Suzunone\Hibana\Console\Commands\GenerateConsole;
use Suzunone\Hibana\Console\Commands\Makes\FactoryMakeConsole;
use Suzunone\Hibana\Console\Commands\Makes\GeneratorMakeConsole;

class HibanaServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $configPath = dirname(__DIR__, 2) . '/config/hibana.php';
        $this->mergeConfigFrom($configPath, 'hibana');

        $this->app->singleton(
            'command.hibana.generate',
            function ($app) {
                return new GenerateConsole($app['config']);
            }
        );

        $this->app->singleton(
            'command.hibana.make.factory',
            function ($app) {
                return new FactoryMakeConsole($app['files']);
            }
        );
        $this->app->singleton(
            'command.hibana.make.generator',
            function ($app) {
                return new GeneratorMakeConsole($app['files']);
            }
        );

        $this->commands(
            'command.hibana.generate',
            'command.hibana.make.factory',
            'command.hibana.make.generator',
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [
            'command.hibana.generate',
            'command.hibana.make.factory',
            'command.hibana.make.generator',
        ];
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $configPath = dirname(__DIR__, 2) . '/config/hibana.php';
        if (function_exists('config_path')) {
            $publishPath = config_path('hibana.php');
        } else {
            $publishPath = base_path('config/hibana.php');
        }
        $this->publishes([$configPath => $publishPath], 'config');
    }
}
