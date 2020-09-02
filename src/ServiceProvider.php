<?php

namespace Stickee\LaravelSearchEncryptedData;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Stickee\LaravelSearchEncryptedData\Console\Commands\Search;
use Stickee\LaravelSearchEncryptedData\Console\Commands\UpdateSearchable;

/**
 * LaravelSearchEncryptedData service provider
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-search-encrypted-data.php', 'laravel-search-encrypted-data'
        );
    }

    /**
     * Bootstrap any application services
     */
    public function boot(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        // Function not available and 'publish' not relevant in Lumen
        if (function_exists('config_path')) {
            $this->publishes([
                __DIR__ . '/../config/laravel-search-encrypted-data.php' => config_path('laravel-search-encrypted-data.php'),
            ], 'config');
        }

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        $this->commands([
            Search::class,
            UpdateSearchable::class,
        ]);
    }
}
