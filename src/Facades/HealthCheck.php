<?php

namespace Vigilant\Healthchecks\Facades;

use Illuminate\Support\Facades\Facade;
use Vigilant\HealthChecksBase\Checks\Check;
use Vigilant\HealthChecksBase\Checks\Metric;

/**
 * @method static void registerCheck(Check $check)
 * @method static void registerMetric(Metric $metric)
 * @method static array getChecks()
 * @method static bool isCheckConfigured(string $checkClass)
 * @method static array getMetrics()
 * @method static void clear()
 *
 * @see \Vigilant\Healthchecks\HealthCheckRegistry
 */
class HealthCheck extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'vigilant.healthcheck';
    }
}
