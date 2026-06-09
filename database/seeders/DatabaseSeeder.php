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
        $lecturer = Lecturer::create([
            'name' => 'Dr. Budi Santoso', 'email' => 'budi@campus.ac.id', 'teaching_style' => 'Praktis',
        ]);
        Lecturer::create([
            'name' => 'Siti Rahma, M.Kom.', 'email' => 'siti@campus.ac.id', 'teaching_style' => 'Interaktif',
        ]);
        $course = Course::create([
            'code' => 'IF301', 'name' => 'Integrasi Aplikasi Sistem', 'credits' => 3,
            'major' => 'Informatika', 'semester' => 4,
        ]);
        Course::create([
            'code' => 'IF302', 'name' => 'Rekayasa Perangkat Lunak', 'credits' => 3,
            'major' => 'Informatika', 'semester' => 4,
        ]);
        Course::create([
            'code' => 'SI201', 'name' => 'Analisis Proses Bisnis', 'credits' => 3,
            'major' => 'Sistem Informasi', 'semester' => 3,
        ]);
        CourseReview::create([
            'course_id' => $course->id, 'lecturer_id' => $lecturer->id, 'student_id' => 'seed-student',
            'difficulty' => 7, 'teaching_rating' => 8, 'tips' => 'Pelajari kontrak API dan latihan Docker.',
            'uts_strategy' => 'Pahami integrasi sinkron dan asinkron.',
            'uas_strategy' => 'Latihan implementasi service end-to-end.',
            'semester_taken' => 4, 'academic_year' => '2025/2026',
        ]);
    }
}
