<?php

namespace App\\Http\\Controllers;

use App\\Models\\{Batch, Branch, Course, LiveClass, LiveClassAttendance, LiveRecording, VideoCourse, VideoLearningProgress, VideoLesson};
use App\\Services\\LiveKitService;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Str;
use RuntimeException;
use Throwable;

class VideoLearningController extends Controller
{
    private function canManage(): bool
    {
        return auth()->user()->isSuperAdmin()
            || auth()->user()->isBranchManager()
            || auth()->user()->isFaculty();
    }

    private function studentCourseIds(): array
    {
        $student = auth()->user()->student;

        return $student ? $student->batches()->wherePivot('status', 'active')->pluck('course_id')->unique()->all() : [];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isStudent = $user->isStudent();
        $coursesQuery = VideoCourse::with(['course', 'lessons' => function ($query) {
            $query->where('is_published', true)->orderBy('sort_order');
        }])->where('is_published', true);

        $classesQuery = LiveClass::with(['course', 'batch', 'faculty', 'recordings'])
            ->orderBy('scheduled_start_at');

        if ($isStudent) {
            $courseIds = $this->studentCourseIds();
            $coursesQuery->whereIn('course_id', $courseIds);
            $classesQuery->whereIn('course_id', $courseIds);
        } elseif ($user->isFaculty()) {
            $coursesQuery->where('branch_id', $user->branch_id);
            $classesQuery->where('faculty_id', $user->id);
        } elseif (!$user->isSuperAdmin()) {
            $coursesQuery->where('branch_id', $user->branch_id);
            $classesQuery->where('branch_id', $user->branch_id);
        }

        $videoCourses = $coursesQuery->latest()->get();
        $liveClasses = $classesQuery->get();
        $recordings = LiveRecording::with('liveClass.course')
            ->where('status', 'ready')
            ->latest('completed_at')
            ->limit(30)->get();

        if ($isStudent) {
            $recordings = $recordings->filter(fn ($recording) => in_array($recording->liveClass?->course_id, $this->studentCourseIds()));
        }

        $courses = Course::where('status', 'active')->orderBy('name')->get();
        $batches = Batch::where('status', 'active')->with('course')->orderBy('name')->get();
        $branches = Branch::where('status', 'active')->orderBy('name')->get();
        $faculty = \\App\\Models\\User::where('user_type', 'faculty')->where('status', 'active')->orderBy('name')->get();

        return view('video-learning.index', compact(
            'videoCourses', 'liveClasses', 'recordings', 'courses', 'batches', 'branches', 'faculty', 'isStudent'
        ));
    }

    public function storeCourse(Request $request)
    {
        abort_unless($this->canManage(), 403);

        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'branch_id' => 'required|exists:branches,id',
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:5000',
        ]);

        $data['is_published'] = $request->boolean('is_published');
        VideoCourse::create($data);

        return back()->with('success', 'Video course created successfully.');
    }

    public function storeLesson(Request $request, VideoCourse $videoCourse)
    {
        abort_unless($this->canManage(), 403);

        $data = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:5000',
            'source_url' => 'required|url|max:2000',
            'duration_seconds' => 'nullable|integer|min:1|max:86400',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $data['provider'] = 'external';
        $data['created_by'] = auth()->id();
        $data['is_published'] = $request->boolean('is_published');
        $videoCourse->lessons()->create($data);

        return back()->with('success', 'Recorded lesson added to the course.');
    }

    public function storeLiveClass(Request $request)
    {
        abort_unless($this->canManage(), 403);

        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'batch_id' => 'nullable|exists:batches,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'faculty_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:3000',
            'scheduled_start_at' => 'required|date|after:now',
            'scheduled_end_at' => 'required|date|after:scheduled_start_at',
        ]);

        $batch = !empty($data['batch_id']) ? Batch::findOrFail($data['batch_id']) : null;
        if ($batch && (int) $batch->course_id !== (int) $data['course_id']) {
            return back()->withErrors(['batch_id' => 'Selected batch does not belong to the selected course.'])->withInput();
        }

        $branchId = $batch?->branch_id ?: auth()->user()->branch_id ?: Branch::query()->value('id');
        if (!$branchId) {
            return back()->withErrors(['branch_id' => 'Create a branch before scheduling live classes.'])->withInput();
        }

        $roomName = 'class-' . Str::lower(Str::random(10)) . '-' . now()->format('Ymd');
        LiveClass::create([
            'branch_id' => $branchId,
            'course_id' => $data['course_id'],
            'batch_id' => $data['batch_id'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'faculty_id' => auth()->user()->isFaculty() ? auth()->id() : ($data['faculty_id'] ?: auth()->id()),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'scheduled_start_at' => $data['scheduled_start_at'],
            'scheduled_end_at' => $data['scheduled_end_at'],
            'provider_room_name' => $roomName,
            'recording_enabled' => true,
            'status' => 'scheduled',
        ]);

        return back()->with('success', 'Live class scheduled. Automatic recording is enabled.');
    }

    public function start(LiveClass $liveClass, LiveKitService $liveKit)
    {
        abort_unless($this->canManage(), 403);
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->isBranchManager() || $liveClass->faculty_id === auth()->id(), 403);
        abort_if($liveClass->status === 'live', 409, 'This class is already live.');

        if (!$liveKit->isConfigured()) {
            return back()->withErrors(['livekit' => 'Configure LIVEKIT_URL, LIVEKIT_API_KEY and LIVEKIT_API_SECRET before starting a live room.']);
        }

        try {
            $liveKit->createRoom($liveClass->provider_room_name);
            $egress = $liveKit->startRecording($liveClass->provider_room_name);

            DB::transaction(function () use ($liveClass, $egress) {
                $liveClass->update(['status' => 'live']);
                $liveClass->recordings()->create([
                    'provider_egress_id' => $egress['egress_id'] ?? null,
                    'status' => 'recording',
                    'started_at' => now(),
                ]);
            });
        } catch (Throwable $e) {
            report($e);
            return back()->withErrors(['livekit' => 'Could not start room and recording. Check LiveKit and storage configuration, then retry.']);
        }

        return redirect()->route('video-learning.classroom', $liveClass)->with('success', 'Live room and automatic recording started.');
    }

    public function join(LiveClass $liveClass, LiveKitService $liveKit)
    {
        $user = auth()->user();
        abort_unless(in_array($liveClass->status, ['live'], true), 409, 'This class is not live yet.');

        if ($user->isStudent()) {
            abort_unless(in_array($liveClass->course_id, $this->studentCourseIds()), 403);
        } elseif ($user->isFaculty()) {
            abort_unless($liveClass->faculty_id === $user->id, 403);
        } elseif (!$user->isSuperAdmin() && !$user->isBranchManager()) {
            abort(403);
        }

        if (!$liveKit->isConfigured()) {
            return back()->withErrors(['livekit' => 'Live classroom is not configured yet.']);
        }

        $identity = (string) $user->id;
        LiveClassAttendance::updateOrCreate(
            ['live_class_id' => $liveClass->id, 'participant_identity' => $identity],
            ['user_id' => $user->id, 'joined_at' => now(), 'left_at' => null]
        );

        $token = $liveKit->createJoinToken(
            $liveClass->provider_room_name,
            $identity,
            $user->name,
            !$user->isStudent()
        );

        return view('video-learning.classroom', [
            'liveClass' => $liveClass->load(['course', 'faculty']),
            'livekitUrl' => $liveKit->serverUrl(),
            'token' => $token,
        ]);
    }
}
