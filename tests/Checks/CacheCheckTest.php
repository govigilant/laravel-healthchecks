<?php

namespace Vigilant\LaravelHealthchecks\Tests\Checks;

use Illuminate\Support\Facades\Cache;
use Mockery;
use Vigilant\LaravelHealthchecks\Checks\CacheCheck;
use Vigilant\LaravelHealthchecks\Tests\TestCase;
use Vigilant\HealthChecksBase\Enums\Status;

class CacheCheckTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_cache_check_returns_healthy_when_cache_works(): void
    {
        $testValue = null;

        Cache::shouldReceive('store')
            ->times(3)
            ->with('array')
            ->andReturnSelf();

        Cache::shouldReceive('put')
            ->once()
            ->withArgs(function ($key, $value, $ttl) use (&$testValue) {
                $testValue = $value;

                return true;
            })
            ->andReturn(true);

        Cache::shouldReceive('get')
            ->once()
            ->andReturnUsing(function () use (&$testValue) {
                return $testValue;
            });

        Cache::shouldReceive('forget')
            ->once()
            ->andReturn(true);

        $check = CacheCheck::make();
        $result = $check->run();

        $this->assertNull($result->key());
        $this->assertEquals(Status::Healthy, $result->status());
        $this->assertEquals('Cache store is healthy.', $result->message());
    }

    public function test_cache_check_returns_unhealthy_when_cache_fails(): void
    {
        Cache::shouldReceive('store')
            ->once()
            ->with('array')
            ->andThrow(new \Exception('Connection refused'));

        $check = CacheCheck::make();
        $result = $check->run();

        $this->assertNull($result->key());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertStringContainsString("Failed to connect to cache store 'array':", $result->message() ?? '');
    }

    public function test_cache_check_returns_unhealthy_when_value_mismatch(): void
    {
        Cache::shouldReceive('store')
            ->times(3)
            ->with('array')
            ->andReturnSelf();

        Cache::shouldReceive('put')
            ->once()
            ->andReturn(true);

        Cache::shouldReceive('get')
            ->once()
            ->andReturn('wrong_value');

        Cache::shouldReceive('forget')
            ->once()
            ->andReturn(true);

        $check = CacheCheck::make();
        $result = $check->run();

        $this->assertNull($result->key());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertEquals('Cache store is not working correctly.', $result->message());
    }

    public function test_cache_check_can_test_specific_store(): void
    {
        $testValue = null;

        Cache::shouldReceive('store')
            ->times(3)
            ->with('redis')
            ->andReturnSelf();

        Cache::shouldReceive('put')
            ->once()
            ->withArgs(function ($key, $value, $ttl) use (&$testValue) {
                $testValue = $value;

                return true;
            })
            ->andReturn(true);

        Cache::shouldReceive('get')
            ->once()
            ->andReturnUsing(function () use (&$testValue) {
                return $testValue;
            });

        Cache::shouldReceive('forget')
            ->once()
            ->andReturn(true);

        $check = (CacheCheck::make())->store('redis');
        $result = $check->run();

        $this->assertEquals('redis', $result->key());
        $this->assertEquals(Status::Healthy, $result->status());
    }

    public function test_cache_check_is_available_when_cache_configured(): void
    {
        config(['cache.default' => 'file']);

        $check = CacheCheck::make();

        $this->assertTrue($check->available());
    }

    public function test_cache_check_is_not_available_when_no_cache_configured(): void
    {
        config(['cache.default' => null]);

        $check = CacheCheck::make();

        $this->assertFalse($check->available());
    }

    public function test_cache_check_key_method_returns_correct_key(): void
    {
        $check = CacheCheck::make();

        $this->assertNull($check->key());
    }
}
