<?php

namespace Vigilant\Healthchecks\Tests;

use Vigilant\Healthchecks\Checks\HorizonCheck;
use Vigilant\HealthChecksBase\Enums\Status;

class HorizonCheckTest extends TestCase
{
    public function test_horizon_check_returns_unhealthy_when_not_installed(): void
    {
        $check = new HorizonCheck;
        $result = $check->run();

        $this->assertEquals('horizon', $result->type());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertEquals('Horizon is not installed.', $result->message());
    }

    public function test_horizon_check_is_not_available_when_not_installed(): void
    {
        $check = new HorizonCheck;

        $this->assertFalse($check->available());
    }

    public function test_horizon_check_type_method_returns_correct_type(): void
    {
        $check = new HorizonCheck;

        $this->assertEquals('horizon', $check->type());
    }
}
