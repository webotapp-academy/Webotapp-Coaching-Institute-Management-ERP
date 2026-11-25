<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->index(['course_id', 'is_published']);
        });

        Schema::create('video_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_course_id')->constrained('video_courses')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('provider')->default('external');
            $table->string('provider_asset_id')->nullable();
            $table->text('source_url')->nullable();
            $table->string('storage_key')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_preview')->default(false);
            $table->boolean('is_published')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['video_course_id', 'sort_order']);
        });

        Schema::create('live_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('faculty_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('scheduled_start_at');
            $table->dateTime('scheduled_end_at');
            $table->string('provider')->default('livekit');
            $table->string('provider_room_name')->unique();
            $table->string('provider_room_sid')->nullable();
            $table->string('status')->default('scheduled');
            $table->boolean('recording_enabled')->default(true);
            $table->timestamps();
            $table->index(['status', 'scheduled_start_at']);
        });

        Schema::create('live_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_class_id')->constrained()->cascadeOnDelete();
            $table->string('provider_egress_id')->nullable()->unique();
            $table->string('status')->default('pending');
            $table->string('storage_disk')->nullable();
            $table->string('storage_key')->nullable();
            $table->text('playback_url')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('mime_type')->nullable();
            $table->text('failure_reason')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            $table->index(['live_class_id', 'status']);
        });

        Schema::create('live_class_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('joined_at')->nullable();
            $table->dateTime('left_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->string('participant_identity');
            $table->timestamps();
            $table->unique(['live_class_id', 'participant_identity']);
            $table->index(['user_id', 'live_class_id']);
        });

        Schema::create('video_learning_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_lesson_id')->constrained('video_lessons')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('watched_seconds')->default(0);
            $table->boolean('completed')->default(false);
            $table->dateTime('last_watched_at')->nullable();
            $table->timestamps();
            $table->unique(['video_lesson_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_learning_progress');
        Schema::dropIfExists('live_class_attendance');
        Schema::dropIfExists('live_recordings');
        Schema::dropIfExists('live_classes');
        Schema::dropIfExists('video_lessons');
        Schema::dropIfExists('video_courses');
    }
};
