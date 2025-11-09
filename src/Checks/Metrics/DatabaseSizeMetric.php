<?php

namespace Vigilant\Healthchecks\Checks\Metrics;

use Illuminate\Support\Facades\DB;
use Throwable;
use Vigilant\HealthChecksBase\Checks\MetricCheck;
use Vigilant\HealthChecksBase\Data\MetricData;

class DatabaseSizeMetric extends MetricCheck
{
    protected string $type = 'database_size';

    protected ?string $connection = null;

    public function connection(?string $connection): self
    {
        $this->connection = $connection;

        return $this;
    }

    public function measure(): MetricData
    {
        try {
            $connection = $this->connection ?? config('database.default');
            $driver = config("database.connections.{$connection}.driver");

            $sizeInMb = match ($driver) {
                'mysql', 'mariadb' => $this->getMySqlSize($connection),
                'pgsql' => $this->getPostgreSqlSize($connection),
                'sqlite' => $this->getSqliteSize($connection),
                default => 0,
            };

            return MetricData::make([
                'type' => $this->type(),
                'value' => round($sizeInMb, 2),
                'unit' => 'MB',
            ]);
        } catch (Throwable) {
            return MetricData::make([
                'type' => $this->type(),
                'value' => 0,
                'unit' => 'MB',
            ]);
        }
    }

    protected function getMySqlSize(?string $connection): float
    {
        $database = config("database.connections.{$connection}.database");

        $result = DB::connection($connection)
            ->select('
                SELECT SUM(data_length + index_length) / 1024 / 1024 AS size
                FROM information_schema.TABLES
                WHERE table_schema = ?
            ', [$database]);

        return (float) ($result[0]->size ?? 0);
    }

    protected function getPostgreSqlSize(?string $connection): float
    {
        $database = config("database.connections.{$connection}.database");

        $result = DB::connection($connection)
            ->select('SELECT pg_database_size(?) / 1024 / 1024 AS size', [$database]);

        return (float) ($result[0]->size ?? 0);
    }

    protected function getSqliteSize(?string $connection): float
    {
        $database = config("database.connections.{$connection}.database");

        if (! file_exists($database)) {
            return 0;
        }

        $sizeInBytes = filesize($database);

        return $sizeInBytes / 1024 / 1024;
    }

    public function available(): bool
    {
        try {
            return config('database.default') !== null;
        } catch (Throwable) {
            return false;
        }
    }
}
