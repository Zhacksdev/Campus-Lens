<?php

namespace App\Http\Controllers;

use App\Models\Lecturer;
use Illuminate\Http\JsonResponse;

class LecturerController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Lecturer::query()->withCount('reviews')->paginate());
    }

    public function show(Lecturer $lecturer): JsonResponse
    {
        return response()->json($lecturer->loadCount('reviews')->load('reviews.course'));
    }
}
