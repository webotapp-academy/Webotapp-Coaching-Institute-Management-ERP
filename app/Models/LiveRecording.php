<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveRecording extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function liveClass()
    {
        return $this->belongsTo(LiveClass::class);
    }
}
