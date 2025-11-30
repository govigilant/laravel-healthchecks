<?php

namespace Vigilant\LaravelHealthchecks\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $token = config('vigilant-healthchecks.token');

        abort_if(blank($token), 401, 'Unauthorized');

        $bearerToken = $request->bearerToken();

        if ($bearerToken !== $token) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
