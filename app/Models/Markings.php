<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Marking extends Model
{
    protected $fillable = ['submission_id', 'marks', 'feedback'];

    // Relationship with Submission
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
