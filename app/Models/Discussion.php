<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Discussion extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id',
        'class_id',
        'meeting_name',
        'meeting_identifier',
        'start_time',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate UUID and meeting identifier
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
            $model->meeting_identifier = $model->meeting_identifier ?? Str::random(10);
        });
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }
}
