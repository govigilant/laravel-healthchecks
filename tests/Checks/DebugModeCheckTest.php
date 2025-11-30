<?php

namespace Vigilant\LaravelHealthchecks\Tests\Checks;

use Vigilant\HealthChecksBase\Enums\Status;
use Vigilant\LaravelHealthchecks\Checks\DebugModeCheck;
use Vigilant\LaravelHealthchecks\Tests\TestCase;

class DebugModeCheckTest extends TestCase
{
    public function test_debug_mode_check_returns_unhealthy_when_debug_enabled_in_production(): void
    {
        config(['app.debug' => true]);
        config(['app.env' => 'production']);

        $check = DebugModeCheck::make();
        $result = $check->run();

        $this->assertEquals('debug_mode', $result->type());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertEquals('Debug mode is enabled in production environment.', $result->message());
    }

    public function test_debug_mode_check_returns_healthy_when_debug_disabled(): void
    {
        config(['app.debug' => false]);
        config(['app.env' => 'production']);

        $check = DebugModeCheck::make();
        $result = $check->run();

        $this->assertEquals('debug_mode', $result->type());
        $this->assertEquals(Status::Healthy, $result->status());
        $this->assertEquals('Debug mode is disabled.', $result->message());
    }

    public function test_debug_mode_check_returns_healthy_when_debug_enabled_in_non_production(): void
    {
        config(['app.debug' => true]);
        config(['app.env' => 'local']);

        $check = DebugModeCheck::make();
        $result = $check->run();

        $this->assertEquals('debug_mode', $result->type());
        $this->assertEquals(Status::Healthy, $result->status());
        $this->assertEquals('Debug mode is enabled (environment: local).', $result->message());
    }

    public function test_debug_mode_check_is_always_available(): void
    {
        $check = DebugModeCheck::make();

        $this->assertTrue($check->available());
    }

    public function test_debug_mode_check_type_method_returns_correct_type(): void
    {
        $check = DebugModeCheck::make();

        $this->assertEquals('debug_mode', $check->type());
    }
}
