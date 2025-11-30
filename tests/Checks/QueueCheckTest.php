<?php

namespace Vigilant\Healthchecks\Tests\Checks;

use Illuminate\Support\Facades\Cache;
use Vigilant\Healthchecks\Checks\QueueCheck;
use Vigilant\Healthchecks\Tests\TestCase;
use Vigilant\HealthChecksBase\Enums\Status;

class QueueCheckTest extends TestCase
{
    public function test_queue_check_returns_unhealthy_when_no_heartbeat(): void
    {
        Cache::shouldReceive('get')
            ->once()
            ->with('vigilant_queue_heartbeat')
            ->andReturn(null);

        $check = QueueCheck::make();
        $result = $check->run();

        $this->assertEquals('queue', $result->type());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertEquals('Queue has never processed a job.', $result->message());
    }

    public function test_queue_check_returns_healthy_when_heartbeat_recent(): void
    {
        Cache::shouldReceive('get')
            ->once()
            ->with('vigilant_queue_heartbeat')
            ->andReturn(now()->timestamp);

        $check = QueueCheck::make();
        $result = $check->run();

        $this->assertEquals('queue', $result->type());
        $this->assertEquals(Status::Healthy, $result->status());
        $this->assertEquals('Queue is operational.', $result->message());
    }

    public function test_queue_check_returns_unhealthy_when_heartbeat_too_old(): void
    {
        $oldTimestamp = now()->subMinutes(5)->timestamp;

        Cache::shouldReceive('get')
            ->once()
            ->with('vigilant_queue_heartbeat')
            ->andReturn($oldTimestamp);

        $check = QueueCheck::make(maxMinutesSinceLastRun: 2);
        $result = $check->run();

        $this->assertEquals('queue', $result->type());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertStringContainsString('Queue last processed a job', $result->message() ?? '');
        $this->assertStringContainsString('minutes ago', $result->message() ?? '');
    }

    public function test_queue_check_respects_custom_max_minutes(): void
    {
        $timestamp = now()->subMinutes(3)->timestamp;

        Cache::shouldReceive('get')
            ->once()
            ->with('vigilant_queue_heartbeat')
            ->andReturn($timestamp);

        $check = QueueCheck::make(maxMinutesSinceLastRun: 5);
        $result = $check->run();

        $this->assertEquals('queue', $result->type());
        $this->assertEquals(Status::Healthy, $result->status());
    }

    public function test_queue_check_is_available(): void
    {
        $check = QueueCheck::make();

        $this->assertTrue($check->available());
    }

    public function test_queue_check_type_method_returns_correct_type(): void
    {
        $check = QueueCheck::make();

        $this->assertEquals('queue', $check->type());
    }
}
