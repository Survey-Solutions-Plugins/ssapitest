<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewAnswer extends Model
{
    protected $fillable = [
        'interview_id',
        'question_id',
        'answer',
        'variable_name',
        'question_type'
    ];

    protected $casts = [
        'answer' => 'array'
    ];

    public function interview(): BelongsTo
    {
        return $this->belongsTo(Interview::class);
    }
}
