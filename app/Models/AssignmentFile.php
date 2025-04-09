<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class AssignmentFile extends Model
{
    use HasFactory;

    public $incrementing = false; // Since we're using UUIDs
    protected $keyType = 'string';

    protected $fillable = [
        'assignment_id',
        'file_name',
        'file_path',
    ];

    // Automatically generate UUID when creating a new model instance
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // Relationship to Assignment
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }
}
