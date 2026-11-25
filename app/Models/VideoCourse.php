<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoCourse extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['price' => 'decimal:2'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function lessons()
    {
        return $this->hasMany(VideoLesson::class)->orderBy('sort_order');
    }
}
