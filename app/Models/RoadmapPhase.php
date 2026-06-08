<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoadmapPhase extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['career_path_id', 'semester', 'focus'];

    public function careerPath(): BelongsTo
    {
        return $this->belongsTo(CareerPath::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(PhaseActivity::class, 'phase_id')->orderBy('priority');
    }
}
