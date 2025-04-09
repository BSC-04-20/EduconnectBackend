<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Marking extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    protected $fillable = ['id', 'submission_id', 'marks', 'feedback'];

    // Relationship with Submission
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
