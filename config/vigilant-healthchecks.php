<?php

use Vigilant\LaravelHealthchecks\Http\Middleware\AuthenticateMiddleware;

return [

    /* Register all the default checks and metrics */
    'register' => true,

    /* Middleware to protect the healthcheck route */
    'middleware' => [
        AuthenticateMiddleware::class,
    ],

    /* Token to authenticate incoming healthcheck requests */
    'token' => env('VIGILANT_HEALTHCHECK_TOKEN'),

    /* Enable scheduler for heartbeats */
    'schedule' => true,
];
