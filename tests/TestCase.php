<?php

declare(strict_types=1);

namespace Hekal\ClassBridge\Tests;

use Hekal\ClassBridge\ClassBridgeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ClassBridgeServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('classbridge.default', 'fake');
        $app['config']->set('classbridge.api.enabled', false);
        $app['config']->set('classbridge.multi_tenant.enabled', true);
        $app['config']->set('classbridge.tokens.signing_key', 'testing-secret-key');
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
