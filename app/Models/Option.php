<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Option extends Model
{
    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct',
    ];

    /**
     * Get the question that owns the option
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the submission answers that chose this option
     */
    public function submissionAnswers(): HasMany
    {
        return $this->hasMany(SubmissionAnswer::class);
    }
}
