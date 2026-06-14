<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CareerPath;

class CareerSeeder extends Seeder
{
    public function run(): void
    {
        CareerPath::create([
            'name' => 'Backend Engineer',
            'major' => 'Informatika',
            'description' => 'Mengembangkan API dan sistem backend.'
        ]);

        CareerPath::create([
            'name' => 'Frontend Developer',
            'major' => 'Informatika',
            'description' => 'Mengembangkan antarmuka pengguna aplikasi.'
        ]);

        CareerPath::create([
            'name' => 'Data Scientist',
            'major' => 'Informatika',
            'description' => 'Mengolah dan menganalisis data.'
        ]);

        CareerPath::create([
            'name' => 'UI/UX Designer',
            'major' => 'Sistem Informasi',
            'description' => 'Merancang pengalaman dan tampilan pengguna.'
        ]);

        CareerPath::create([
            'name' => 'Cyber Security Analyst',
            'major' => 'Informatika',
            'description' => 'Menjaga keamanan sistem dan jaringan.'
        ]);
    }
}
