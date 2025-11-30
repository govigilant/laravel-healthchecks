<?php

namespace Vigilant\Healthchecks\Tests\Checks;

use Illuminate\Support\Facades\Redis;
use Mockery;
use Vigilant\Healthchecks\Checks\RedisCheck;
use Vigilant\Healthchecks\Tests\TestCase;
use Vigilant\HealthChecksBase\Enums\Status;

class RedisCheckTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_redis_check_returns_healthy_when_connection_succeeds(): void
    {
        Redis::shouldReceive('connection')
            ->once()
            ->with(null)
            ->andReturnSelf();

        Redis::shouldReceive('ping')
            ->once()
            ->andReturn(true);

        $check = RedisCheck::make();
        $result = $check->run();

        $this->assertNull($result->key());
        $this->assertEquals(Status::Healthy, $result->status());
        $this->assertEquals('Redis connection is healthy.', $result->message());
    }

    public function test_redis_check_returns_unhealthy_when_connection_fails(): void
    {
        Redis::shouldReceive('connection')
            ->once()
            ->with(null)
            ->andThrow(new \Exception('Connection refused'));

        $check = RedisCheck::make();
        $result = $check->run();

        $this->assertNull($result->key());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertStringContainsString('Failed to connect to Redis:', $result->message() ?? '');
    }

    public function test_redis_check_can_test_specific_connection(): void
    {
        Redis::shouldReceive('connection')
            ->once()
            ->with('cache')
            ->andReturnSelf();

        Redis::shouldReceive('ping')
            ->once()
            ->andReturn(true);

        $check = (RedisCheck::make())->connection('cache');
        $result = $check->run();

        $this->assertEquals('cache', $result->key());
        $this->assertEquals(Status::Healthy, $result->status());
    }

    public function test_redis_check_is_available_when_redis_configured(): void
    {
        config(['cache.default' => 'redis']);

        $check = RedisCheck::make();

        $this->assertTrue($check->available());
    }

    public function test_redis_check_is_not_available_when_no_redis_configured(): void
    {
        config(['cache.default' => 'file']);

        $check = RedisCheck::make();

        $this->assertFalse($check->available());
    }

    public function test_redis_check_key_method_returns_correct_key(): void
    {
        $check = RedisCheck::make();

        $this->assertNull($check->key());
    }
}
