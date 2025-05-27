<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class StudentProfilePicture extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string'; // UUID stored as string

    protected $fillable = [
        'id',
        'student_id',
        'image_path',
        'identifier',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->identifier)) {
                $model->identifier = (string) Str::uuid();
            }
        });
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}
