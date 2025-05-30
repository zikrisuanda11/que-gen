<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionAnswer extends Model
{
    protected $fillable = [
        'submission_id',
        'option_id',
    ];

    /**
     * Get the submission that owns the answer
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Get the option that was selected
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }
}