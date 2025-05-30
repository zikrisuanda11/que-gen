<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $guarded = ["id"];
    
    /**
     * Get the questions for the subject
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
