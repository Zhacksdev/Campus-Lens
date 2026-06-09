<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseReview;
use App\Services\RabbitMQPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class CourseReviewApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_courses_include_average_difficulty_and_can_be_filtered(): void
    {
        $course = $this->course();
        CourseReview::create([
            'course_id' => $course->id, 'student_id' => 'student-1',
            'difficulty' => 8, 'teaching_rating' => 9,
        ]);

        $this->getJson('/api/courses?major=Informatika&semester=4')
            ->assertOk()->assertJsonPath('data.0.code', 'IF301')
            ->assertJsonPath('data.0.reviews_avg_difficulty', 8);
    }

    public function test_submit_review_requires_jwt_and_publishes_events(): void
    {
        $course = $this->course();
        $payload = ['course_id' => $course->id, 'difficulty' => 8, 'teaching_rating' => 9];

        $this->postJson('/api/reviews', $payload)->assertUnauthorized();
        $this->mock(RabbitMQPublisher::class, function (MockInterface $mock) {
            $mock->shouldReceive('publish')->once()->with('ReviewSubmitted', \Mockery::type('array'))->andReturnTrue();
            $mock->shouldReceive('publish')->once()->with('DifficultyScoreUpdated', \Mockery::type('array'))->andReturnTrue();
        });

        $this->withToken($this->jwt('student-1'))->postJson('/api/reviews', $payload)
            ->assertCreated()->assertJsonPath('student_id', 'student-1');
        $this->assertDatabaseHas('course_reviews', ['course_id' => $course->id, 'student_id' => 'student-1']);
    }

    public function test_student_can_only_change_own_review(): void
    {
        $review = CourseReview::create([
            'course_id' => $this->course()->id, 'student_id' => 'owner',
            'difficulty' => 8, 'teaching_rating' => 9,
        ]);

        $this->withToken($this->jwt('other'))->putJson("/api/reviews/{$review->id}", ['difficulty' => 5])->assertForbidden();
        $this->withToken($this->jwt('owner'))->putJson("/api/reviews/{$review->id}", ['difficulty' => 5])
            ->assertOk()->assertJsonPath('difficulty', 5);
        $this->withToken($this->jwt('other'))->deleteJson("/api/reviews/{$review->id}")->assertForbidden();
        $this->withToken($this->jwt('owner'))->deleteJson("/api/reviews/{$review->id}")->assertNoContent();
    }

    public function test_internal_endpoint_is_not_api_prefixed(): void
    {
        $this->course();
        $this->getJson('/internal/courses/difficulty?major=Informatika')->assertOk()->assertJsonCount(1);
    }

    private function course(): Course
    {
        return Course::create([
            'code' => 'IF301', 'name' => 'Integrasi', 'credits' => 3,
            'major' => 'Informatika', 'semester' => 4,
        ]);
    }

    private function jwt(string $studentId): string
    {
        $encode = fn (array $value) => rtrim(strtr(base64_encode(json_encode($value)), '+/', '-_'), '=');
        $header = $encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = $encode(['sub' => $studentId, 'exp' => time() + 3600]);
        $signature = rtrim(strtr(base64_encode(hash_hmac('sha256', "$header.$payload", 'test-secret', true)), '+/', '-_'), '=');

        return "$header.$payload.$signature";
    }
}
