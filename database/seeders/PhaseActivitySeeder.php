<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PhaseActivity;

class PhaseActivitySeeder extends Seeder
{
    public function run(): void
    {
        PhaseActivity::insert([
            [
                'phase_id' => 1,
                'type' => 'course',
                'title' => 'Belajar Dasar C++',
                'description' => 'Memahami algoritma dan struktur data dasar',
                'priority' => 1
            ],
            [
                'phase_id' => 2,
                'type' => 'project',
                'title' => 'Membuat Aplikasi OOP',
                'description' => 'Implementasi konsep OOP',
                'priority' => 1
            ],
            [
                'phase_id' => 3,
                'type' => 'course',
                'title' => 'Belajar MySQL',
                'description' => 'Mempelajari query dan desain database',
                'priority' => 1
            ],
            [
                'phase_id' => 4,
                'type' => 'project',
                'title' => 'Membuat REST API Laravel',
                'description' => 'CRUD API menggunakan Laravel',
                'priority' => 1
            ]
        ]);
    }
}
