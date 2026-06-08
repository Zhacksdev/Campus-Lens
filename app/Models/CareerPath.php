<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerPath extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'major', 'description'];

    public function phases(): HasMany
    {
        return $this->hasMany(RoadmapPhase::class)->orderBy('semester');
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class);
    }
}
