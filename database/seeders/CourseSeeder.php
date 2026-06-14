<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        Course::insert([
            [
                'code' => 'IF301',
                'name' => 'Basis Data',
                'credits' => 3,
                'major' => 'Informatika',
                'semester' => 3,
            ],
            [
                'code' => 'IF302',
                'name' => 'Jaringan Komputer',
                'credits' => 3,
                'major' => 'Informatika',
                'semester' => 4,
            ],
            [
                'code' => 'IF303',
                'name' => 'Sistem Operasi',
                'credits' => 3,
                'major' => 'Informatika',
                'semester' => 3,
            ],
            [
                'code' => 'IF304',
                'name' => 'Enterprise Application Integration',
                'credits' => 3,
                'major' => 'Informatika',
                'semester' => 4,
            ],
            [
                'code' => 'IF305',
                'name' => 'Machine Learning',
                'credits' => 3,
                'major' => 'Informatika',
                'semester' => 5,
            ],
        ]);
    }
}