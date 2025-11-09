<?php

namespace Vigilant\Healthchecks\Tests;

use Illuminate\Support\Facades\Cache;
use Mockery;
use Vigilant\Healthchecks\Checks\SchedulerCheck;
use Vigilant\HealthChecksBase\Enums\Status;

class SchedulerCheckTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_scheduler_check_returns_healthy_when_heartbeat_is_recent(): void
    {
        Cache::shouldReceive('get')
            ->once()
            ->with('vigilant_scheduler_heartbeat')
            ->andReturn(now()->timestamp);

        $check = new SchedulerCheck;
        $result = $check->run();

        $this->assertEquals('scheduler', $result->key());
        $this->assertEquals(Status::Healthy, $result->status());
        $this->assertEquals('Scheduler is running.', $result->message());
    }

    public function test_scheduler_check_returns_unhealthy_when_heartbeat_is_old(): void
    {
        Cache::shouldReceive('get')
            ->once()
            ->with('vigilant_scheduler_heartbeat')
            ->andReturn(now()->subMinutes(5)->timestamp);

        $check = new SchedulerCheck;
        $result = $check->run();

        $this->assertEquals('scheduler', $result->key());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertStringContainsString('Scheduler last ran', $result->message() ?? '');
    }

    public function test_scheduler_check_returns_unhealthy_when_heartbeat_never_ran(): void
    {
        Cache::shouldReceive('get')
            ->once()
            ->with('vigilant_scheduler_heartbeat')
            ->andReturn(null);

        $check = new SchedulerCheck;
        $result = $check->run();

        $this->assertEquals('scheduler', $result->key());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertEquals('Scheduler has never run.', $result->message());
    }

    public function test_scheduler_check_can_customize_max_minutes(): void
    {
        Cache::shouldReceive('get')
            ->once()
            ->with('vigilant_scheduler_heartbeat')
            ->andReturn(now()->subMinutes(3)->timestamp);

        $check = (new SchedulerCheck)->maxMinutesSinceLastRun(5);
        $result = $check->run();

        $this->assertEquals('scheduler', $result->key());
        $this->assertEquals(Status::Healthy, $result->status());
    }

    public function test_scheduler_check_returns_unhealthy_when_cache_throws_exception(): void
    {
        Cache::shouldReceive('get')
            ->once()
            ->with('vigilant_scheduler_heartbeat')
            ->andThrow(new \Exception('Cache error'));

        $check = new SchedulerCheck;
        $result = $check->run();

        $this->assertEquals('scheduler', $result->key());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertStringContainsString('Failed to check scheduler status:', $result->message() ?? '');
    }

    public function test_scheduler_check_is_always_available(): void
    {
        $check = new SchedulerCheck;

        $this->assertTrue($check->available());
    }

    public function test_scheduler_check_key_method_returns_correct_key(): void
    {
        $check = new SchedulerCheck;

        $this->assertEquals('scheduler', $check->key());
    }
}
