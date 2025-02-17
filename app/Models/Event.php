<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Event extends Model
{
    //
    use HasUuids;

    protected $table = "events";
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ["user_id", "name", "location", "time", "date", "number_of_attendees"];
    protected $hidden = ["created_at", "updated_at", "id", "user_id"];
}
