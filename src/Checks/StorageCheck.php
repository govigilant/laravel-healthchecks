<?php

namespace Vigilant\LaravelHealthchecks\Checks;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use Vigilant\HealthChecksBase\Checks\Check;
use Vigilant\HealthChecksBase\Data\ResultData;
use Vigilant\HealthChecksBase\Enums\Status;

class StorageCheck extends Check
{
    protected string $type = 'storage';

    public function __construct(
        protected ?string $disk = null
    ) {}

    public function run(): ResultData
    {
        try {
            $testFile = 'vigilant_healthcheck_'.Str::random(10).'.txt';
            $testContent = Str::random(20);

            $storage = $this->disk ? Storage::disk($this->disk) : Storage::disk();

            $storage->put($testFile, $testContent);
            $retrieved = $storage->get($testFile);
            $storage->delete($testFile);

            if ($retrieved === $testContent) {
                $canWrite = true;
                $message = $this->disk
                    ? "Storage disk '{$this->disk}' is healthy."
                    : 'Storage disk is healthy.';
            } else {
                $canWrite = false;
                $message = $this->disk
                    ? "Storage disk '{$this->disk}' is not working correctly."
                    : 'Storage disk is not working correctly.';
            }
        } catch (Throwable $e) {
            $canWrite = false;
            $message = $this->disk
                ? "Failed to write to storage disk '{$this->disk}': ".$e->getMessage()
                : 'Failed to write to storage disk: '.$e->getMessage();
        }

        return ResultData::make([
            'type' => $this->type(),
            'status' => $canWrite ? Status::Healthy : Status::Unhealthy,
            'message' => $message,
        ]);
    }

    public function available(): bool
    {
        try {
            return config('filesystems.default') !== null;
        } catch (Throwable) {
            return false;
        }
    }

    public function key(): ?string
    {
        return $this->disk;
    }
}
