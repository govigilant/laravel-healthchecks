<?php

namespace Vigilant\Healthchecks\Checks;

use Throwable;
use Vigilant\HealthChecksBase\Checks\Check;
use Vigilant\HealthChecksBase\Data\ResultData;
use Vigilant\HealthChecksBase\Enums\Status;

class EnvCheck extends Check
{
    protected string $type = 'environment';

    public function __construct(
        protected array $requiredVariables = []
    ) {}

    public function run(): ResultData
    {
        try {
            $missing = [];

            foreach ($this->requiredVariables as $variable) {
                $value = getenv($variable);
                if ($value === false || $value === '') {
                    $missing[] = $variable;
                }
            }

            if (empty($missing)) {
                return ResultData::make([
                    'type' => $this->type(),
                    'status' => Status::Healthy,
                    'message' => 'All required environment variables are set.',
                ]);
            }

            return ResultData::make([
                'type' => $this->type(),
                'status' => Status::Unhealthy,
                'message' => 'Missing environment variables: '.implode(', ', $missing),
            ]);
        } catch (Throwable $e) {
            return ResultData::make([
                'type' => $this->type(),
                'status' => Status::Unhealthy,
                'message' => 'Failed to check environment variables: '.$e->getMessage(),
            ]);
        }
    }

    public function available(): bool
    {
        return ! empty($this->requiredVariables);
    }
}
