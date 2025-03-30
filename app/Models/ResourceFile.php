<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceFile extends Model
{
    use HasFactory;

    protected $keyType = 'string'; // UUID is a string type
    public $incrementing = false; // Disable auto-incrementing
    
    protected $fillable = [
        'resource_id', 'file_path',
    ];

    // Relationship to the Resource model
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}
