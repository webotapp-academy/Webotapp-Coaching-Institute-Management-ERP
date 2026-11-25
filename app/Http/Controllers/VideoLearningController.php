<?php

namespace App\Http\Controllers;

use App\Models\{Batch, Branch, Course, LiveClass, LiveRecording, VideoCourse, VideoLearningProgress, VideoLesson};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        return $student
            ? $student->batches()->wherePivot('status', 'active')->pluck('course_id')->unique()->all()
            : [];
    }

    private function isGoogleDriveUrl(string $url): bool
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?: '');

        return $host === 'drive.google.com' || $host === 'docs.google.com';
    }

    private function googleDriveFileId(string $url): ?string
    {
        if (preg_match('~/file/d/([a-zA-Z0-9_-]+)~', $url, $matches)) {
            return $matches[1];
        }

        parse_str(parse_url($url, PHP_URL_QUERY) ?: '', $query);

        return isset($query['id']) && preg_match('/^[a-zA-Z0-9_-]+$/', $query['id'])
            ? $query['id']
            : null;
    }

    private function canAccessLiveClass(LiveClass $liveClass): bool
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isBranchManager()) {
            return (int) $liveClass->branch_id === (int) $user->branch_id;
        }

        if ($user->isFaculty()) {
            return (int) $liveClass->faculty_id === (int) $user->id;
        }

        if (!$user->isStudent() || (int) $liveClass->branch_id !== (int) $user->branch_id) {
            return false;
        }

        if (!in_array($liveClass->course_id, $this->studentCourseIds())) {
            return false;
        }

        if ($liveClass->batch_id) {
            $batchIds = $user->student?->batches()
                ->wherePivot('status', 'active')
                ->pluck('batches.id')
                ->all() ?? [];

            return in_array($liveClass->batch_id, $batchIds);
        }

        return true;
    }

    private function canAccessVideoCourse(VideoCourse $videoCourse): bool
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ((int) $videoCourse->branch_id !== (int) $user->branch_id) {
            return false;
        }

        if ($user->isBranchManager() || $user->isFaculty()) {
            return true;
        }

        return $user->isStudent()
            && $videoCourse->is_published
            && in_array($videoCourse->course_id, $this->studentCourseIds());
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isStudent = $user->isStudent();
        $canManage = $this->canManage();

        $coursesQuery = VideoCourse::with(['course', 'lessons' => function ($query) use ($isStudent) {
            if ($isStudent) {
                $query->where('is_published', true);
            }
            $query->orderBy('sort_order');
        }]);

        $classesQuery = LiveClass::with(['course', 'batch', 'faculty', 'recordings'])
            ->orderBy('scheduled_start_at');

        if ($isStudent) {
            $courseIds = $this->studentCourseIds();
            $batchIds = $user->student?->batches()
                ->wherePivot('status', 'active')
                ->pluck('batches.id')
                ->all() ?? [];

            $coursesQuery->where('is_published', true)
                ->where('branch_id', $user->branch_id)
                ->whereIn('course_id', $courseIds);

            $classesQuery->where('branch_id', $user->branch_id)
                ->whereIn('course_id', $courseIds)
                ->where(function ($query) use ($batchIds) {
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

        $recordingsQuery = LiveRecording::with('liveClass.course')->where('status', 'ready');

        if ($isStudent) {
            $studentBatchIds = $user->student?->batches()
                ->wherePivot('status', 'active')
                ->pluck('batches.id')
                ->all() ?? [];
            $courseIds = $this->studentCourseIds();

            $recordingsQuery->whereHas('liveClass', function ($query) use ($studentBatchIds, $courseIds, $user) {
                $query->where('branch_id', $user->branch_id)
                    ->whereIn('course_id', $courseIds)
                    ->where(function ($batchQuery) use ($studentBatchIds) {
                        $batchQuery->whereNull('batch_id')->orWhereIn('batch_id', $studentBatchIds);
                    });
            });
        } elseif ($user->isFaculty()) {
            $recordingsQuery->whereHas('liveClass', fn ($query) => $query->where('faculty_id', $user->id));
        } elseif (!$user->isSuperAdmin()) {
            $recordingsQuery->whereHas('liveClass', fn ($query) => $query->where('branch_id', $user->branch_id));
        }

        $recordings = $recordingsQuery->latest('completed_at')->limit(30)->get();

        $courses = Course::where('status', 'active')->orderBy('name')->get();
        $batches = Batch::where('status', 'active')->with('course')->orderBy('name')->get();
        $branches = Branch::where('status', 'active')->orderBy('name')->get();
        $faculty = \App\Models\User::where('user_type', 'faculty')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('video-learning.index', compact(
            'videoCourses', 'liveClasses', 'recordings', 'courses', 'batches',
            'branches', 'faculty', 'isStudent', 'canManage'
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
            'price' => 'required|numeric|min:0|max:99999999.99',
        ]);

        if (!auth()->user()->isSuperAdmin()) {
            $data['branch_id'] = auth()->user()->branch_id;
        }

        abort_unless($data['branch_id'], 422, 'A branch must be assigned before creating a video course.');

        $data['is_published'] = $request->boolean('is_published');
        VideoCourse::create($data);

        return back()->with('success', 'Recorded video course created successfully.');
    }

    public function storeLesson(Request $request, VideoCourse $videoCourse)
    {
        abort_unless($this->canManage(), 403);
        abort_unless($this->canAccessVideoCourse($videoCourse), 403);

        $data = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:5000',
            'source_url' => 'required|url|max:2000',
            'duration_seconds' => 'nullable|integer|min:1|max:86400',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        if (!$this->isGoogleDriveUrl($data['source_url'])) {
            return back()->withErrors([
                'source_url' => 'Please paste a Google Drive sharing link for this recorded lesson.',
            ])->withInput();
        }

        $data['provider'] = 'google_drive';
        $data['created_by'] = auth()->id();
        $data['is_published'] = $request->boolean('is_published');
        $videoCourse->lessons()->create($data);

        return back()->with('success', 'Google Drive lesson added to the recorded course.');
    }

    public function storeLiveClass(Request $request)
    {
        abort_unless($this->canManage(), 403);

        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'batch_id' => 'nullable|exists:batches,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'faculty_id' => auth()->user()->isFaculty() ? 'nullable' : 'required|exists:users,id',
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:3000',
            'zoom_meeting_url' => 'required|url|max:2000',
            'price' => 'required|numeric|min:0|max:99999999.99',
            'scheduled_start_at' => 'required|date|after:now',
            'scheduled_end_at' => 'required|date|after:scheduled_start_at',
        ]);

        $zoomHost = strtolower(parse_url($data['zoom_meeting_url'], PHP_URL_HOST) ?: '');
        if ($zoomHost !== 'zoom.us' && !str_ends_with($zoomHost, '.zoom.us')) {
            return back()->withErrors([
                'zoom_meeting_url' => 'Please paste a valid Zoom invitation URL.',
            ])->withInput();
        }

        $batch = !empty($data['batch_id']) ? Batch::findOrFail($data['batch_id']) : null;
        if ($batch && (int) $batch->course_id !== (int) $data['course_id']) {
            return back()->withErrors(['batch_id' => 'Selected batch does not belong to the selected course.'])->withInput();
        }

        $branchId = $batch?->branch_id ?: auth()->user()->branch_id ?: Branch::query()->value('id');

        if (!auth()->user()->isSuperAdmin()) {
            abort_unless((int) $branchId === (int) auth()->user()->branch_id, 403);
        }

        if (!$branchId) {
            return back()->withErrors(['branch_id' => 'Create a branch before scheduling live classes.'])->withInput();
        }

        $facultyId = auth()->user()->isFaculty() ? auth()->id() : ($data['faculty_id'] ?? auth()->id());
        $facultyMember = \App\Models\User::whereKey($facultyId)
            ->where('user_type', 'faculty')
            ->where('status', 'active')
            ->first();

        if (!$facultyMember || (int) $facultyMember->branch_id !== (int) $branchId) {
            return back()->withErrors(['faculty_id' => 'Select an active faculty member from the class branch.'])->withInput();
        }

        if ($batch && !$batch->faculty()->where('users.id', $facultyId)->exists() && $batch->primary_mentor_id !== $facultyId) {
            return back()->withErrors(['faculty_id' => 'Selected faculty is not assigned to this batch.'])->withInput();
        }

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
            'zoom_meeting_url' => $data['zoom_meeting_url'],
            'price' => $data['price'],
            'provider' => 'zoom',
            'status' => 'scheduled',
        ]);

        return back()->with('success', 'Zoom live session scheduled successfully.');
    }

    public function storeRecording(Request $request, LiveClass $liveClass)
    {
        abort_unless($this->canManage(), 403);
        abort_unless($this->canAccessLiveClass($liveClass), 403);

        $data = $request->validate([
            'title' => 'required|string|max:191',
            'playback_url' => 'required|url|max:2000',
            'duration_seconds' => 'nullable|integer|min:1|max:86400',
        ]);

        if (!$this->isGoogleDriveUrl($data['playback_url'])) {
            return back()->withErrors([
                'playback_url' => 'Please paste a Google Drive sharing link for the class recording.',
            ])->withInput();
        }

        $data['drive_file_id'] = $this->googleDriveFileId($data['playback_url']);
        $data['uploaded_by'] = auth()->id();
        $data['status'] = 'ready';
        $data['completed_at'] = now();
        $liveClass->recordings()->create($data);

        return back()->with('success', 'Google Drive class recording added.');
    }

    public function showLesson(VideoLesson $lesson)
    {
        abort_unless($lesson->is_published || $this->canManage(), 404);
        $lesson->load('videoCourse.course');
        abort_unless($this->canAccessVideoCourse($lesson->videoCourse), 403);

        $url = $lesson->source_url;
        abort_if(blank($url), 404, 'This lesson does not have a video source yet.');

        $embedUrl = null;
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?: '');

        if ($host === 'drive.google.com') {
            $fileId = $this->googleDriveFileId($url);
            if ($fileId) {
                $embedUrl = 'https://drive.google.com/file/d/' . $fileId . '/preview';
            }
        } elseif ($host === 'youtu.be') {
            $embedUrl = 'https://www.youtube-nocookie.com/embed/' . trim(parse_url($url, PHP_URL_PATH) ?: '', '/');
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            parse_str(parse_url($url, PHP_URL_QUERY) ?: '', $query);
            if (!empty($query['v'])) {
                $embedUrl = 'https://www.youtube-nocookie.com/embed/' . $query['v'];
            }
        } elseif (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)) {
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
        $lesson->loadMissing('videoCourse');
        abort_unless($this->canAccessVideoCourse($lesson->videoCourse), 403);

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

    public function showRecording(LiveRecording $recording)
    {
        $recording->load('liveClass');
        abort_unless($recording->status === 'ready', 404);
        abort_unless($this->canAccessLiveClass($recording->liveClass), 403);

        $fileId = $recording->drive_file_id ?: $this->googleDriveFileId($recording->playback_url);
        $embedUrl = $fileId
            ? 'https://drive.google.com/file/d/' . $fileId . '/preview'
            : $recording->playback_url;

        return view('video-learning.drive-recording', compact('recording', 'embedUrl'));
    }
}
