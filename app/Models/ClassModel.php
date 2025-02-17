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
    protected $hidden = ['created_at', "updated_at"];

}
