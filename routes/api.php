<?php

use App\Http\Controllers\CareerController;
use App\Http\Controllers\RoadmapController;
use Illuminate\Support\Facades\Route;

Route::get('/careers', [CareerController::class, 'index']);
Route::get('/careers/{careerPath}', [CareerController::class, 'show']);
Route::post('/careers', [CareerController::class, 'store']);
Route::post('/careers/{careerPath}/phases', [RoadmapController::class, 'storePhase']);

Route::get('/roadmap', [RoadmapController::class, 'roadmap']);
Route::get('/certifications', [RoadmapController::class, 'certifications']);
