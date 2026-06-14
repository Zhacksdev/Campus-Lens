<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Certification;

class CertificationSeeder extends Seeder
{
    public function run(): void
    {
        Certification::insert([
            [
                'career_path_id' => 1,
                'name' => 'Laravel Certification',
                'provider' => 'Laravel',
                'recommended_semester' => 5,
                'url' => 'https://laravel.com'
            ],
            [
                'career_path_id' => 1,
                'name' => 'AWS Cloud Practitioner',
                'provider' => 'AWS',
                'recommended_semester' => 6,
                'url' => 'https://aws.amazon.com'
            ]
        ]);
    }
}
