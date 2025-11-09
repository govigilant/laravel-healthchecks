<?php

namespace Vigilant\Healthchecks\Checks;

use Illuminate\Support\Facades\Redis;
use Throwable;
use Vigilant\HealthChecksBase\Checks\Check;
use Vigilant\HealthChecksBase\Data\ResultData;
use Vigilant\HealthChecksBase\Enums\Status;

class RedisCheck extends Check
{
    protected string $type = 'redis_connection';

    public function __construct(
        protected ?string $connection = null
    ) {}

    public function connection(string $connection): self
    {
        $this->connection = $connection;

        return $this;
    }

    public function run(): ResultData
    {
        try {
            Redis::connection($this->connection)->ping();
            $canConnect = true;
            $message = $this->connection
                ? "Redis connection '{$this->connection}' is healthy."
                : 'Redis connection is healthy.';
        } catch (Throwable $e) {
            $canConnect = false;
            $message = $this->connection
                ? "Failed to connect to Redis '{$this->connection}': ".$e->getMessage()
                : 'Failed to connect to Redis: '.$e->getMessage();
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
            return config('database.redis') !== null;
        } catch (Throwable) {
            return false;
        }
    }

    public function key(): ?string
    {
        return 'redis_connection';
    }
}
