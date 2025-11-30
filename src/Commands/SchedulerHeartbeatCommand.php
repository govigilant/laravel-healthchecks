<?php

namespace Vigilant\LaravelHealthchecks\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SchedulerHeartbeatCommand extends Command
{
    protected $signature = 'vigilant:scheduler-heartbeat';

    protected $description = 'Schedule hearbeat for healthchecks';

    public function handle(): int
    {
        Cache::put('vigilant_scheduler_heartbeat', now()->timestamp, 3600);

        $this->info('Scheduler heartbeat updated.');

        return self::SUCCESS;
    }
}
