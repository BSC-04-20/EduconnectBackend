<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Lecture extends Authenticatable
{
    //
    use HasUuids, HasFactory, HasApiTokens;

    protected $table = "lectures";
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ["fullname", "email", "phonenumber", "password"];
    protected $hidden = ["password"];

    public function classes()
    {
        return $this->hasMany(ClassModel::class, 'lecture_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'lecture_id');
    }
}
