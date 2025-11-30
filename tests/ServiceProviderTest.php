<?php

namespace Vigilant\LaravelHealthchecks\Tests;

use Illuminate\Support\Facades\Artisan;

class ServiceProviderTest extends TestCase
{
    public function test_service_provider_registers_commands(): void
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('vigilant:scheduler-heartbeat', $commands);
    }

    public function test_service_provider_loads_config(): void
    {
        $config = config('vigilant-healthchecks');

        $this->assertIsArray($config);
    }

    public function test_service_provider_registers_routes(): void
    {
        $routeCollection = app('router')->getRoutes();
        $routes = [];

        /** @var \Illuminate\Routing\Route $route */
        foreach ($routeCollection->getRoutes() as $route) {
            $routes[] = $route->uri();
        }

        $this->assertContains('api/vigilant/health', $routes);
    }
}
