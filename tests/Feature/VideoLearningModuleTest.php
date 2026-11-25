<?php

namespace Tests\\Feature;

use App\\Models\\Branch;
use App\\Models\\Course;
use App\\Models\\User;
use Tests\\TestCase;

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
            ->assertSee('Create video course')
            ->assertSee('Schedule a live class');
    }

    public function test_student_can_open_learning_hub_but_cannot_manage_courses(): void
    {
        $student = User::where('user_type', 'student')->firstOrFail();

        $this->actingAs($student)
            ->get('/video-learning')
            ->assertOk()
            ->assertSee('Recorded courses')
            ->assertDontSee('Create video course')
            ->assertDontSee('Schedule a live class');
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
                'is_published' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('video_courses', [
            'course_id' => $course->id,
            'branch_id' => $branch->id,
            'title' => 'Video Learning Feature Test',
            'is_published' => 1,
        ]);
    }
}
