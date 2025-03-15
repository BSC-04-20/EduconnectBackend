<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Announcement extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['title', 'description', 'class_id'];


    // Relationship with Class
    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    // Relationship with Announcement Files
    public function files()
    {
        return $this->hasMany(AnnouncementFile::class);
    }
}
