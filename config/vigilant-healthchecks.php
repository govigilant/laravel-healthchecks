<?php

use Vigilant\Healthchecks\Checks\CacheCheck;
use Vigilant\Healthchecks\Checks\DatabaseCheck;
use Vigilant\Healthchecks\Checks\DebugModeCheck;
use Vigilant\Healthchecks\Checks\DiskSpaceCheck;
use Vigilant\Healthchecks\Checks\EnvCheck;
use Vigilant\Healthchecks\Checks\HorizonCheck;
use Vigilant\Healthchecks\Checks\Metrics\CpuLoadMetric;
use Vigilant\Healthchecks\Checks\Metrics\DatabaseSizeMetric;
use Vigilant\Healthchecks\Checks\Metrics\DiskUsageMetric;
use Vigilant\Healthchecks\Checks\Metrics\LogFileSizeMetric;
use Vigilant\Healthchecks\Checks\Metrics\MemoryUsageMetric;
use Vigilant\Healthchecks\Checks\QueueCheck;
use Vigilant\Healthchecks\Checks\RedisCheck;
use Vigilant\Healthchecks\Checks\RedisMemoryCheck;
use Vigilant\Healthchecks\Checks\SchedulerCheck;
use Vigilant\Healthchecks\Checks\StorageCheck;
use Vigilant\Healthchecks\Http\Middleware\AuthenticateMiddleware;

return [
    'checks' => [
        CacheCheck::make(),
        DatabaseCheck::make(),
        DebugModeCheck::make(),
        DiskSpaceCheck::make(),
        EnvCheck::make(),
        HorizonCheck::make(),
        QueueCheck::make(),
        RedisCheck::make(),
        RedisMemoryCheck::make(),
        SchedulerCheck::make(),
        StorageCheck::make(),

    ],

    'metrics' => [
        DiskUsageMetric::make(),
        CpuLoadMetric::make(),
        MemoryUsageMetric::make(),
        DatabaseSizeMetric::make(),
        LogFileSizeMetric::make(),
    ],

    'middleware' => [
        AuthenticateMiddleware::class,
    ],

    'token' => env('VIGILANT_HEALTHCHECK_TOKEN'),

    'schedule' => true,
];
