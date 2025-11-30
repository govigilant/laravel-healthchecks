<?php

namespace Vigilant\LaravelHealthchecks\Tests\Commands;

use Illuminate\Support\Facades\Cache;
use Mockery;
use Vigilant\LaravelHealthchecks\Tests\TestCase;

class SchedulerHeartbeatCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_scheduler_heartbeat_command_updates_cache(): void
    {
        Cache::shouldReceive('put')
            ->once()
            ->withArgs(function ($key, $value, $ttl) {
                return $key === 'vigilant_scheduler_heartbeat'
                    && is_int($value)
                    && $ttl === 3600;
            })
            ->andReturn(true);

        // @phpstan-ignore method.nonObject
        $this->artisan('vigilant:scheduler-heartbeat')->assertExitCode(0);
    }
}
