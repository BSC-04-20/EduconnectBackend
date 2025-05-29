<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Assignment extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['title', 'description', 'due_date', 'class_id'];

    // Relationship with Class
    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function files()
    {
        return $this->hasMany(AssignmentFile::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

}
