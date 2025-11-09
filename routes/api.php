<?php

use Illuminate\Support\Facades\Route;
use Vigilant\Healthchecks\Http\Controllers\HealthController;

Route::post('/vigilant/health', HealthController::class);
