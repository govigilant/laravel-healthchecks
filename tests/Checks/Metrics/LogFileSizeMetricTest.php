<?php

namespace Vigilant\Healthchecks\Tests\Checks\Metrics;

use Vigilant\Healthchecks\Checks\Metrics\LogFileSizeMetric;
use Vigilant\Healthchecks\Tests\TestCase;

class LogFileSizeMetricTest extends TestCase
{
    public function test_log_file_size_metric_returns_value(): void
    {
        $metric = new LogFileSizeMetric;
        $result = $metric->measure();

        $this->assertEquals('log_file_size', $result->type());
        $this->assertEquals('MB', $result->unit());
        $this->assertIsFloat($result->value());
        $this->assertGreaterThanOrEqual(0, $result->value());
    }

    public function test_log_file_size_metric_returns_zero_when_file_not_exists(): void
    {
        config(['logging.default' => 'nonexistent']);
        config(['logging.channels.nonexistent' => [
            'driver' => 'single',
            'path' => storage_path('logs/nonexistent.log'),
        ]]);

        $metric = new LogFileSizeMetric;
        $result = $metric->measure();

        $this->assertEquals('log_file_size', $result->type());
        $this->assertEquals('MB', $result->unit());
        $this->assertEquals(0, $result->value());
    }

    public function test_log_file_size_metric_can_use_specific_channel(): void
    {
        config(['logging.channels.test' => [
            'driver' => 'single',
            'path' => storage_path('logs/test.log'),
        ]]);

        $metric = (new LogFileSizeMetric)->channel('test');
        $result = $metric->measure();

        $this->assertEquals('log_file_size', $result->type());
        $this->assertEquals('MB', $result->unit());
        $this->assertGreaterThanOrEqual(0, $result->value());
    }

    public function test_log_file_size_metric_is_available_when_logging_configured(): void
    {
        config(['logging.default' => 'stack']);

        $metric = new LogFileSizeMetric;

        $this->assertTrue($metric->available());
    }

    public function test_log_file_size_metric_is_not_available_when_no_logging_configured(): void
    {
        config(['logging.default' => null]);

        $metric = new LogFileSizeMetric;

        $this->assertFalse($metric->available());
    }

    public function test_log_file_size_metric_type_method_returns_correct_type(): void
    {
        $metric = new LogFileSizeMetric;

        $this->assertEquals('log_file_size', $metric->type());
    }
}
