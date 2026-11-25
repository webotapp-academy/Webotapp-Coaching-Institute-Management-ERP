@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl bg-gradient-to-r from-slate-950 via-indigo-950 to-brand-800 p-6 sm:p-8 text-white shadow-lg">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div>
                <div class="flex items-center gap-2 text-indigo-200 text-[10px] font-bold uppercase tracking-[.18em]"><i class="fa-solid fa-circle-play"></i> Video Learning Hub</div>
                <h1 class="text-2xl sm:text-3xl font-extrabold mt-2">Learn live. Replay anytime.</h1>
                <p class="text-sm text-indigo-100/80 mt-2 max-w-2xl">Your recorded lessons, upcoming live classrooms and previous class recordings — all in one place.</p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs">
                <div class="rounded-xl border border-white/15 bg-white/10 px-4 py-3"><div class="text-indigo-200">Courses</div><div class="text-2xl font-bold">{{ $videoCourses->count() }}</div></div>
                <div class="rounded-xl border border-white/15 bg-white/10 px-4 py-3"><div class="text-indigo-200">Live / upcoming</div><div class="text-2xl font-bold">{{ $liveClasses->whereIn('status', ['live','scheduled'])->count() }}</div></div>
                <div class="rounded-xl border border-white/15 bg-white/10 px-4 py-3"><div class="text-indigo-200">Recordings</div><div class="text-2xl font-bold">{{ $recordings->count() }}</div></div>
            </div>
        </div>
    </div>

    @if($canManage)
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <section class="xl:col-span-1 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-4"><span class="h-8 w-8 rounded-lg bg-indigo-50 text-brand-700 flex items-center justify-center"><i class="fa-solid fa-folder-plus"></i></span><div><h2 class="font-bold text-sm">Create video course</h2><p class="text-[10px] text-slate-400">Organize lessons under an ERP course</p></div></div>
            <form method="POST" action="{{ route('video-learning.courses.store') }}" class="space-y-3 text-xs">@csrf
                <label class="block"><span class="font-semibold text-slate-600">ERP course</span><select name="course_id" required class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50">@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->name }}</option>@endforeach</select></label>
                <label class="block"><span class="font-semibold text-slate-600">Branch</span><select name="branch_id" required class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50">@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></label>
                <label class="block"><span class="font-semibold text-slate-600">Display title</span><input name="title" required maxlength="191" placeholder="e.g. Complete Java Masterclass" class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50"></label>
                <label class="block"><span class="font-semibold text-slate-600">Description</span><textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50"></textarea></label>
                <label class="flex items-center gap-2"><input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-brand-600"><span class="text-slate-600">Publish to enrolled students</span></label>
                <button class="w-full rounded-lg bg-brand-600 hover:bg-brand-700 text-white py-2.5 font-bold">Create video course</button>
            </form>
        </section>

        <section class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-4"><span class="h-8 w-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center"><i class="fa-solid fa-tower-broadcast"></i></span><div><h2 class="font-bold text-sm">Schedule a live class</h2><p class="text-[10px] text-slate-400">LiveKit classroom with automatic recording</p></div></div>
            <form method="POST" action="{{ route('video-learning.live-classes.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">@csrf
                <label class="block"><span class="font-semibold text-slate-600">Course</span><select name="course_id" required class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50">@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->name }}</option>@endforeach</select></label>
                <label class="block"><span class="font-semibold text-slate-600">Batch (optional)</span><select name="batch_id" class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50"><option value="">All eligible students</option>@foreach($batches as $batch)<option value="{{ $batch->id }}">{{ $batch->name }} — {{ $batch->course->name ?? '' }}</option>@endforeach</select></label>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isBranchManager())
                <label class="block"><span class="font-semibold text-slate-600">Faculty</span><select name="faculty_id" class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50">@foreach($faculty as $member)<option value="{{ $member->id }}">{{ $member->name }}</option>@endforeach</select></label>
                @endif
                <label class="block"><span class="font-semibold text-slate-600">Class title</span><input name="title" required maxlength="191" placeholder="e.g. Kafka Consumer Groups — Live Q&A" class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50"></label>
                <label class="block"><span class="font-semibold text-slate-600">Starts at</span><input type="datetime-local" name="scheduled_start_at" required class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50"></label>
                <label class="block"><span class="font-semibold text-slate-600">Ends at</span><input type="datetime-local" name="scheduled_end_at" required class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50"></label>
                <label class="block sm:col-span-2"><span class="font-semibold text-slate-600">Description</span><input name="description" maxlength="3000" placeholder="Topics / instructions for students" class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50"></label>
                <div class="sm:col-span-2 flex flex-wrap items-center justify-between gap-2"><span class="text-[10px] text-emerald-700"><i class="fa-solid fa-circle-check mr-1"></i> Automatic recording is enabled by default</span><button class="rounded-lg bg-rose-600 hover:bg-rose-700 text-white px-5 py-2.5 font-bold"><i class="fa-regular fa-calendar-plus mr-1"></i> Schedule class</button></div>
            </form>
        </section>
    </div>
    @endif

    <section class="space-y-3">
        <div class="flex items-end justify-between"><div><h2 class="text-lg font-extrabold text-slate-900">Recorded courses</h2><p class="text-xs text-slate-500">On-demand lessons available for your account</p></div><span class="text-xs text-slate-400">{{ $videoCourses->count() }} courses</span></div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse($videoCourses as $videoCourse)
            <article class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="h-2 bg-gradient-to-r from-brand-600 to-cyan-500"></div>
                <div class="p-5">
                    <div class="flex items-start justify-between gap-2"><span class="text-[10px] font-bold uppercase tracking-wider text-brand-700 bg-brand-50 rounded-full px-2.5 py-1">{{ $videoCourse->course->name ?? 'Course' }}</span><i class="fa-solid fa-circle-play text-2xl text-brand-500"></i></div>
                    <h3 class="font-extrabold text-base text-slate-900 mt-3">{{ $videoCourse->title }}</h3>
                    <p class="text-xs text-slate-500 mt-1 min-h-8">{{ $videoCourse->description ?: 'Recorded lessons and course resources.' }}</p>
                    <div class="mt-4 space-y-2">
                        @forelse($videoCourse->lessons as $lesson)
                        <a href="{{ route('video-learning.lessons.show', $lesson) }}" class="flex items-center gap-3 rounded-xl bg-slate-50 hover:bg-brand-50 p-3 transition">
                            <span class="h-8 w-8 shrink-0 rounded-lg bg-white text-brand-600 flex items-center justify-center shadow-sm"><i class="fa-solid fa-play"></i></span>
                            <span class="min-w-0 flex-1"><span class="block text-xs font-bold text-slate-800">{{ $lesson->title }}</span><span class="block text-[10px] text-slate-400">{{ $lesson->duration_seconds ? gmdate('H:i:s', $lesson->duration_seconds) : 'Recorded lesson' }}</span></span>
                            <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                        </a>
                        @empty
                        <p class="text-xs text-slate-400 rounded-lg bg-slate-50 p-3">No published lessons yet.</p>
                        @endforelse
                    </div>
                    @if($canManage)
                    <details class="mt-4 border-t border-slate-100 pt-3">
                        <summary class="cursor-pointer text-xs font-bold text-brand-700">＋ Add recorded lesson</summary>
                        <form method="POST" action="{{ route('video-learning.lessons.store', $videoCourse) }}" class="mt-3 space-y-2 text-xs">@csrf
                            <input name="title" required placeholder="Lesson title" class="w-full rounded-lg border-slate-200 bg-slate-50">
                            <input name="source_url" type="url" required placeholder="Video URL (MP4, YouTube or Vimeo)" class="w-full rounded-lg border-slate-200 bg-slate-50">
                            <input name="duration_seconds" type="number" min="1" placeholder="Duration in seconds (optional)" class="w-full rounded-lg border-slate-200 bg-slate-50">
                            <textarea name="description" rows="2" placeholder="Lesson summary" class="w-full rounded-lg border-slate-200 bg-slate-50"></textarea>
                            <label class="flex items-center gap-2"><input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-brand-600"> Publish lesson</label>
                            <button class="rounded-lg bg-brand-600 text-white px-4 py-2 font-bold">Save lesson</button>
                        </form>
                    </details>
                    @endif
                </div>
            </article>
            @empty
            <div class="md:col-span-3 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center"><i class="fa-solid fa-film text-3xl text-slate-300"></i><p class="font-bold text-slate-700 mt-3">No video courses available yet</p><p class="text-xs text-slate-400 mt-1">Create a video course and publish its first recorded lesson.</p></div>
            @endforelse
        </div>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between"><div><h2 class="font-extrabold text-base text-slate-900">Live classroom</h2><p class="text-xs text-slate-500">Current and scheduled sessions</p></div><span class="rounded-full bg-rose-50 text-rose-700 px-2.5 py-1 text-[10px] font-bold">LIVE & SCHEDULED</span></div>
            <div class="divide-y divide-slate-100">
                @forelse($liveClasses as $class)
                <div class="p-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0"><div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full {{ $class->status === 'live' ? 'bg-rose-500 animate-pulse' : 'bg-amber-400' }}"></span><span class="text-[10px] font-bold uppercase {{ $class->status === 'live' ? 'text-rose-600' : 'text-amber-600' }}">{{ $class->status }}</span></div><h3 class="font-bold text-sm text-slate-900 mt-1">{{ $class->title }}</h3><p class="text-[11px] text-slate-500">{{ $class->course->name ?? 'Course' }} · {{ $class->batch->name ?? 'All eligible students' }} · {{ $class->scheduled_start_at->format('d M, h:i A') }}</p></div>
                    @if($class->status === 'live')
                    <form method="POST" action="{{ route('video-learning.live-classes.join', $class) }}">@csrf<button class="rounded-lg bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 text-xs font-bold">Join classroom</button></form>
                    @elseif($canManage && $class->status === 'scheduled')
                    <form method="POST" action="{{ route('video-learning.live-classes.start', $class) }}">@csrf<button class="rounded-lg bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 text-xs font-bold">Start & record</button></form>
                    @else
                    <span class="text-[10px] text-slate-400">{{ $class->scheduled_end_at->format('h:i A') }}</span>
                    @endif
                </div>
                @empty
                <div class="p-8 text-center text-xs text-slate-400">No live classes scheduled.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-slate-100"><h2 class="font-extrabold text-base text-slate-900">Previous class recordings</h2><p class="text-xs text-slate-500">Completed live sessions are published here after recording processing</p></div>
            <div class="divide-y divide-slate-100">
                @forelse($recordings as $recording)
                <div class="p-4 flex items-center justify-between gap-3">
                    <div class="min-w-0"><h3 class="font-bold text-sm text-slate-800">{{ $recording->liveClass->title ?? 'Live class recording' }}</h3><p class="text-[11px] text-slate-500">{{ $recording->liveClass->course->name ?? 'Course' }} · {{ $recording->completed_at?->format('d M Y') }}</p></div>
                    @if($recording->status === 'ready')<a href="{{ route('video-learning.recordings.play', $recording) }}" class="rounded-lg bg-slate-100 hover:bg-brand-50 text-brand-700 px-3 py-2 text-xs font-bold">Watch replay</a>@else<span class="text-[10px] text-slate-400">{{ ucfirst($recording->status) }}</span>@endif
                </div>
                @empty
                <div class="p-8 text-center text-xs text-slate-400">No processed recordings yet. Recordings will appear after the LiveKit egress-completed event is received.</div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
