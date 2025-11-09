<?php

namespace Vigilant\Healthchecks\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Vigilant\Healthchecks\ServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }
}
