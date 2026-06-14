<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{course}', [CourseController::class, 'show']);
Route::get('/courses/{course}/reviews', [CourseController::class, 'reviews']);
Route::get('/reviews/{review}', [ReviewController::class, 'show']);
Route::get('/lecturers', [LecturerController::class, 'index']);
Route::get('/lecturers/{lecturer}', [LecturerController::class, 'show']);
Route::middleware('jwt')->group(function () {
    Route::post('/courses', [CourseController::class, 'store']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
    
});
