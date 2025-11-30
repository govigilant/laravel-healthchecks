<?php

namespace Vigilant\Healthchecks\Tests\Checks;

use Vigilant\Healthchecks\Tests\TestCase;
use Mockery;
use Vigilant\Healthchecks\Checks\DatabaseCheck;
use Vigilant\HealthChecksBase\Enums\Status;

class DatabaseCheckTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_database_check_returns_healthy_when_connection_succeeds(): void
    {
        $check = DatabaseCheck::make();
        $result = $check->run();

        $this->assertNull($result->key());
        $this->assertEquals(Status::Healthy, $result->status());
        $this->assertEquals('Database connection is healthy.', $result->message());
    }

    public function test_database_check_returns_unhealthy_when_connection_fails(): void
    {
        config(['database.default' => 'invalid']);
        config(['database.connections.invalid' => [
            'driver' => 'mysql',
            'host' => 'invalid-host',
            'database' => 'invalid',
            'username' => 'invalid',
            'password' => 'invalid',
        ]]);

        $check = (DatabaseCheck::make())->connection('invalid');
        $result = $check->run();

        $this->assertEquals('invalid', $result->key());
        $this->assertEquals(Status::Unhealthy, $result->status());
        $this->assertStringContainsString("Failed to connect to database 'invalid':", $result->message() ?? '');
    }

    public function test_database_check_can_test_specific_connection(): void
    {
        $check = (DatabaseCheck::make())->connection('testing');
        $result = $check->run();

        $this->assertEquals('testing', $result->key());
        $this->assertEquals(Status::Healthy, $result->status());
    }

    public function test_database_check_is_available_when_database_configured(): void
    {
        config(['database.default' => 'testing']);

        $check = DatabaseCheck::make();

        $this->assertTrue($check->available());
    }

    public function test_database_check_is_not_available_when_no_database_configured(): void
    {
        config(['database.default' => null]);

        $check = DatabaseCheck::make();

        $this->assertFalse($check->available());
    }

    public function test_database_check_key_method_returns_correct_key(): void
    {
        $check = DatabaseCheck::make();

        $this->assertNull($check->key());
    }
}
