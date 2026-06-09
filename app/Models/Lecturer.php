<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lecturer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'teaching_style'];

    public function reviews(): HasMany
    {
        return $this->hasMany(CourseReview::class);
    }
}
