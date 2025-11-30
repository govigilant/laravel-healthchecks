<?php

use Illuminate\Support\Facades\Route;
use Vigilant\LaravelHealthchecks\Http\Controllers\HealthController;

Route::post('/vigilant/health', HealthController::class);
