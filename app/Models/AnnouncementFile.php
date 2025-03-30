<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AnnouncementFile extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'announcement_files';

    protected $fillable = [
        'announcement_id',
        'file_path',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function announcement()
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }
}
