<?php

namespace Vigilant\Healthchecks;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Vigilant\Healthchecks\Checks\QueueCheck;
use Vigilant\Healthchecks\Jobs\QueueHeartbeatJob;
use Vigilant\HealthChecksBase\Data\CheckConfigData;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this
            ->registerConfig();
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
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

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

                if ($this->isCheckConfigured(QueueCheck::class)) {
                    $schedule->job(QueueHeartbeatJob::class)->everyMinute();
                }
            });
        }

        return $this;
    }

    protected function isCheckConfigured(string $checkClass): bool
    {
        $checks = config('vigilant-healthchecks.checks', []);

        /** @var CheckConfigData $check */
        foreach ($checks as $check) {
            if ($check->class === $checkClass) {
                return true;
            }
        }

        return false;
    }
}
