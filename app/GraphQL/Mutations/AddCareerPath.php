<?php

namespace App\GraphQL\Mutations;

use App\Models\CareerPath;

class AddCareerPath
{
    public function __invoke($_, array $args)
    {
        return CareerPath::create([
            'name' => $args['name'],
            'major' => $args['major'],
            'description' => $args['description'] ?? null,
        ]);
    }
}
