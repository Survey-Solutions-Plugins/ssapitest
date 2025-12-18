<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Questionnaire extends Model
{
    protected $fillable = [
        'questionnaire_id',
        'version',
        'title',
        'description',
        'document',
        'audio_recording_enabled',
        'criticality_level',
        'workspace_id'
    ];

    protected $casts = [
        'document' => 'array',
        'audio_recording_enabled' => 'boolean'
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }
}
