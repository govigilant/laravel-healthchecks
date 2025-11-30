<?php

namespace Vigilant\Healthchecks\Tests\Checks;

use Vigilant\Healthchecks\Tests\TestCase;
use Vigilant\Healthchecks\Checks\StorageCheck;
use Vigilant\HealthChecksBase\Enums\Status;

class StorageCheckTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['filesystems.default' => 'local']);
        config(['filesystems.disks.local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ]]);
    }

    public function test_storage_check_returns_healthy_when_storage_works(): void
    {
        $check = StorageCheck::make();
        $result = $check->run();

        $this->assertEquals('storage', $result->type());
        $this->assertEquals(Status::Healthy, $result->status());
        $this->assertEquals('Storage disk is healthy.', $result->message());
    }

    public function test_storage_check_can_test_specific_disk(): void
    {
        config(['filesystems.disks.test_disk' => [
            'driver' => 'local',
            'root' => storage_path('app/test'),
        ]]);

        if (! is_dir(storage_path('app/test'))) {
            mkdir(storage_path('app/test'), 0755, true);
        }

        $check = StorageCheck::make('test_disk');
        $result = $check->run();

        $this->assertEquals('storage', $result->type());
        $this->assertEquals(Status::Healthy, $result->status());
        $this->assertStringContainsString("Storage disk 'test_disk' is healthy.", $result->message() ?? '');

        if (is_dir(storage_path('app/test'))) {
            rmdir(storage_path('app/test'));
        }
    }

    public function test_storage_check_is_available_when_filesystem_configured(): void
    {
        config(['filesystems.default' => 'local']);

        $check = StorageCheck::make();

        $this->assertTrue($check->available());
    }

    public function test_storage_check_is_not_available_when_no_filesystem_configured(): void
    {
        config(['filesystems.default' => null]);

        $check = StorageCheck::make();

        $this->assertFalse($check->available());
    }

    public function test_storage_check_type_method_returns_correct_type(): void
    {
        $check = StorageCheck::make();

        $this->assertEquals('storage', $check->type());
    }
}
