<?php

namespace Vigilant\LaravelHealthchecks\Tests\Checks;

use Illuminate\Support\Facades\Cache;
use Mockery;
use Vigilant\HealthChecksBase\Enums\Status;
use Vigilant\LaravelHealthchecks\Checks\SchedulerCheck;
use Vigilant\LaravelHealthchecks\Tests\TestCase;

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

        Cache::shouldReceive('forget')
            ->once()
            ->with('vigilant_scheduler_missing_since')
            ->andReturn(true);

        $check = SchedulerCheck::make();
        $result = $check->run();

        $this->assertNull($result->key());
        $this->assertEquals(Status::Healthy, $result->status());
        $this->assertEquals('Scheduler is running.', $result->message());
    }

    public function test_scheduler_check_returns_unhealthy_when_heartbeat_is_old(): void
    {
        Cache::shouldReceive('get')
            ->once()
            ->with('vigilant_scheduler_heartbeat')
            ->andReturn(now()->subMinutes(5)->timestamp);

        Cache::shouldReceive('forget')
            ->once()
            ->with('vigilant_scheduler_missing_since')
            ->andReturn(true);

        $check = SchedulerCheck::make();
        $result = $check->run();

        $this->assertNull($result->key());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertStringContainsString('Scheduler last ran', $result->message() ?? '');
    }

    public function test_scheduler_check_returns_unhealthy_when_heartbeat_never_ran(): void
    {
        Cache::shouldReceive('get')
            ->once()
            ->with('vigilant_scheduler_heartbeat')
            ->andReturn(null);

        Cache::shouldReceive('get')
            ->once()
            ->with('vigilant_scheduler_missing_since')
            ->andReturn(now()->subMinutes(5)->timestamp);

        $check = SchedulerCheck::make();
        $result = $check->run();

        $this->assertNull($result->key());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertEquals('Scheduler has never run.', $result->message());
    }

    public function test_scheduler_check_can_customize_max_minutes(): void
    {
        Cache::shouldReceive('get')
            ->once()
            ->with('vigilant_scheduler_heartbeat')
            ->andReturn(now()->subMinutes(3)->timestamp);

        Cache::shouldReceive('forget')
            ->once()
            ->with('vigilant_scheduler_missing_since')
            ->andReturn(true);

        $check = (SchedulerCheck::make())->maxMinutesSinceLastRun(5);
        $result = $check->run();

        $this->assertNull($result->key());
        $this->assertEquals(Status::Healthy, $result->status());
    }

    public function test_scheduler_check_returns_unhealthy_when_cache_throws_exception(): void
    {
        Cache::shouldReceive('get')
            ->once()
            ->with('vigilant_scheduler_heartbeat')
            ->andThrow(new \Exception('Cache error'));

        $check = SchedulerCheck::make();
        $result = $check->run();

        $this->assertNull($result->key());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertStringContainsString('Failed to check scheduler status:', $result->message() ?? '');
    }

    public function test_scheduler_check_is_always_available(): void
    {
        $check = SchedulerCheck::make();

        $this->assertTrue($check->available());
    }

    public function test_scheduler_check_key_method_returns_correct_key(): void
    {
        $check = SchedulerCheck::make();

        $this->assertNull($check->key());
    }
}
