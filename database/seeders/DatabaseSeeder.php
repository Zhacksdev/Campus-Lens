<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Lecturer;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $lecturer = Lecturer::updateOrCreate(
            ['email' => 'budi@campuslens.test'],
            ['name' => 'Dr. Budi Santoso', 'teaching_style' => 'Practical']
        );
        Lecturer::updateOrCreate(
            ['email' => 'siti@campuslens.test'],
            ['name' => 'Siti Rahma, M.Kom.', 'teaching_style' => 'Interactive']
        );

        $course = Course::updateOrCreate(
            ['code' => 'IF301'],
            ['name' => 'Enterprise Application Integration', 'credits' => 3, 'major' => 'Informatics', 'semester' => 4]
        );
        Course::updateOrCreate(
            ['code' => 'IF302'],
            ['name' => 'Software Engineering', 'credits' => 3, 'major' => 'Informatics', 'semester' => 4]
        );
        Course::updateOrCreate(
            ['code' => 'SI201'],
            ['name' => 'Business Process Analysis', 'credits' => 3, 'major' => 'Information Systems', 'semester' => 3]
        );

        CourseReview::updateOrCreate(
            ['course_id' => $course->id, 'student_id' => 'b3b7cb5e-9d9e-4df9-8d2c-7f30c1a8d101'],
            [
                'lecturer_id' => $lecturer->id, 'difficulty' => 7, 'teaching_rating' => 8,
                'tips' => 'Review API contracts and practice Docker.',
                'uts_strategy' => 'Understand synchronous and asynchronous integration.',
                'uas_strategy' => 'Practice an end-to-end service implementation.',
                'semester_taken' => 4, 'academic_year' => '2025/2026',
            ]
        );
    }
}
