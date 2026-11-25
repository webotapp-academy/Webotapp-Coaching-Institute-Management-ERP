<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;

class LiveClassAttendance extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function liveClass()
    {
        return $this->belongsTo(LiveClass::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
