<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhaseActivity extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['phase_id', 'type', 'title', 'description', 'priority'];

    public function phase(): BelongsTo
    {
        return $this->belongsTo(RoadmapPhase::class, 'phase_id');
    }
}
