<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'class_id', 'files'
    ];

    // Define the relationship with the Class model
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }
}
