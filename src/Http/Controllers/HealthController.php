<?php

namespace Vigilant\Healthchecks\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Vigilant\HealthChecksBase\BuildResponse;

class HealthController extends Controller
{
    public function __invoke(BuildResponse $builder): JsonResponse
    {
        return response()->json(
            $builder->build(
                config()->get('vigilant-healthchecks.checks', []),
                config()->get('vigilant-healthchecks.metrics', [])
            )
        );
    }
}
