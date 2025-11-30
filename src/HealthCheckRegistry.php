<?php

namespace Vigilant\Healthchecks;

use Vigilant\HealthChecksBase\Checks\Check;
use Vigilant\HealthChecksBase\Checks\Metric;

class HealthCheckRegistry
{
    /** @var array<Check> */
    protected array $checks = [];

    /** @var array<Metric> */
    protected array $metrics = [];

    public function registerCheck(Check $check): void
    {
        $this->checks[] = $check;
    }

    public function registerMetric(Metric $metric): void
    {
        $this->metrics[] = $metric;
    }

    /**
     * @return array<Check>
     */
    public function getChecks(): array
    {
        return $this->checks;
    }

    public function isCheckConfigured(string $checkClass): bool
    {
        foreach ($this->checks as $check) {
            if ($check instanceof $checkClass) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<Metric>
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }

    public function clear(): void
    {
        $this->checks = [];
        $this->metrics = [];
    }
}
