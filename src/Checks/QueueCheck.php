<?php

namespace Vigilant\LaravelHealthchecks\Checks;

use Illuminate\Support\Facades\Cache;
use Throwable;
use Vigilant\HealthChecksBase\Checks\Check;
use Vigilant\HealthChecksBase\Data\ResultData;
use Vigilant\HealthChecksBase\Enums\Status;

class QueueCheck extends Check
{
    protected string $type = 'queue';

    public function __construct(
        protected int $maxMinutesSinceLastRun = 2
    ) {}

    public function run(): ResultData
    {
        try {
            $lastHeartbeat = Cache::get('vigilant_queue_heartbeat');

            if ($lastHeartbeat === null) {
                return ResultData::make([
                    'type' => $this->type(),
                    'status' => Status::Unhealthy,
                    'message' => 'Queue has never processed a job.',
                ]);
            }

            $minutesSinceLastRun = (now()->timestamp - $lastHeartbeat) / 60;

            if ($minutesSinceLastRun > $this->maxMinutesSinceLastRun) {
                return ResultData::make([
                    'type' => $this->type(),
                    'status' => Status::Unhealthy,
                    'message' => sprintf(
                        'Queue last processed a job %d minutes ago.',
                        (int) $minutesSinceLastRun
                    ),
                ]);
            }

            return ResultData::make([
                'type' => $this->type(),
                'status' => Status::Healthy,
                'message' => 'Queue is operational.',
            ]);
        } catch (Throwable $e) {
            return ResultData::make([
                'type' => $this->type(),
                'status' => Status::Unhealthy,
                'message' => 'Failed to check queue status: '.$e->getMessage(),
            ]);
        }
    }

    public function available(): bool
    {
        try {
            return interface_exists(\Illuminate\Contracts\Queue\Queue::class);
        } catch (Throwable) {
            return false;
        }
    }
}
