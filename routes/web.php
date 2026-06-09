<?php

use App\Http\Controllers\InternalCourseController;
use Illuminate\Support\Facades\Route;

Route::get('/internal/courses/difficulty', [InternalCourseController::class, 'difficulty']);

Route::get('/', function () {
    return response()->json(['service' => 'Campus Lens Course Review Service', 'status' => 'ok']);
});
