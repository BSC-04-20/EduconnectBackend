<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    //
    use HasUuids, HasFactory, HasApiTokens;

    protected $table = "students";
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ["fullname", "email", "phonenumber", "password"];
    protected $hidden = ["password", "created_at", "updated_at", "pivot"];

    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'classstudents', 'student_id', 'classe_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'student_id');
    }

    public function lecturers()
    {
        return $this->classes()->with('lecturer')->get()->pluck('lecturer')->unique('id')->values();
    }

    // Relationship with Submissions
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
    
    // Relationship with Markings through Submissions
    public function markings(): HasManyThrough
    {
        return $this->hasManyThrough(Marking::class, Submission::class);
    }
}
