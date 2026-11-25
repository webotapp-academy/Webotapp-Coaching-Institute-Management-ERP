<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoLesson extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_preview' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function videoCourse()
    {
        return $this->belongsTo(VideoCourse::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function progress()
    {
        return $this->hasMany(VideoLearningProgress::class);
    }
}
