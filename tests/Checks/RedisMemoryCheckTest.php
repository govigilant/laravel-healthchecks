<?php

namespace Vigilant\LaravelHealthchecks\Tests\Checks;

use Illuminate\Support\Facades\Redis;
use Mockery;
use Vigilant\LaravelHealthchecks\Checks\RedisMemoryCheck;
use Vigilant\LaravelHealthchecks\Tests\TestCase;
use Vigilant\HealthChecksBase\Enums\Status;

class RedisMemoryCheckTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_redis_memory_check_returns_healthy_when_no_maxmemory(): void
    {
        $mockRedis = Mockery::mock();
        $mockRedis->shouldReceive('info')
            ->once()
            ->with('memory')
            ->andReturn([
                'used_memory' => 1024 * 1024,
                'maxmemory' => 0,
            ]);

        Redis::shouldReceive('connection')
            ->once()
            ->with(null)
            ->andReturn($mockRedis);

        $check = RedisMemoryCheck::make();
        $result = $check->run();

        $this->assertEquals('redis_memory', $result->type());
        $this->assertEquals(Status::Healthy, $result->status());
        $this->assertStringContainsString('no maxmemory limit set', $result->message() ?? '');
    }

    public function test_redis_memory_check_returns_healthy_when_usage_below_threshold(): void
    {
        $mockRedis = Mockery::mock();
        $mockRedis->shouldReceive('info')
            ->once()
            ->with('memory')
            ->andReturn([
                'used_memory' => 50 * 1024 * 1024,
                'maxmemory' => 100 * 1024 * 1024,
            ]);

        Redis::shouldReceive('connection')
            ->once()
            ->with(null)
            ->andReturn($mockRedis);

        $check = RedisMemoryCheck::make();
        $result = $check->run();

        $this->assertEquals('redis_memory', $result->type());
        $this->assertEquals(Status::Healthy, $result->status());
        $this->assertStringContainsString('memory usage is healthy', $result->message() ?? '');
    }

    public function test_redis_memory_check_returns_unhealthy_when_above_warning_threshold(): void
    {
        $mockRedis = Mockery::mock();
        $mockRedis->shouldReceive('info')
            ->once()
            ->with('memory')
            ->andReturn([
                'used_memory' => 85 * 1024 * 1024,
                'maxmemory' => 100 * 1024 * 1024,
            ]);

        Redis::shouldReceive('connection')
            ->once()
            ->with(null)
            ->andReturn($mockRedis);

        $check = RedisMemoryCheck::make(warningThresholdPercentage: 80);
        $result = $check->run();

        $this->assertEquals('redis_memory', $result->type());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertStringContainsString('memory usage high', $result->message() ?? '');
    }

    public function test_redis_memory_check_returns_unhealthy_when_limit_exceeded(): void
    {
        $mockRedis = Mockery::mock();
        $mockRedis->shouldReceive('info')
            ->once()
            ->with('memory')
            ->andReturn([
                'used_memory' => 105 * 1024 * 1024,
                'maxmemory' => 100 * 1024 * 1024,
            ]);

        Redis::shouldReceive('connection')
            ->once()
            ->with(null)
            ->andReturn($mockRedis);

        $check = RedisMemoryCheck::make();
        $result = $check->run();

        $this->assertEquals('redis_memory', $result->type());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertStringContainsString('memory limit exceeded', $result->message() ?? '');
    }

    public function test_redis_memory_check_can_test_specific_connection(): void
    {
        $mockRedis = Mockery::mock();
        $mockRedis->shouldReceive('info')
            ->once()
            ->with('memory')
            ->andReturn([
                'used_memory' => 50 * 1024 * 1024,
                'maxmemory' => 100 * 1024 * 1024,
            ]);

        Redis::shouldReceive('connection')
            ->once()
            ->with('custom')
            ->andReturn($mockRedis);

        $check = RedisMemoryCheck::make('custom');
        $result = $check->run();

        $this->assertEquals('redis_memory', $result->type());
        $this->assertEquals(Status::Healthy, $result->status());
        $this->assertStringContainsString("'custom'", $result->message() ?? '');
    }

    public function test_redis_memory_check_is_available_when_redis_configured(): void
    {
        config(['cache.default' => 'redis']);

        $check = RedisMemoryCheck::make();

        $this->assertTrue($check->available());
    }

    public function test_redis_memory_check_is_not_available_when_redis_not_configured(): void
    {
        config(['cache.default' => 'file']);

        $check = RedisMemoryCheck::make();

        $this->assertFalse($check->available());
    }

    public function test_redis_memory_check_type_method_returns_correct_type(): void
    {
        $check = RedisMemoryCheck::make();

        $this->assertEquals('redis_memory', $check->type());
    }
}
