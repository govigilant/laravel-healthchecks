<?php

namespace Vigilant\LaravelHealthchecks\Tests\Jobs;

use Illuminate\Support\Facades\Cache;
use Vigilant\LaravelHealthchecks\Jobs\QueueHeartbeatJob;
use Vigilant\LaravelHealthchecks\Tests\TestCase;

class QueueHeartbeatJobTest extends TestCase
{
    public function test_queue_heartbeat_job_updates_cache(): void
    {
        Cache::shouldReceive('put')
            ->once()
            ->withArgs(function ($key, $value, $ttl) {
                return $key === 'vigilant_queue_heartbeat'
                    && is_int($value)
                    && $ttl === 3600;
            })
            ->andReturn(true);

        $job = new QueueHeartbeatJob;
        $job->handle();

        $this->addToAssertionCount(1);
    }
}
