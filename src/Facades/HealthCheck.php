<?php

namespace Vigilant\LaravelHealthchecks\Facades;

use Illuminate\Support\Facades\Facade;
use Vigilant\HealthChecksBase\Checks\Check;
use Vigilant\HealthChecksBase\Checks\Metric;
use Vigilant\LaravelHealthchecks\HealthCheckRegistry;

/**
 * @method static void registerCheck(Check $check)
 * @method static void registerMetric(Metric $metric)
 * @method static array getChecks()
 * @method static bool isCheckConfigured(string $checkClass)
 * @method static array getMetrics()
 * @method static void clear()
 *
 * @see HealthCheckRegistry
 */
class HealthCheck extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return HealthCheckRegistry::class;
    }
}
