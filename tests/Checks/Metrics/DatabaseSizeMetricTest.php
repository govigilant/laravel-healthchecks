<?php

namespace Vigilant\Healthchecks\Tests\Checks\Metrics;

use Vigilant\Healthchecks\Checks\Metrics\DatabaseSizeMetric;
use Vigilant\Healthchecks\Tests\TestCase;

class DatabaseSizeMetricTest extends TestCase
{
    public function test_database_size_metric_returns_value(): void
    {
        $metric = DatabaseSizeMetric::make();
        $result = $metric->measure();

        $this->assertEquals('database_size', $result->type());
        $this->assertEquals('MB', $result->unit());
        $this->assertIsFloat($result->value());
        $this->assertGreaterThanOrEqual(0, $result->value());
    }

    public function test_database_size_metric_can_use_specific_connection(): void
    {
        $metric = (DatabaseSizeMetric::make())->connection('testing');
        $result = $metric->measure();

        $this->assertEquals('database_size', $result->type());
        $this->assertEquals('MB', $result->unit());
        $this->assertIsFloat($result->value());
        $this->assertGreaterThanOrEqual(0, $result->value());
    }

    public function test_database_size_metric_is_available_when_database_configured(): void
    {
        config(['database.default' => 'testing']);

        $metric = DatabaseSizeMetric::make();

        $this->assertTrue($metric->available());
    }

    public function test_database_size_metric_is_not_available_when_no_database_configured(): void
    {
        config(['database.default' => null]);

        $metric = DatabaseSizeMetric::make();

        $this->assertFalse($metric->available());
    }

    public function test_database_size_metric_type_method_returns_correct_type(): void
    {
        $metric = DatabaseSizeMetric::make();

        $this->assertEquals('database_size', $metric->type());
    }
}
