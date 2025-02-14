<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lecture extends Model
{
    //
    use HasUuids, HasFactory;

    protected $table = "lectures";
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ["fullname", "email", "phonenumber", "password"];
    protected $hidden = ["password"];
}
