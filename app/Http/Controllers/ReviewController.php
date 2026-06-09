<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseReview;
use App\Services\RabbitMQPublisher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(private readonly RabbitMQPublisher $publisher) {}

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $course = Course::findOrFail($data['course_id']);
        $previousAverage = (float) ($course->reviews()->avg('difficulty') ?? 0);
        $data['student_id'] = $request->attributes->get('student_id');
        $review = CourseReview::create($data)->load(['course', 'lecturer']);
        $newAverage = (float) $course->reviews()->avg('difficulty');

        $this->publisher->publish('ReviewSubmitted', [
            'review_id' => $review->id, 'course_id' => $course->id,
            'student_id' => $review->student_id, 'major' => $course->major,
            'semester' => $course->semester, 'difficulty' => $review->difficulty,
        ]);

        if (abs($newAverage - $previousAverage) > 0.5) {
            $this->publisher->publish('DifficultyScoreUpdated', [
                'course_id' => $course->id, 'course_code' => $course->code,
                'new_avg_difficulty' => round($newAverage, 2),
                'previous_avg_difficulty' => round($previousAverage, 2),
                'total_reviews' => $course->reviews()->count(),
            ]);
        }

        return response()->json($review, 201);
    }

    public function show(CourseReview $review): JsonResponse
    {
        return response()->json($review->load(['course', 'lecturer']));
    }

    public function update(Request $request, CourseReview $review): JsonResponse
    {
        abort_unless($review->student_id === $request->attributes->get('student_id'), 403);
        $review->update($this->validated($request, true));

        return response()->json($review->fresh(['course', 'lecturer']));
    }

    public function destroy(Request $request, CourseReview $review): JsonResponse
    {
        abort_unless($review->student_id === $request->attributes->get('student_id'), 403);
        $review->delete();

        return response()->json(null, 204);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'course_id' => [$required, 'integer', 'exists:courses,id'],
            'lecturer_id' => ['nullable', 'integer', 'exists:lecturers,id'],
            'difficulty' => [$required, 'integer', 'between:1,10'],
            'teaching_rating' => [$required, 'integer', 'between:1,10'],
            'tips' => ['nullable', 'string'], 'uts_strategy' => ['nullable', 'string'],
            'uas_strategy' => ['nullable', 'string'], 'semester_taken' => ['nullable', 'integer', 'between:1,14'],
            'academic_year' => ['nullable', 'string', 'max:10', 'regex:/^\d{4}\/\d{4}$/'],
        ]);
    }
}
