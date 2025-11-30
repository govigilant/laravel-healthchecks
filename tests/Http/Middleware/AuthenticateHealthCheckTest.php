<?php

namespace Vigilant\LaravelHealthchecks\Tests\Http\Middleware;

use Vigilant\LaravelHealthchecks\Tests\TestCase;

class AuthenticateHealthCheckTest extends TestCase
{
    public function test_allows_request_when_no_token_is_configured(): void
    {
        config(['vigilant-healthchecks.token' => 'test-token']);
        config(['vigilant-healthchecks.checks' => []]);
        config(['vigilant-healthchecks.metrics' => []]);

        $response = $this->postJson('/api/vigilant/health', [], [
            'Authorization' => 'Bearer test-token',
        ]);

        $response->assertStatus(200);
    }

    public function test_allows_request_with_valid_bearer_token(): void
    {
        config(['vigilant-healthchecks.token' => 'secret-token']);
        config(['vigilant-healthchecks.checks' => []]);
        config(['vigilant-healthchecks.metrics' => []]);

        $response = $this->postJson('/api/vigilant/health', [], [
            'Authorization' => 'Bearer secret-token',
        ]);

        $response->assertStatus(200);
    }

    public function test_denies_request_with_invalid_bearer_token(): void
    {
        config(['vigilant-healthchecks.token' => 'secret-token']);

        $response = $this->postJson('/api/vigilant/health', [], [
            'Authorization' => 'Bearer wrong-token',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthorized',
        ]);
    }

    public function test_denies_request_without_bearer_token_when_token_is_configured(): void
    {
        config(['vigilant-healthchecks.token' => 'secret-token']);

        $response = $this->postJson('/api/vigilant/health');

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthorized',
        ]);
    }
}
