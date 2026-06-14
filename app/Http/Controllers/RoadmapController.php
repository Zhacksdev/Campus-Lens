<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CareerPath;

class RoadmapController extends Controller
{
    public function roadmap(Request $request)
    {
        $major = $request->major;
        $semester = $request->semester;

        $careers = CareerPath::where('major', $major)->get();

        return response()->json([
            'major' => $major,
            'semester' => $semester,
            'careers' => $careers
        ]);
    }

    public function certifications(Request $request)
    {
        return response()->json([
            'message' => 'Certifications endpoint'
        ]);
    }

    public function storePhase(Request $request, $careerPath)
    {
        return response()->json([
            'message' => 'Phase added',
            'career_path_id' => $careerPath
        ]);
    }

    public function internalPhases(Request $request)
    {
        return response()->json([
            'message' => 'Internal roadmap phases'
        ]);
    }
}
