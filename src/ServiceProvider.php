<?php

namespace Vigilant\LaravelHealthchecks;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Vigilant\LaravelHealthchecks\Checks\QueueCheck;
use Vigilant\LaravelHealthchecks\Facades\HealthCheck;
use Vigilant\LaravelHealthchecks\Jobs\QueueHeartbeatJob;
use Vigilant\HealthChecksBase\Checks\DiskSpaceCheck;
use Vigilant\HealthChecksBase\Checks\Metrics\CpuLoadMetric;
use Vigilant\HealthChecksBase\Checks\Metrics\DiskUsageMetric;
use Vigilant\HealthChecksBase\Checks\Metrics\MemoryUsageMetric;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this
            ->registerSingleton()
            ->registerConfig();
    }

    protected function registerSingleton(): static
    {
        $this->app->singleton('vigilant.healthcheck', function () {
            return new HealthCheckRegistry;
        });

        $this->app->singleton(HealthCheckRegistry::class, function ($app) {
            return $app->make('vigilant.healthcheck');
        });

        return $this;
    }

    protected function registerConfig(): static
    {
        $this->mergeConfigFrom(__DIR__.'/../config/vigilant-healthchecks.php', 'vigilant-healthchecks');

        return $this;
    }

    public function boot(): void
    {
        $this
            ->bootRoutes()
            ->bootConfig()
            ->bootMigrations()
            ->bootCommands()
            ->bootRegistrations()
            ->bootSchedule();
    }

    protected function bootRoutes(): static
    {
        if (! $this->app->routesAreCached()) {
            Route::prefix('api')
                ->middleware(config()->array('vigilant-healthchecks.middleware'))
                ->group(fn () => $this->loadRoutesFrom(__DIR__.'/../routes/api.php'));
        }

        return $this;
    }

    protected function bootConfig(): static
    {
        $this->publishes([
            __DIR__.'/../config/vigilant-healthchecks.php' => config_path('vigilant-healthchecks.php'),
        ], 'config');

        return $this;
    }

    protected function bootMigrations(): static
    {
        $path = __DIR__.'/../database/migrations';

        if (is_dir($path)) {
            $this->loadMigrationsFrom($path);
        }

        return $this;
    }

    protected function bootCommands(): static
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\SchedulerHeartbeatCommand::class,
            ]);
        }

        return $this;
    }

    protected function bootSchedule(): static
    {
        if (config('vigilant-healthchecks.schedule', true)) {
            $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
                $schedule->command('vigilant:scheduler-heartbeat')->everyMinute();

                if (HealthCheck::isCheckConfigured(QueueCheck::class)) {
                    $schedule->job(QueueHeartbeatJob::class)->everyMinute();
                }
            });
        }

        return $this;
    }

    protected function bootRegistrations(): static
    {
        if (! config('vigilant-healthchecks.register', true)) {
            return $this;
        }

        $registry = app('vigilant.healthcheck');

        $checks = [
            Checks\DatabaseCheck::class,
            Checks\QueueCheck::class,
            Checks\CacheCheck::class,
            Checks\RedisCheck::class,
            Checks\RedisMemoryCheck::class,
            Checks\StorageCheck::class,
            Checks\DebugModeCheck::class,
            Checks\HorizonCheck::class,
            Checks\SchedulerCheck::class,
            DiskSpaceCheck::class,

        ];

        foreach ($checks as $checkClass) {
            $registry->registerCheck($checkClass::make());
        }

        $metrics = [
            MemoryUsageMetric::class,
            CpuLoadMetric::class,
            DiskUsageMetric::class,
            Checks\Metrics\DatabaseSizeMetric::class,
            Checks\Metrics\LogFileSizeMetric::class,
        ];

        foreach ($metrics as $metricClass) {
            $registry->registerMetric($metricClass::make());
        }

        return $this;
    }
}
