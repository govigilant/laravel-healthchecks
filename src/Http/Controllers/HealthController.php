<?php

namespace Vigilant\LaravelHealthchecks\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Vigilant\HealthChecksBase\BuildResponse;

class HealthController extends Controller
{
    public function __invoke(BuildResponse $builder): JsonResponse
    {
        $registry = app('vigilant.healthcheck');

        return response()->json(
            $builder->build(
                $registry->getChecks(),
                $registry->getMetrics()
            )
        );
    }
}
