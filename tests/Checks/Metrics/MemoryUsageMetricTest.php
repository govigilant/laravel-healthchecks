<?php

namespace Vigilant\Healthchecks\Tests\Checks\Metrics;

use Vigilant\Healthchecks\Checks\Metrics\MemoryUsageMetric;
use Vigilant\Healthchecks\Tests\TestCase;

class MemoryUsageMetricTest extends TestCase
{
    public function test_memory_usage_metric_returns_value(): void
    {
        $metric = new MemoryUsageMetric;
        $result = $metric->measure();

        $this->assertEquals('memory_usage', $result->type());
        $this->assertEquals('%', $result->unit());
        $this->assertIsFloat($result->value());
        $this->assertGreaterThanOrEqual(0, $result->value());
    }

    public function test_memory_usage_metric_is_available_on_linux(): void
    {
        $metric = new MemoryUsageMetric;

        if (file_exists('/proc/meminfo')) {
            $this->assertTrue($metric->available());
        } else {
            $this->assertFalse($metric->available());
        }
    }

    public function test_memory_usage_metric_type_method_returns_correct_type(): void
    {
        $metric = new MemoryUsageMetric;

        $this->assertEquals('memory_usage', $metric->type());
    }
}
