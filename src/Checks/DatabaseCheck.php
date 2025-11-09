<?php

namespace Vigilant\Healthchecks\Checks;

use Illuminate\Support\Facades\DB;
use Throwable;
use Vigilant\HealthChecksBase\Checks\Check;
use Vigilant\HealthChecksBase\Data\ResultData;
use Vigilant\HealthChecksBase\Enums\Status;

class DatabaseCheck extends Check
{
    protected string $type = 'database_connection';

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
            DB::connection($this->connection)->getPdo();
            $canConnect = true;
            $message = $this->connection
                ? "Database connection '{$this->connection}' is healthy."
                : 'Database connection is healthy.';
        } catch (Throwable $e) {
            $canConnect = false;
            $message = $this->connection
                ? "Failed to connect to database '{$this->connection}': ".$e->getMessage()
                : 'Failed to connect to the database: '.$e->getMessage();
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
            return config('database.default') !== null;
        } catch (Throwable) {
            return false;
        }
    }

    public function key(): ?string
    {
        return 'database_connection';
    }
}
