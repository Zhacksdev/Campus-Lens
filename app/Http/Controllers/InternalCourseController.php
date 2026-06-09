<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternalCourseController extends Controller
{
    public function difficulty(Request $request): JsonResponse
    {
        return response()->json(Course::query()
            ->when($request->filled('major'), fn ($query) => $query->where('major', $request->string('major')))
            ->when($request->filled('semester'), fn ($query) => $query->where('semester', $request->integer('semester')))
            ->withAvg('reviews', 'difficulty')->withCount('reviews')
            ->get(['id', 'code', 'name', 'major', 'semester']));
    }
}
