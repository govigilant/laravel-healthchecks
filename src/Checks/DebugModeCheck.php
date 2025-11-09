<?php

namespace Vigilant\Healthchecks\Checks;

use Throwable;
use Vigilant\HealthChecksBase\Checks\Check;
use Vigilant\HealthChecksBase\Data\ResultData;
use Vigilant\HealthChecksBase\Enums\Status;

class DebugModeCheck extends Check
{
    protected string $type = 'debug_mode';

    public function run(): ResultData
    {
        try {
            $debugMode = config('app.debug');
            $environment = config('app.env');

            if ($debugMode && $environment === 'production') {
                return ResultData::make([
                    'type' => $this->type(),
                    'status' => Status::Unhealthy,
                    'message' => 'Debug mode is enabled in production environment.',
                ]);
            }

            return ResultData::make([
                'type' => $this->type(),
                'status' => Status::Healthy,
                'message' => $debugMode
                    ? "Debug mode is enabled (environment: {$environment})."
                    : 'Debug mode is disabled.',
            ]);
        } catch (Throwable $e) {
            return ResultData::make([
                'type' => $this->type(),
                'status' => Status::Unhealthy,
                'message' => 'Failed to check debug mode: '.$e->getMessage(),
            ]);
        }
    }

    public function available(): bool
    {
        return true;
    }
}
