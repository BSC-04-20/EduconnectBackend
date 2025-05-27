<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class LectureProfilePicture extends Model
{
    use HasFactory;

    protected $table = 'lecture_profile_pictures';

    protected $fillable = [
        'id',
        'lecture_id',
        'image_path',
        'identifier'
    ];

    public function lecture()
    {
        return $this->belongsTo(Lecture::class);
    }
}
