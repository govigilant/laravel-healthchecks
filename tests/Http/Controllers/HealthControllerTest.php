<?php

namespace Vigilant\Healthchecks\Tests\Http\Controllers;

use Vigilant\Healthchecks\Tests\TestCase;

class HealthControllerTest extends TestCase
{
    public function test_health_endpoint_returns_json_response(): void
    {
        config(['vigilant-healthchecks.token' => 'test-token']);
        config(['vigilant-healthchecks.checks' => []]);
        config(['vigilant-healthchecks.metrics' => []]);

        $response = $this->postJson('/api/vigilant/health', [], [
            'Authorization' => 'Bearer test-token',
        ]);

        $response->assertStatus(200);
        $response->assertJson([]);
    }
}
