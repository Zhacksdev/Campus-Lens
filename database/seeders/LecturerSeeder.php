<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lecturer;

class LecturerSeeder extends Seeder
{
    public function run(): void
    {
        Lecturer::insert([
            [
                'name' => 'Dr. Budi Santoso',
                'email' => 'budi@campuslens.ac.id',
                'teaching_style' => 'Interactive'
            ],
            [
                'name' => 'Dr. Siti Rahma',
                'email' => 'siti@campuslens.ac.id',
                'teaching_style' => 'Project Based'
            ],
            [
                'name' => 'Dr. Ahmad Fauzi',
                'email' => 'ahmad@campuslens.ac.id',
                'teaching_style' => 'Discussion'
            ]
        ]);
    }
}