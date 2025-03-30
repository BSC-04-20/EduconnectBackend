<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Resource extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title', 'description', 'class_id', 'files'
    ];

    // Define the relationship with the Class model
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }
}
