<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Course;
use App\Models\User;
use Tests\TestCase;

class VideoLearningModuleTest extends TestCase
{
    public function test_guest_is_redirected_from_video_learning(): void
    {
        $this->get('/video-learning')->assertRedirect('/login');
    }

    public function test_admin_can_open_video_learning_hub(): void
    {
        $admin = User::where('email', 'admin@coaching.com')->firstOrFail();

        $this->actingAs($admin)
            ->get('/video-learning')
            ->assertOk()
            ->assertSee('Video Learning Hub')
            ->assertSee('Create recorded video course')
            ->assertSee('Schedule a Zoom live session');
    }

    public function test_student_can_open_learning_hub_but_cannot_manage_courses(): void
    {
        $student = User::where('user_type', 'student')->firstOrFail();

        $this->actingAs($student)
            ->get('/video-learning')
            ->assertOk()
            ->assertSee('Recorded video courses')
            ->assertDontSee('Create recorded video course')
            ->assertDontSee('Schedule a Zoom live session');
    }

    public function test_student_cannot_create_a_video_course(): void
    {
        $student = User::where('user_type', 'student')->firstOrFail();

        $this->actingAs($student)
            ->post('/video-learning/courses', [])
            ->assertForbidden();
    }

    public function test_admin_can_create_a_video_course(): void
    {
        $admin = User::where('email', 'admin@coaching.com')->firstOrFail();
        $course = Course::where('status', 'active')->firstOrFail();
        $branch = Branch::where('status', 'active')->firstOrFail();

        $this->actingAs($admin)
            ->post('/video-learning/courses', [
                'course_id' => $course->id,
                'branch_id' => $branch->id,
                'title' => 'Video Learning Feature Test',
                'description' => 'Created by the feature test.',
                'price' => 499,
                'is_published' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('video_courses', [
            'course_id' => $course->id,
            'branch_id' => $branch->id,
            'title' => 'Video Learning Feature Test',
            'price' => 499,
            'is_published' => 1,
        ]);
    }


    public function test_admin_can_schedule_zoom_live_session_with_its_own_price(): void
    {
        $admin = User::where('email', 'admin@coaching.com')->firstOrFail();
        $course = Course::where('status', 'active')->firstOrFail();
        $faculty = User::where('user_type', 'faculty')
            ->where('status', 'active')
            ->where('branch_id', $admin->branch_id)
            ->firstOrFail();

        $start = now()->addDay();
        $end = $start->copy()->addHour();

        $this->actingAs($admin)
            ->post('/video-learning/live-classes', [
                'course_id' => $course->id,
                'faculty_id' => $faculty->id,
                'title' => 'Kafka Zoom Live Session Test',
                'zoom_meeting_url' => 'https://us02web.zoom.us/j/12345678901',
                'price' => 299,
                'scheduled_start_at' => $start->format('Y-m-d H:i:s'),
                'scheduled_end_at' => $end->format('Y-m-d H:i:s'),
                'description' => 'Test Zoom invitation and per-session price.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('live_classes', [
            'course_id' => $course->id,
            'faculty_id' => $faculty->id,
            'title' => 'Kafka Zoom Live Session Test',
            'zoom_meeting_url' => 'https://us02web.zoom.us/j/12345678901',
            'price' => '299.00',
            'provider' => 'zoom',
            'status' => 'scheduled',
        ]);
    }

    public function test_admin_can_add_a_google_drive_lesson_to_a_recorded_course(): void
    {
        $admin = User::where('email', 'admin@coaching.com')->firstOrFail();
        $course = Course::where('status', 'active')->firstOrFail();

        $this->actingAs($admin)
            ->post('/video-learning/courses', [
                'course_id' => $course->id,
                'branch_id' => $admin->branch_id,
                'title' => 'Drive Course Test',
                'description' => 'Recorded course stored in Google Drive.',
                'price' => 999,
                'is_published' => 1,
            ])
            ->assertRedirect();

        $videoCourse = \App\Models\VideoCourse::where('title', 'Drive Course Test')->firstOrFail();

        $this->actingAs($admin)
            ->post("/video-learning/courses/{$videoCourse->id}/lessons", [
                'title' => 'Lesson 1 - Kafka Basics',
                'source_url' => 'https://drive.google.com/file/d/1AbC_1234/view?usp=sharing',
                'duration_seconds' => 1800,
                'is_published' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('video_lessons', [
            'video_course_id' => $videoCourse->id,
            'title' => 'Lesson 1 - Kafka Basics',
            'provider' => 'google_drive',
            'source_url' => 'https://drive.google.com/file/d/1AbC_1234/view?usp=sharing',
            'is_published' => 1,
        ]);
    }

}
