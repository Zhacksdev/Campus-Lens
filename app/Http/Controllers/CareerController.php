<?php

namespace App\Http\Controllers;

use App\Models\CareerPath;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    public function index()
    {
        return CareerPath::all();
    }

    public function show(CareerPath $careerPath)
    {
        return $careerPath;
    }

    public function store(Request $request)
    {
        return CareerPath::create($request->all());
    }
}
