<?php

namespace Vigilant\Healthchecks\Checks;

use Illuminate\Support\Facades\Cache;
use Throwable;
use Vigilant\HealthChecksBase\Checks\Check;
use Vigilant\HealthChecksBase\Data\ResultData;
use Vigilant\HealthChecksBase\Enums\Status;

class SchedulerCheck extends Check
{
    protected string $type = 'scheduler';

    protected int $maxMinutesSinceLastRun = 2;

    public function maxMinutesSinceLastRun(int $minutes): self
    {
        $this->maxMinutesSinceLastRun = $minutes;

        return $this;
    }

    public function run(): ResultData
    {
        try {
            $lastHeartbeat = Cache::get('vigilant_scheduler_heartbeat');

            if ($lastHeartbeat === null) {
                return ResultData::make([
                    'type' => $this->type(),
                    'key' => $this->key(),
                    'status' => Status::Unhealthy,
                    'message' => 'Scheduler has never run.',
                ]);
            }

            $minutesSinceLastRun = (now()->timestamp - $lastHeartbeat) / 60;

            if ($minutesSinceLastRun > $this->maxMinutesSinceLastRun) {
                return ResultData::make([
                    'type' => $this->type(),
                    'key' => $this->key(),
                    'status' => Status::Unhealthy,
                    'message' => sprintf(
                        'Scheduler last ran %d minutes ago.',
                        (int) $minutesSinceLastRun
                    ),
                ]);
            }

            return ResultData::make([
                'type' => $this->type(),
                'key' => $this->key(),
                'status' => Status::Healthy,
                'message' => 'Scheduler is running.',
            ]);
        } catch (Throwable $e) {
            return ResultData::make([
                'type' => $this->type(),
                'key' => $this->key(),
                'status' => Status::Unhealthy,
                'message' => 'Failed to check scheduler status: '.$e->getMessage(),
            ]);
        }
    }

    public function available(): bool
    {
        return true;
    }

    public function key(): ?string
    {
        return 'scheduler';
    }
}
