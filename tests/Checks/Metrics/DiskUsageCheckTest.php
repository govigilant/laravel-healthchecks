<?php

namespace Vigilant\Healthchecks\Tests;

use Mockery;
use Vigilant\Healthchecks\Checks\Metrics\DiskUsageMetric;

class DiskUsageCheckTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_disk_usage_check_returns_healthy_when_usage_is_low(): void
    {
        $check = new DiskUsageMetric;
        $result = $check->measure();

        $this->assertEquals('disk_usage', $result->type());
        $this->assertIsFloat($result->value());
        $this->assertEquals('%', $result->unit());
        $this->assertGreaterThanOrEqual(0, $result->value());
        $this->assertLessThanOrEqual(100, $result->value());
    }

    public function test_disk_usage_check_can_customize_path(): void
    {
        $check = (new DiskUsageMetric)->path('/tmp');
        $result = $check->measure();

        $this->assertEquals('disk_usage', $result->type());
        $this->assertIsFloat($result->value());
    }

    public function test_disk_usage_check_can_customize_thresholds(): void
    {
        $check = (new DiskUsageMetric)
            ->warningThreshold(50)
            ->criticalThreshold(75);

        $result = $check->measure();

        $this->assertEquals('disk_usage', $result->type());
        $this->assertIsFloat($result->value());
    }

    public function test_disk_usage_check_is_available(): void
    {
        $check = new DiskUsageMetric;

        $this->assertTrue($check->available());
    }

    public function test_disk_usage_check_type_method_returns_correct_type(): void
    {
        $check = new DiskUsageMetric;

        $this->assertEquals('disk_usage', $check->type());
    }

    public function test_disk_usage_check_run_method_returns_result_data(): void
    {
        $check = new DiskUsageMetric;
        $result = $check->run();

        $this->assertEquals('disk_usage', $result->type());
        $this->assertArrayHasKey('value', $result->data());
        $this->assertArrayHasKey('unit', $result->data());
        $this->assertEquals('%', $result->data()['unit']);
    }
}
