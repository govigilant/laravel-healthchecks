<?php

namespace Vigilant\Healthchecks\Tests;

use Vigilant\Healthchecks\Checks\DiskSpaceCheck;
use Vigilant\HealthChecksBase\Enums\Status;

class DiskSpaceCheckTest extends TestCase
{
    public function test_disk_space_check_returns_healthy_when_sufficient_space(): void
    {
        $check = new DiskSpaceCheck(minFreeSpaceInMb: 1);
        $result = $check->run();

        $this->assertEquals('disk_space', $result->type());
        $this->assertEquals(Status::Healthy, $result->status());
        $this->assertStringContainsString('Disk space is healthy', $result->message() ?? '');
    }

    public function test_disk_space_check_can_use_custom_path(): void
    {
        $check = new DiskSpaceCheck(path: sys_get_temp_dir(), minFreeSpaceInMb: 1);
        $result = $check->run();

        $this->assertEquals('disk_space', $result->type());
        $this->assertEquals(Status::Healthy, $result->status());
    }

    public function test_disk_space_check_is_always_available(): void
    {
        $check = new DiskSpaceCheck;

        $this->assertTrue($check->available());
    }

    public function test_disk_space_check_type_method_returns_correct_type(): void
    {
        $check = new DiskSpaceCheck;

        $this->assertEquals('disk_space', $check->type());
    }
}
