<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;

class LiveClass extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'scheduled_start_at' => 'datetime',
        'scheduled_end_at' => 'datetime',
        'recording_enabled' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function recordings()
    {
        return $this->hasMany(LiveRecording::class);
    }

    public function attendance()
    {
        return $this->hasMany(LiveClassAttendance::class);
    }
}
