<?php

namespace Database\Seeders;

use App\Models\CareerPath;
use App\Models\Certification;
use App\Models\PhaseActivity;
use App\Models\RoadmapPhase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'career-demo@campuslens.test'],
            ['name' => 'Career Demo User', 'password' => Hash::make('password123')]
        );

        $backend = CareerPath::updateOrCreate(
            ['name' => 'Backend Engineer', 'major' => 'Informatics'],
            ['description' => 'Build APIs, data-backed systems, and cloud services.']
        );
        CareerPath::updateOrCreate(
            ['name' => 'Frontend Developer', 'major' => 'Informatics'],
            ['description' => 'Build accessible and responsive user interfaces.']
        );
        CareerPath::updateOrCreate(
            ['name' => 'Data Analyst', 'major' => 'Information Systems'],
            ['description' => 'Analyze business, product, and operational data.']
        );

        $phases = [
            ['semester' => 1, 'focus' => 'Programming fundamentals and algorithms'],
            ['semester' => 2, 'focus' => 'Object-oriented programming and Git'],
            ['semester' => 3, 'focus' => 'Database design, SQL, and API fundamentals'],
            ['semester' => 4, 'focus' => 'Laravel, testing, and deployment'],
        ];

        foreach ($phases as $phaseData) {
            $phase = RoadmapPhase::updateOrCreate(
                ['career_path_id' => $backend->id, 'semester' => $phaseData['semester']],
                ['focus' => $phaseData['focus']]
            );

            PhaseActivity::updateOrCreate(
                ['phase_id' => $phase->id, 'title' => "Semester {$phaseData['semester']} portfolio project"],
                [
                    'type' => 'project',
                    'description' => "Build one project focused on {$phaseData['focus']}.",
                    'priority' => 1,
                ]
            );
        }

        Certification::updateOrCreate(
            ['career_path_id' => $backend->id, 'name' => 'AWS Cloud Practitioner'],
            ['provider' => 'AWS', 'recommended_semester' => 6, 'url' => 'https://aws.amazon.com/certification/']
        );
        Certification::updateOrCreate(
            ['career_path_id' => $backend->id, 'name' => 'Laravel Certification'],
            ['provider' => 'Laravel', 'recommended_semester' => 5, 'url' => 'https://laravel.com']
        );
    }
}
