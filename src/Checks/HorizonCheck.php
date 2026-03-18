<?php

namespace Vigilant\LaravelHealthchecks\Checks;

use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use Throwable;
use Vigilant\HealthChecksBase\Checks\Check;
use Vigilant\HealthChecksBase\Data\ResultData;
use Vigilant\HealthChecksBase\Enums\Status;

class HorizonCheck extends Check
{
    protected string $type = 'horizon';

    public function run(): ResultData
    {
        try {
            if (! interface_exists(MasterSupervisorRepository::class)) {
                return ResultData::make([
                    'type' => $this->type(),
                    'status' => Status::Unhealthy,
                    'message' => 'Horizon is not installed.',
                ]);
            }

            $masters = app(MasterSupervisorRepository::class)->all();

            if (empty($masters)) {
                return ResultData::make([
                    'type' => $this->type(),
                    'status' => Status::Unhealthy,
                    'message' => 'Horizon is not running.',
                ]);
            }

            return ResultData::make([
                'type' => $this->type(),
                'status' => Status::Healthy,
                'message' => 'Horizon is running.',
            ]);
        } catch (Throwable $e) {
            return ResultData::make([
                'type' => $this->type(),
                'status' => Status::Unhealthy,
                'message' => 'Failed to check Horizon status: '.$e->getMessage(),
            ]);
        }
    }

    public function available(): bool
    {
        try {
            return interface_exists(MasterSupervisorRepository::class);
        } catch (Throwable) {
            return false;
        }
    }
}
