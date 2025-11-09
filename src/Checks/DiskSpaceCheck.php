<?php

namespace Vigilant\Healthchecks\Checks;

use Throwable;
use Vigilant\HealthChecksBase\Checks\Check;
use Vigilant\HealthChecksBase\Data\ResultData;
use Vigilant\HealthChecksBase\Enums\Status;

class DiskSpaceCheck extends Check
{
    protected string $type = 'disk_space';

    public function __construct(
        protected ?string $path = null,
        protected int $minFreeSpaceInMb = 1024
    ) {
        $this->path = $this->path ?? base_path();
    }

    public function run(): ResultData
    {
        try {
            $path = $this->path ?? base_path();
            $freeSpace = disk_free_space($path);
            $totalSpace = disk_total_space($path);

            if ($freeSpace === false || $totalSpace === false) {
                return ResultData::make([
                    'type' => $this->type(),
                    'status' => Status::Unhealthy,
                    'message' => "Could not determine disk space for path: {$path}",
                ]);
            }

            $freeSpaceMb = round($freeSpace / 1024 / 1024, 2);
            $totalSpaceMb = round($totalSpace / 1024 / 1024, 2);
            $usedPercentage = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);

            if ($freeSpaceMb < $this->minFreeSpaceInMb) {
                return ResultData::make([
                    'type' => $this->type(),
                    'status' => Status::Unhealthy,
                    'message' => "Low disk space: {$freeSpaceMb}MB free ({$usedPercentage}% used), minimum required: {$this->minFreeSpaceInMb}MB",
                ]);
            }

            return ResultData::make([
                'type' => $this->type(),
                'status' => Status::Healthy,
                'message' => "Disk space is healthy: {$freeSpaceMb}MB free of {$totalSpaceMb}MB ({$usedPercentage}% used)",
            ]);
        } catch (Throwable $e) {
            return ResultData::make([
                'type' => $this->type(),
                'status' => Status::Unhealthy,
                'message' => 'Failed to check disk space: '.$e->getMessage(),
            ]);
        }
    }

    public function available(): bool
    {
        return true;
    }
}
