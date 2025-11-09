<?php

namespace Vigilant\Healthchecks\Tests;

use Mockery;
use Vigilant\Healthchecks\Checks\Metrics\CpuLoadMetric;

class CpuLoadCheckTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_cpu_load_check_returns_metric_data(): void
    {
        $check = new CpuLoadMetric;
        $result = $check->measure();

        $this->assertEquals('cpu_load', $result->type());
        $this->assertIsFloat($result->value());
        $this->assertEquals('%', $result->unit());
        $this->assertGreaterThanOrEqual(0, $result->value());
    }

    public function test_cpu_load_check_can_customize_thresholds(): void
    {
        $check = (new CpuLoadMetric)
            ->warningThreshold(50)
            ->criticalThreshold(75);

        $result = $check->measure();

        $this->assertEquals('cpu_load', $result->type());
        $this->assertIsFloat($result->value());
    }

    public function test_cpu_load_check_is_available(): void
    {
        $check = new CpuLoadMetric;

        $this->assertTrue($check->available());
    }

    public function test_cpu_load_check_type_method_returns_correct_type(): void
    {
        $check = new CpuLoadMetric;

        $this->assertEquals('cpu_load', $check->type());
    }

    public function test_cpu_load_check_run_method_returns_result_data(): void
    {
        $check = new CpuLoadMetric;
        $result = $check->run();

        $this->assertEquals('cpu_load', $result->type());
        $this->assertArrayHasKey('value', $result->data());
        $this->assertArrayHasKey('unit', $result->data());
        $this->assertEquals('%', $result->data()['unit']);
    }
}
