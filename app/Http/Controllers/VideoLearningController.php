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
        }]);

        $classesQuery = LiveClass::with(['course', 'batch', 'faculty', 'recordings'])
            ->orderBy('scheduled_start_at');

        if ($isStudent) {
            $courseIds = $this->studentCourseIds();
            $batchIds = $user->student?->batches()->wherePivot('status', 'active')->pluck('batches.id')->all() ?? [];
            $coursesQuery->where('is_published', true)->whereIn('course_id', $courseIds);
            $classesQuery->whereIn('course_id', $courseIds)->where(function ($query) use ($batchIds) {
                $query->whereNull('batch_id')->orWhereIn('batch_id', $batchIds);
            });
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
            $studentBatchIds = $user->student?->batches()->wherePivot('status', 'active')->pluck('batches.id')->all() ?? [];
            $recordings = $recordings->filter(function ($recording) use ($studentBatchIds) {
                return in_array($recording->liveClass?->course_id, $this->studentCourseIds())
                    && (!$recording->liveClass?->batch_id || in_array($recording->liveClass->batch_id, $studentBatchIds));
            });
        } elseif ($user->isFaculty()) {
            $recordings = $recordings->filter(fn ($recording) => $recording->liveClass?->faculty_id === $user->id);
        } elseif (!$user->isSuperAdmin()) {
            $recordings = $recordings->filter(fn ($recording) => $recording->liveClass?->branch_id === $user->branch_id);
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

        if (!auth()->user()->isSuperAdmin()) {
            $data['branch_id'] = auth()->user()->branch_id;
        }
        abort_unless($data['branch_id'], 422, 'A branch must be assigned before creating a video course.');

        $data['is_published'] = $request->boolean('is_published');
        VideoCourse::create($data);

        return back()->with('success', 'Video course created successfully.');
    }

    public function storeLesson(Request $request, VideoCourse $videoCourse)
    {
        abort_unless($this->canManage(), 403);

        if (!auth()->user()->isSuperAdmin() && $videoCourse->branch_id !== auth()->user()->branch_id) {
            abort(403);
        }

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
        if (!auth()->user()->isSuperAdmin()) {
            abort_unless($branchId === auth()->user()->branch_id, 403);
        }
        $facultyId = auth()->user()->isFaculty() ? auth()->id() : ($data['faculty_id'] ?: auth()->id());
        if ($batch && $facultyId !== auth()->id() && !$batch->faculty()->where('users.id', $facultyId)->exists() && $batch->primary_mentor_id !== $facultyId) {
            return back()->withErrors(['faculty_id' => 'Selected faculty is not assigned to this batch.'])->withInput();
        }
        if (!$branchId) {
            return back()->withErrors(['branch_id' => 'Create a branch before scheduling live classes.'])->withInput();
        }

        $roomName = 'class-' . Str::lower(Str::random(10)) . '-' . now()->format('Ymd');
        LiveClass::create([
            'branch_id' => $branchId,
            'course_id' => $data['course_id'],
            'batch_id' => $data['batch_id'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'faculty_id' => $facultyId,
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
                    'storage_disk' => 's3',
                    'storage_key' => $egress['recording_path'] ?? null,
                    'started_at' => now(),
                ]);
            });
        } catch (Throwable $e) {
            report($e);
            return back()->withErrors(['livekit' => 'Could not start room and recording. Check LiveKit and storage configuration, then retry.']);
        }

        return $this->join($liveClass, $liveKit);
    }

    public function join(LiveClass $liveClass, LiveKitService $liveKit)
    {
        $user = auth()->user();
        abort_unless(in_array($liveClass->status, ['live'], true), 409, 'This class is not live yet.');

        if ($user->isStudent()) {
            abort_unless(in_array($liveClass->course_id, $this->studentCourseIds()), 403);
            if ($liveClass->batch_id) {
                $batchIds = $user->student?->batches()->wherePivot('status', 'active')->pluck('batches.id')->all() ?? [];
                abort_unless(in_array($liveClass->batch_id, $batchIds), 403);
            }
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
            'canPublish' => !$user->isStudent(),
        ]);
    }


    public function showLesson(VideoLesson $lesson)
    {
        abort_unless($lesson->is_published || $this->canManage(), 404);
        $lesson->load('videoCourse.course');

        if (auth()->user()->isStudent()) {
            abort_unless(in_array($lesson->videoCourse->course_id, $this->studentCourseIds()), 403);
        } elseif (!$this->canManage() && $lesson->videoCourse->branch_id !== auth()->user()->branch_id) {
            abort(403);
        }

        $url = $lesson->source_url;
        $embedUrl = null;
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?: '');
        if (str_contains($host, 'youtu.be')) {
            $embedUrl = 'https://www.youtube-nocookie.com/embed/' . trim(parse_url($url, PHP_URL_PATH) ?: '', '/');
        } elseif (str_contains($host, 'youtube.com')) {
            parse_str(parse_url($url, PHP_URL_QUERY) ?: '', $query);
            if (!empty($query['v'])) {
                $embedUrl = 'https://www.youtube-nocookie.com/embed/' . $query['v'];
            }
        } elseif (str_contains($host, 'vimeo.com')) {
            $id = trim(parse_url($url, PHP_URL_PATH) ?: '', '/');
            if ($id !== '') {
                $embedUrl = 'https://player.vimeo.com/video/' . $id;
            }
        }

        $progress = VideoLearningProgress::firstOrNew([
            'video_lesson_id' => $lesson->id,
            'user_id' => auth()->id(),
        ]);

        return view('video-learning.lesson', compact('lesson', 'embedUrl', 'progress'));
    }

    public function saveProgress(Request $request, VideoLesson $lesson)
    {
        abort_unless($lesson->is_published || $this->canManage(), 404);
        if (auth()->user()->isStudent()) {
            abort_unless(in_array($lesson->videoCourse->course_id, $this->studentCourseIds()), 403);
        }

        $data = $request->validate([
            'watched_seconds' => 'required|integer|min:0|max:86400',
            'completed' => 'sometimes|boolean',
        ]);

        VideoLearningProgress::updateOrCreate(
            ['video_lesson_id' => $lesson->id, 'user_id' => auth()->id()],
            [
                'watched_seconds' => $data['watched_seconds'],
                'completed' => (bool) ($data['completed'] ?? false),
                'last_watched_at' => now(),
            ]
        );

        return response()->json(['saved' => true]);
    }

    public function playRecording(LiveRecording $recording)
    {
        $recording->load('liveClass');
        $user = auth()->user();

        if ($user->isStudent()) {
            abort_unless(in_array($recording->liveClass->course_id, $this->studentCourseIds()), 403);
            if ($recording->liveClass->batch_id) {
                $batchIds = $user->student?->batches()->wherePivot('status', 'active')->pluck('batches.id')->all() ?? [];
                abort_unless(in_array($recording->liveClass->batch_id, $batchIds), 403);
            }
        } elseif ($user->isFaculty()) {
            abort_unless($recording->liveClass->faculty_id === $user->id, 403);
        } elseif (!$user->isSuperAdmin() && !$user->isBranchManager()) {
            abort(403);
        }

        abort_unless($recording->status === 'ready' && $recording->storage_key, 404);
        $url = \Illuminate\Support\Facades\Storage::disk($recording->storage_disk ?: 's3')
            ->temporaryUrl($recording->storage_key, now()->addMinutes(15));

        return redirect()->away($url);
    }

    public function webhook(Request $request, LiveKitService $liveKit)
    {
        $rawBody = $request->getContent();
        abort_unless($liveKit->verifyWebhook($rawBody, $request->header('Authorization')), 401, 'Invalid LiveKit webhook signature.');

        $event = json_decode($rawBody, true) ?: [];
        $eventName = $event['event'] ?? '';
        $roomName = data_get($event, 'room.name');

        if ($eventName === 'egress_ended') {
            $egress = $event['egressInfo'] ?? $event['egress_info'] ?? [];
            $egressId = $egress['egressId'] ?? $egress['egress_id'] ?? null;
            $status = $egress['status'] ?? null;
            $recording = $egressId
                ? LiveRecording::where('provider_egress_id', $egressId)->first()
                : null;

            if ($recording) {
                $completed = in_array($status, [3, '3', 'EGRESS_COMPLETE'], true);
                $recording->update([
                    'status' => $completed ? 'ready' : 'failed',
                    'completed_at' => now(),
                    'duration_seconds' => (int) round((data_get($egress, 'fileResults.0.duration') ?? data_get($egress, 'file_results.0.duration') ?? 0) / 1000000000),
                    'failure_reason' => $completed ? null : ($egress['error'] ?? 'LiveKit recording did not complete successfully.'),
                ]);
            }
        } elseif ($eventName === 'room_finished' && $roomName) {
            LiveClass::where('provider_room_name', $roomName)->update(['status' => 'completed']);
        } elseif (in_array($eventName, ['participant_joined', 'participant_left'], true)) {
            $participant = $event['participant'] ?? [];
            $identity = $participant['identity'] ?? null;
            if ($roomName && $identity && ctype_digit((string) $identity)) {
                $class = LiveClass::where('provider_room_name', $roomName)->first();
                if ($class) {
                    $attendance = LiveClassAttendance::firstOrNew([
                        'live_class_id' => $class->id,
                        'participant_identity' => (string) $identity,
                    ]);
                    $attendance->user_id = (int) $identity;
                    if ($eventName === 'participant_joined') {
                        $attendance->joined_at = now();
                        $attendance->left_at = null;
                    } else {
                        $attendance->left_at = now();
                        if ($attendance->joined_at) {
                            $attendance->duration_seconds = max(0, $attendance->joined_at->diffInSeconds($attendance->left_at));
                        }
                    }
                    $attendance->save();
                }
            }
        }

        return response()->json(['received' => true]);
    }

}
