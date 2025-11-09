<?php

namespace Vigilant\Healthchecks\Tests;

use Vigilant\Healthchecks\Checks\EnvCheck;
use Vigilant\HealthChecksBase\Enums\Status;

class EnvCheckTest extends TestCase
{
    public function test_env_check_returns_healthy_when_all_variables_present(): void
    {
        putenv('TEST_VAR_1=value1');
        putenv('TEST_VAR_2=value2');

        $check = new EnvCheck(['TEST_VAR_1', 'TEST_VAR_2']);
        $result = $check->run();

        $this->assertEquals('environment', $result->type());
        $this->assertEquals(Status::Healthy, $result->status());
        $this->assertEquals('All required environment variables are set.', $result->message());

        putenv('TEST_VAR_1');
        putenv('TEST_VAR_2');
    }

    public function test_env_check_returns_unhealthy_when_variables_missing(): void
    {
        putenv('TEST_VAR_1');
        putenv('TEST_VAR_2');

        $check = new EnvCheck(['TEST_VAR_1', 'TEST_VAR_2']);
        $result = $check->run();

        $this->assertEquals('environment', $result->type());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertStringContainsString('Missing environment variables:', $result->message() ?? '');
        $this->assertStringContainsString('TEST_VAR_1', $result->message() ?? '');
        $this->assertStringContainsString('TEST_VAR_2', $result->message() ?? '');
    }

    public function test_env_check_returns_unhealthy_when_some_variables_missing(): void
    {
        putenv('TEST_VAR_1=value1');
        putenv('TEST_VAR_2');

        $check = new EnvCheck(['TEST_VAR_1', 'TEST_VAR_2']);
        $result = $check->run();

        $this->assertEquals('environment', $result->type());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertStringContainsString('Missing environment variables: TEST_VAR_2', $result->message() ?? '');

        putenv('TEST_VAR_1');
    }

    public function test_env_check_is_available_when_variables_configured(): void
    {
        $check = new EnvCheck(['APP_ENV']);

        $this->assertTrue($check->available());
    }

    public function test_env_check_is_not_available_when_no_variables_configured(): void
    {
        $check = new EnvCheck([]);

        $this->assertFalse($check->available());
    }

    public function test_env_check_type_method_returns_correct_type(): void
    {
        $check = new EnvCheck(['APP_ENV']);

        $this->assertEquals('environment', $check->type());
    }
}
