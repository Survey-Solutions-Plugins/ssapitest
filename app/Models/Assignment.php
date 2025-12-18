<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = [
        'questionnaire_id',
        'workspace_id',
        'responsible_id',
        'identifying_data',
        'quantity',
        'interviews_count',
        'archived',
        'audio_recording',
        'status'
    ];

    protected $casts = [
        'identifying_data' => 'array',
        'archived' => 'boolean',
        'audio_recording' => 'boolean'
    ];

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }
}
