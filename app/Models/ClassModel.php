<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ClassModel extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'classes'; // Explicitly define the table name
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['name', 'class_code', "lecture_id", "number_of_students"];
    protected $hidden = ['created_at', "updated_at", 'lecture_id'];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'classstudents', 'classe_id', 'student_id');
    }

    public function lecture()
    {
        return $this->belongsTo(Lecture::class, 'lecture_id');
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'class_id');
    }
}
