<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassStudents extends Model
{
    //
    protected $table = "classstudents";
    public $timestamps = true;
    protected $fillable = ["classe_student", "student_id"];
    protected $hidden = ["updated_at"];
}
