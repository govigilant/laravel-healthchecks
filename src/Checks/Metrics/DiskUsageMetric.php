<?php

namespace Vigilant\Healthchecks\Checks\Metrics;

use Throwable;
use Vigilant\HealthChecksBase\Checks\MetricCheck;
use Vigilant\HealthChecksBase\Data\MetricData;

class DiskUsageMetric extends MetricCheck
{
    protected string $type = 'disk_usage';

    protected string $path = '/';

    protected float $warningThreshold = 70;

    protected float $criticalThreshold = 90;

    public function path(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function warningThreshold(float $threshold): self
    {
        $this->warningThreshold = $threshold;

        return $this;
    }

    public function criticalThreshold(float $threshold): self
    {
        $this->criticalThreshold = $threshold;

        return $this;
    }

    public function run(): MetricData
    {
        return $this->measure();
    }

    public function measure(): MetricData
    {
        try {
            $total = disk_total_space($this->path);
            $free = disk_free_space($this->path);

            if ($total === false || $free === false) {
                return MetricData::make([
                    'type' => $this->type(),
                    'key' => $this->key(),
                    'value' => 0,
                    'unit' => '%',
                ]);
            }

            $used = $total - $free;
            $percentage = ($used / $total) * 100;

            return MetricData::make([
                'type' => $this->type(),
                'key' => $this->key(),
                'value' => round($percentage, 2),
                'unit' => '%',
            ]);
        } catch (Throwable) {
            return MetricData::make([
                'type' => $this->type(),
                'key' => $this->key(),
                'value' => 0,
                'unit' => '%',
            ]);
        }
    }

    public function available(): bool
    {
        return function_exists('disk_total_space') && function_exists('disk_free_space');
    }
}
