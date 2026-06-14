<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(Course::query()
            ->when($request->filled('major'), fn($query) => $query->where('major', $request->string('major')))
            ->when($request->filled('semester'), fn($query) => $query->where('semester', $request->integer('semester')))
            ->withAvg('reviews', 'difficulty')->withCount('reviews')->paginate());
    }

    public function show(Course $course): JsonResponse
    {
        return response()->json($course->loadAvg('reviews', 'difficulty')->loadCount('reviews'));
    }

    public function reviews(Course $course): JsonResponse
    {
        return response()->json($course->reviews()->with('lecturer')->latest()->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $course = Course::create($request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:courses,code'],
            'name' => ['required', 'string', 'max:150'],
            'credits' => ['required', 'integer', 'between:1,12'],
            'major' => ['required', 'string', 'max:100'],
            'semester' => ['required', 'integer', 'between:1,14'],
        ]));

        return response()->json($course, 201);
    }
}
