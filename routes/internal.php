<?php

use App\Http\Controllers\RoadmapController;
use Illuminate\Support\Facades\Route;

Route::get('/internal/roadmap/phases', [RoadmapController::class, 'internalPhases']);
