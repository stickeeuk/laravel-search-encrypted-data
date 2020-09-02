<?php

namespace Stickee\LaravelSearchEncryptedData\Test;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;
use Stickee\LaravelSearchEncryptedData\Seeds\TestSeeder;
use Stickee\LaravelSearchEncryptedData\ServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
        $this->artisan('migrate');

        $this->withFactories(__DIR__ . '/../src/database/factories');
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app The app container
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => 'test_',
        ]);
    }

    /**
     * Get the package's providers
     *
     * @param \Illuminate\Foundation\Application $app The app container
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }
}
