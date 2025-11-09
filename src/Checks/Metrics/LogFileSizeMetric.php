<?php

namespace Vigilant\Healthchecks\Checks\Metrics;

use Throwable;
use Vigilant\HealthChecksBase\Checks\Metric;
use Vigilant\HealthChecksBase\Data\MetricData;

class LogFileSizeMetric extends Metric
{
    protected string $type = 'log_file_size';

    protected ?string $channel = null;

    public function channel(?string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function measure(): MetricData
    {
        try {
            $filePath = $this->getLogFilePath();

            if ($filePath === null || ! file_exists($filePath)) {
                return MetricData::make([
                    'type' => $this->type(),
                    'value' => 0.0,
                    'unit' => 'MB',
                ]);
            }

            $sizeInBytes = filesize($filePath);
            $sizeInMb = round($sizeInBytes / 1024 / 1024, 2);

            return MetricData::make([
                'type' => $this->type(),
                'value' => $sizeInMb,
                'unit' => 'MB',
            ]);
        } catch (Throwable) {
            return MetricData::make([
                'type' => $this->type(),
                'value' => 0.0,
                'unit' => 'MB',
            ]);
        }
    }

    protected function getLogFilePath(): ?string
    {
        try {
            $channel = $this->channel ?? config('logging.default');
            $channelConfig = config("logging.channels.{$channel}");

            if (! $channelConfig) {
                return null;
            }

            $driver = $channelConfig['driver'] ?? null;

            return match ($driver) {
                'single' => $channelConfig['path'] ?? storage_path('logs/laravel.log'),
                'daily' => $this->getDailyLogPath($channelConfig),
                'stack' => $this->getStackLogPath($channelConfig),
                default => null,
            };
        } catch (Throwable) {
            return null;
        }
    }

    protected function getDailyLogPath(array $config): string
    {
        $path = $config['path'] ?? storage_path('logs/laravel.log');
        $date = now()->format('Y-m-d');

        return str_replace('.log', "-{$date}.log", $path);
    }

    protected function getStackLogPath(array $config): ?string
    {
        $channels = $config['channels'] ?? [];

        foreach ($channels as $channelName) {
            $channelConfig = config("logging.channels.{$channelName}");

            if (! $channelConfig) {
                continue;
            }

            $driver = $channelConfig['driver'] ?? null;

            if (in_array($driver, ['single', 'daily'])) {
                if ($driver === 'daily') {
                    return $this->getDailyLogPath($channelConfig);
                }

                return $channelConfig['path'] ?? storage_path('logs/laravel.log');
            }
        }

        return null;
    }

    public function available(): bool
    {
        try {
            return config('logging.default') !== null;
        } catch (Throwable) {
            return false;
        }
    }
}
