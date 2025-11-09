<?php

namespace Vigilant\Healthchecks\Checks\Metrics;

use Throwable;
use Vigilant\HealthChecksBase\Checks\MetricCheck;
use Vigilant\HealthChecksBase\Data\MetricData;

class MemoryUsageMetric extends MetricCheck
{
    protected string $type = 'memory_usage';

    public function measure(): MetricData
    {
        try {
            $memInfo = $this->getMemoryInfo();

            if ($memInfo === null) {
                return MetricData::make([
                    'type' => $this->type(),
                    'value' => 0,
                    'unit' => '%',
                ]);
            }

            $percentage = (($memInfo['total'] - $memInfo['available']) / $memInfo['total']) * 100;

            return MetricData::make([
                'type' => $this->type(),
                'value' => round($percentage, 2),
                'unit' => '%',
            ]);
        } catch (Throwable) {
            return MetricData::make([
                'type' => $this->type(),
                'value' => 0,
                'unit' => '%',
            ]);
        }
    }

    protected function getMemoryInfo(): ?array
    {
        if (! file_exists('/proc/meminfo')) {
            return null;
        }

        $meminfo = file_get_contents('/proc/meminfo');
        if ($meminfo === false) {
            return null;
        }

        preg_match('/MemTotal:\s+(\d+)/', $meminfo, $totalMatch);
        preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $availableMatch);

        if (empty($totalMatch[1]) || empty($availableMatch[1])) {
            return null;
        }

        return [
            'total' => (int) $totalMatch[1],
            'available' => (int) $availableMatch[1],
        ];
    }

    public function available(): bool
    {
        return file_exists('/proc/meminfo');
    }
}
