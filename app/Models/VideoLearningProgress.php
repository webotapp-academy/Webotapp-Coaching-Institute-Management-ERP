<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;

class VideoLearningProgress extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'completed' => 'boolean',
        'last_watched_at' => 'datetime',
    ];

    public function lesson()
    {
        return $this->belongsTo(VideoLesson::class, 'video_lesson_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
