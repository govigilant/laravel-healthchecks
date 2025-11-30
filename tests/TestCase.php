<?php

namespace Vigilant\LaravelHealthchecks\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Vigilant\LaravelHealthchecks\ServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }
}
