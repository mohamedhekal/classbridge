<?php

declare(strict_types=1);

namespace Hekal\ClassBridge;

use Hekal\ClassBridge\Contracts\ClassroomProvider;
use Hekal\ClassBridge\Services\AnalyticsService;
use Hekal\ClassBridge\Services\AttendanceService;
use Hekal\ClassBridge\Services\FeatureManifest;
use Hekal\ClassBridge\Services\JoinTokenService;
use Hekal\ClassBridge\Services\PollService;
use Hekal\ClassBridge\Services\QuestionService;
use Hekal\ClassBridge\Services\SessionStateMachine;
use Hekal\ClassBridge\Services\WaitingRoomService;
use Hekal\ClassBridge\Services\WhiteboardService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class ClassBridgeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/classbridge.php', 'classbridge');

        $this->app->singleton(ClassBridgeManager::class, function (Application $app): ClassBridgeManager {
            return new ClassBridgeManager($app);
        });

        $this->app->alias(ClassBridgeManager::class, 'classbridge');

        $this->app->singleton(SessionStateMachine::class);
        $this->app->singleton(FeatureManifest::class);
        $this->app->singleton(JoinTokenService::class);
        $this->app->singleton(WaitingRoomService::class);
        $this->app->singleton(AttendanceService::class);
        $this->app->singleton(PollService::class);
        $this->app->singleton(QuestionService::class);
        $this->app->singleton(WhiteboardService::class);
        $this->app->singleton(AnalyticsService::class);

        $this->app->bind(ClassroomProvider::class, function (Application $app): ClassroomProvider {
            return $app->make(ClassBridgeManager::class)->driver();
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/classbridge.php' => config_path('classbridge.php'),
        ], 'classbridge-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'classbridge-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'classbridge');

        $this->registerRoutes();
        $this->registerEmbedRoutes();
    }

    private function registerRoutes(): void
    {
        if (! config('classbridge.api.enabled')) {
            return;
        }

        /** @var Router $router */
        $router = $this->app->make(Router::class);

        $router->middleware((array) config('classbridge.api.middleware', ['api']))
            ->prefix((string) config('classbridge.api.prefix', 'api/classbridge'))
            ->group(__DIR__.'/../routes/classbridge.php');
    }

    private function registerEmbedRoutes(): void
    {
        if (! config('classbridge.embed.enabled', false)) {
            return;
        }

        /** @var Router $router */
        $router = $this->app->make(Router::class);

        $router->middleware((array) config('classbridge.embed.middleware', ['web']))
            ->prefix((string) config('classbridge.embed.prefix', 'embed/classbridge'))
            ->group(__DIR__.'/../routes/embed.php');
    }
}
