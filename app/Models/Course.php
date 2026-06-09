<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'credits', 'major', 'semester'];

    public function reviews(): HasMany
    {
        return $this->hasMany(CourseReview::class);
    }
}
