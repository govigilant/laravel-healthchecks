<?php

namespace Vigilant\Healthchecks\Checks;

use Illuminate\Support\Facades\Redis;
use Throwable;
use Vigilant\HealthChecksBase\Checks\Check;
use Vigilant\HealthChecksBase\Data\ResultData;
use Vigilant\HealthChecksBase\Enums\Status;

class RedisMemoryCheck extends Check
{
    protected string $type = 'redis_memory';

    public function __construct(
        protected ?string $connection = null,
        protected int $warningThresholdPercentage = 80
    ) {}

    public function run(): ResultData
    {
        try {
            $redis = Redis::connection($this->connection);
            $info = $redis->info('memory');

            $usedMemory = (int) ($info['used_memory'] ?? 0);
            $maxMemory = (int) ($info['maxmemory'] ?? 0);

            if ($maxMemory === 0) {
                return ResultData::make([
                    'type' => $this->type(),
                    'status' => Status::Healthy,
                    'message' => $this->connection
                        ? "Redis connection '{$this->connection}' has no maxmemory limit set."
                        : 'Redis has no maxmemory limit set.',
                ]);
            }

            $usedPercentage = round(($usedMemory / $maxMemory) * 100, 2);
            $usedMemoryMb = round($usedMemory / 1024 / 1024, 2);
            $maxMemoryMb = round($maxMemory / 1024 / 1024, 2);

            if ($usedPercentage >= 100) {
                $message = $this->connection
                    ? "Redis connection '{$this->connection}' memory limit exceeded: {$usedMemoryMb}MB / {$maxMemoryMb}MB ({$usedPercentage}%)"
                    : "Redis memory limit exceeded: {$usedMemoryMb}MB / {$maxMemoryMb}MB ({$usedPercentage}%)";

                return ResultData::make([
                    'type' => $this->type(),
                    'status' => Status::Unhealthy,
                    'message' => $message,
                ]);
            }

            if ($usedPercentage >= $this->warningThresholdPercentage) {
                $message = $this->connection
                    ? "Redis connection '{$this->connection}' memory usage high: {$usedMemoryMb}MB / {$maxMemoryMb}MB ({$usedPercentage}%)"
                    : "Redis memory usage high: {$usedMemoryMb}MB / {$maxMemoryMb}MB ({$usedPercentage}%)";

                return ResultData::make([
                    'type' => $this->type(),
                    'status' => Status::Unhealthy,
                    'message' => $message,
                ]);
            }

            $message = $this->connection
                ? "Redis connection '{$this->connection}' memory usage is healthy: {$usedMemoryMb}MB / {$maxMemoryMb}MB ({$usedPercentage}%)"
                : "Redis memory usage is healthy: {$usedMemoryMb}MB / {$maxMemoryMb}MB ({$usedPercentage}%)";

            return ResultData::make([
                'type' => $this->type(),
                'status' => Status::Healthy,
                'message' => $message,
            ]);
        } catch (Throwable $e) {
            $message = $this->connection
                ? "Failed to check Redis memory for connection '{$this->connection}': ".$e->getMessage()
                : 'Failed to check Redis memory: '.$e->getMessage();

            return ResultData::make([
                'type' => $this->type(),
                'status' => Status::Unhealthy,
                'message' => $message,
            ]);
        }
    }

    public function available(): bool
    {
        try {
            return config('database.redis') !== null;
        } catch (Throwable) {
            return false;
        }
    }
}
