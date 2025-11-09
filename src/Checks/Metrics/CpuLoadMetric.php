<?php

namespace Vigilant\Healthchecks\Checks\Metrics;

use Throwable;
use Vigilant\HealthChecksBase\Checks\MetricCheck;
use Vigilant\HealthChecksBase\Data\MetricData;

class CpuLoadMetric extends MetricCheck
{
    protected string $type = 'cpu_load';

    protected float $warningThreshold = 70;

    protected float $criticalThreshold = 90;

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
            $load = sys_getloadavg();

            if ($load === false) {
                return MetricData::make([
                    'type' => $this->type(),
                    'key' => $this->key(),
                    'value' => 0,
                    'unit' => '%',
                ]);
            }

            $cpuCount = $this->getCpuCount();
            $percentage = ($load[0] / $cpuCount) * 100;

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

    protected function getCpuCount(): int
    {
        if (function_exists('shell_exec')) {
            $cpuCount = shell_exec('nproc');
            if ($cpuCount !== null && $cpuCount !== false) {
                $trimmed = trim($cpuCount);
                if (is_numeric($trimmed)) {
                    return (int) $trimmed;
                }
            }
        }

        return 1;
    }

    public function available(): bool
    {
        return function_exists('sys_getloadavg');
    }
}
