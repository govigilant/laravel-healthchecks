<?php

namespace Vigilant\Healthchecks\Checks;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;
use Vigilant\HealthChecksBase\Checks\Check;
use Vigilant\HealthChecksBase\Data\ResultData;
use Vigilant\HealthChecksBase\Enums\Status;

class CacheCheck extends Check
{
    protected string $type = 'cache_store';

    public function __construct(
        protected ?string $store = null
    ) {}

    public function store(string $store): self
    {
        $this->store = $store;

        return $this;
    }

    public function run(): ResultData
    {
        $store = $this->store ?? config()->get('cache.default');

        try {
            $testKey = 'vigilant_healthcheck_'.Str::random(10);
            $testValue = Str::random(20);

            Cache::store($store)->put($testKey, $testValue, 10);
            $retrieved = Cache::store($store)->get($testKey);
            Cache::store($store)->forget($testKey);

            if ($retrieved === $testValue) {
                $canConnect = true;
                $message = $this->store
                    ? "Cache store '{$store}' is healthy."
                    : 'Cache store is healthy.';
            } else {
                $canConnect = false;
                $message = $this->store
                    ? "Cache store '{$store}' is not working correctly."
                    : 'Cache store is not working correctly.';
            }
        } catch (Throwable $e) {
            $canConnect = false;
            $message = $store
                ? "Failed to connect to cache store '{$store}': ".$e->getMessage()
                : 'Failed to connect to cache store: '.$e->getMessage();
        }

        return ResultData::make([
            'type' => $this->type(),
            'key' => $this->key(),
            'status' => $canConnect ? Status::Healthy : Status::Unhealthy,
            'message' => $message,
        ]);
    }

    public function available(): bool
    {
        try {
            return config('cache.default') !== null;
        } catch (Throwable) {
            return false;
        }
    }

    public function key(): ?string
    {
        return $this->store;
    }
}
