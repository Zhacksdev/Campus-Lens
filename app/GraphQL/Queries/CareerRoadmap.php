<?php

namespace App\GraphQL\Queries;

use App\Models\CareerPath;

class CareerRoadmap
{
    public function __invoke($_, array $args)
    {
        return CareerPath::with([
            'phases.activities',
            'certifications'
        ])
        ->where('major', $args['major'])
        ->where('name', $args['careerGoal'])
        ->first();
    }
}
