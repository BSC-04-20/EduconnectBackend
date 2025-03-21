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
}
