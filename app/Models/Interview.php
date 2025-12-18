<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Interview extends Model
{
    protected $fillable = [
        'interview_id',
        'assignment_id',
        'questionnaire_id',
        'workspace_id',
        'interviewer_id',
        'supervisor_id',
        'status',
        'identifying_data',
        'answers',
        'has_errors',
        'errors_count'
    ];

    protected $casts = [
        'identifying_data' => 'array',
        'answers' => 'array',
        'has_errors' => 'boolean'
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function interviewAnswers(): HasMany
    {
        return $this->hasMany(InterviewAnswer::class);
    }
}
