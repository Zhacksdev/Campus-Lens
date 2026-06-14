<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoadmapPhase;

class RoadmapSeeder extends Seeder
{
    public function run(): void
    {
        RoadmapPhase::insert([
            [
                'career_path_id' => 1,
                'semester' => 1,
                'focus' => 'Dasar Pemrograman dan Algoritma'
            ],
            [
                'career_path_id' => 1,
                'semester' => 2,
                'focus' => 'Object Oriented Programming'
            ],
            [
                'career_path_id' => 1,
                'semester' => 3,
                'focus' => 'Database dan SQL'
            ],
            [
                'career_path_id' => 1,
                'semester' => 4,
                'focus' => 'Laravel dan REST API'
            ]
        ]);
    }
}
