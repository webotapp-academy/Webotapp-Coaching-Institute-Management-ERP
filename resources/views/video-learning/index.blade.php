@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl bg-gradient-to-r from-slate-950 via-indigo-950 to-brand-800 p-6 sm:p-8 text-white shadow-lg">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div>
                <div class="flex items-center gap-2 text-indigo-200 text-[10px] font-bold uppercase tracking-[.18em]"><i class="fa-solid fa-graduation-cap"></i> Video Learning Hub</div>
                <h1 class="text-2xl sm:text-3xl font-extrabold mt-2">Live sessions. Recorded courses.</h1>
                <p class="text-sm text-indigo-100/80 mt-2 max-w-2xl">Join live classes through Zoom invitations and learn anytime from recorded lessons hosted in Google Drive.</p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs">
                <div class="rounded-xl border border-white/15 bg-white/10 px-4 py-3"><div class="text-indigo-200">Recorded courses</div><div class="text-2xl font-bold">{{ $videoCourses->count() }}</div></div>
                <div class="rounded-xl border border-white/15 bg-white/10 px-4 py-3"><div class="text-indigo-200">Live sessions</div><div class="text-2xl font-bold">{{ $liveClasses->count() }}</div></div>
                <div class="rounded-xl border border-white/15 bg-white/10 px-4 py-3"><div class="text-indigo-200">Drive recordings</div><div class="text-2xl font-bold">{{ $recordings->count() }}</div></div>
            </div>
        </div>
        <div class="mt-5 flex flex-wrap gap-3 text-[11px] text-indigo-100/90">
            <span><i class="fa-solid fa-video mr-1"></i> Live sessions use your Zoom meeting link</span>
            <span><i class="fa-brands fa-google-drive mr-1"></i> Recorded videos stay in your Google Drive</span>
        </div>
    </div>

    @if($canManage)
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <section class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <span class="h-8 w-8 rounded-lg bg-indigo-50 text-brand-700 flex items-center justify-center"><i class="fa-solid fa-film"></i></span>
                <div><h2 class="font-bold text-sm">Create recorded video course</h2><p class="text-[10px] text-slate-400">Set one price for the complete recorded course</p></div>
            </div>
            <form method="POST" action="{{ route('video-learning.courses.store') }}" class="space-y-3 text-xs">@csrf
                <label class="block"><span class="font-semibold text-slate-600">ERP course</span><select name="course_id" required class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50">@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->name }}</option>@endforeach</select></label>
                @if(auth()->user()->isSuperAdmin())
                <label class="block"><span class="font-semibold text-slate-600">Branch</span><select name="branch_id" required class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50">@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></label>
                @else
                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                @endif
                <label class="block"><span class="font-semibold text-slate-600">Recorded course title</span><input name="title" required maxlength="191" placeholder="e.g. Complete Kafka Masterclass" class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50"></label>
                <label class="block"><span class="font-semibold text-slate-600">Description</span><textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50"></textarea></label>
                <label class="block"><span class="font-semibold text-slate-600">Course price (INR ₹)</span><input name="price" type="number" min="0" step="0.01" required value="0" class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50"></label>
                <label class="flex items-center gap-2"><input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-brand-600"><span class="text-slate-600">Publish to eligible students</span></label>
                <button class="w-full rounded-lg bg-brand-600 hover:bg-brand-700 text-white py-2.5 font-bold">Create recorded course</button>
            </form>
        </section>

        <section class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <span class="h-8 w-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center"><i class="fa-solid fa-video"></i></span>
                <div><h2 class="font-bold text-sm">Schedule a Zoom live session</h2><p class="text-[10px] text-slate-400">Paste the Zoom invitation link students will use to join</p></div>
            </div>
            <form method="POST" action="{{ route('video-learning.live-classes.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">@csrf
                <label class="block"><span class="font-semibold text-slate-600">ERP course</span><select name="course_id" required class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50">@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->name }}</option>@endforeach</select></label>
                <label class="block"><span class="font-semibold text-slate-600">Batch (optional)</span><select name="batch_id" class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50"><option value="">All eligible students</option>@foreach($batches as $batch)<option value="{{ $batch->id }}">{{ $batch->name }} — {{ $batch->course->name ?? '' }}</option>@endforeach</select></label>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isBranchManager())
                <label class="block"><span class="font-semibold text-slate-600">Faculty</span><select name="faculty_id" required class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50">@foreach($faculty as $member)<option value="{{ $member->id }}">{{ $member->name }}</option>@endforeach</select></label>
                @endif
                <label class="block"><span class="font-semibold text-slate-600">Session title</span><input name="title" required maxlength="191" placeholder="e.g. Kafka Consumer Groups - Live Q&A" class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50"></label>
                <label class="block"><span class="font-semibold text-slate-600">Starts at</span><input type="datetime-local" name="scheduled_start_at" required class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50"></label>
                <label class="block"><span class="font-semibold text-slate-600">Ends at</span><input type="datetime-local" name="scheduled_end_at" required class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50"></label>
                <label class="block sm:col-span-2"><span class="font-semibold text-slate-600">Zoom invitation URL</span><input name="zoom_meeting_url" type="url" required placeholder="https://us04web.zoom.us/j/..." class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50"></label>
                <label class="block"><span class="font-semibold text-slate-600">Live session price (INR ₹)</span><input name="price" type="number" min="0" step="0.01" required value="0" class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50"></label>
                <label class="block"><span class="font-semibold text-slate-600">Description</span><input name="description" maxlength="3000" placeholder="Topics / instructions" class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50"></label>
                <div class="sm:col-span-2 flex flex-wrap items-center justify-between gap-2"><span class="text-[10px] text-slate-500">Students join directly through Zoom.</span><button class="rounded-lg bg-rose-600 hover:bg-rose-700 text-white px-5 py-2.5 font-bold"><i class="fa-regular fa-calendar-plus mr-1"></i> Save live session</button></div>
            </form>
        </section>
    </div>
    @endif

    <section class="space-y-3">
        <div class="flex items-end justify-between"><div><h2 class="text-lg font-extrabold text-slate-900">Recorded video courses</h2><p class="text-xs text-slate-500">Course videos are played from Google Drive</p></div><span class="text-xs text-slate-400">{{ $videoCourses->count() }} courses</span></div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse($videoCourses as $videoCourse)
            <article class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="h-2 bg-gradient-to-r from-brand-600 to-cyan-500"></div>
                <div class="p-5">
                    <div class="flex items-start justify-between gap-2"><span class="text-[10px] font-bold uppercase tracking-wider text-brand-700 bg-brand-50 rounded-full px-2.5 py-1">{{ $videoCourse->course->name ?? 'Course' }}</span><span class="text-lg font-extrabold text-slate-900">₹{{ number_format((float) $videoCourse->price, 2) }}</span></div>
                    <h3 class="font-extrabold text-base text-slate-900 mt-3">{{ $videoCourse->title }}</h3>
                    <p class="text-xs text-slate-500 mt-1 min-h-8">{{ $videoCourse->description ?: 'Recorded lessons hosted on Google Drive.' }}</p>
                    <div class="mt-4 space-y-2">
                        @forelse($videoCourse->lessons as $lesson)
                        <a href="{{ route('video-learning.lessons.show', $lesson) }}" class="flex items-center gap-3 rounded-xl bg-slate-50 hover:bg-brand-50 p-3 transition">
                            <span class="h-8 w-8 shrink-0 rounded-lg bg-white text-brand-600 flex items-center justify-center shadow-sm"><i class="fa-solid fa-play"></i></span>
                            <span class="min-w-0 flex-1"><span class="block text-xs font-bold text-slate-800">{{ $lesson->title }}</span><span class="block text-[10px] text-slate-400">{{ $lesson->duration_seconds ? gmdate('H:i:s', $lesson->duration_seconds) : 'Google Drive video' }}</span></span>
                            <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                        </a>
                        @empty
                        <p class="text-xs text-slate-400 rounded-lg bg-slate-50 p-3">No published lessons yet.</p>
                        @endforelse
                    </div>
                    @if($canManage)
                    <details class="mt-4 border-t border-slate-100 pt-3">
                        <summary class="cursor-pointer text-xs font-bold text-brand-700">＋ Add Google Drive lesson</summary>
                        <form method="POST" action="{{ route('video-learning.lessons.store', $videoCourse) }}" class="mt-3 space-y-2 text-xs">@csrf
                            <input name="title" required placeholder="Lesson title" class="w-full rounded-lg border-slate-200 bg-slate-50">
                            <input name="source_url" type="url" required placeholder="Google Drive share link" class="w-full rounded-lg border-slate-200 bg-slate-50">
                            <input name="duration_seconds" type="number" min="1" placeholder="Duration in seconds (optional)" class="w-full rounded-lg border-slate-200 bg-slate-50">
                            <textarea name="description" rows="2" placeholder="Lesson summary" class="w-full rounded-lg border-slate-200 bg-slate-50"></textarea>
                            <label class="flex items-center gap-2"><input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-brand-600"> Publish lesson</label>
                            <button class="rounded-lg bg-brand-600 text-white px-4 py-2 font-bold">Save Drive lesson</button>
                        </form>
                    </details>
                    @endif
                </div>
            </article>
            @empty
            <div class="md:col-span-3 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center"><i class="fa-brands fa-google-drive text-3xl text-slate-300"></i><p class="font-bold text-slate-700 mt-3">No recorded courses available yet</p><p class="text-xs text-slate-400 mt-1">Create a course, set its price and add Google Drive video links.</p></div>
            @endforelse
        </div>
    </section>

    <section class="space-y-3">
        <div class="flex items-end justify-between"><div><h2 class="text-lg font-extrabold text-slate-900">Zoom live sessions</h2><p class="text-xs text-slate-500">Students open the Zoom invitation in the Zoom app or browser</p></div><span class="text-xs text-slate-400">{{ $liveClasses->count() }} sessions</span></div>
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            @forelse($liveClasses as $class)
            <article class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-rose-700 bg-rose-50 rounded-full px-2.5 py-1">Zoom live class</span>
                        <h3 class="font-extrabold text-base text-slate-900 mt-2">{{ $class->title }}</h3>
                        <p class="text-xs text-slate-500">{{ $class->course->name ?? 'Course' }} · {{ $class->batch->name ?? 'All eligible students' }}</p>
                    </div>
                    <div class="text-right shrink-0"><div class="text-xl font-extrabold text-slate-900">₹{{ number_format((float) $class->price, 2) }}</div><div class="text-[10px] text-slate-400">per live session</div></div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 rounded-xl p-3">
                    <div><span class="text-slate-400 block">Starts</span><strong class="text-slate-800">{{ $class->scheduled_start_at->format('d M Y, h:i A') }}</strong></div>
                    <div><span class="text-slate-400 block">Ends</span><strong class="text-slate-800">{{ $class->scheduled_end_at->format('h:i A') }}</strong></div>
                </div>
                @if($class->description)<p class="text-xs text-slate-500">{{ $class->description }}</p>@endif
                <a href="{{ $class->zoom_meeting_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 w-full rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 text-xs font-bold"><i class="fa-solid fa-video"></i> Join Zoom meeting</a>
                @if($canManage)
                <details class="border-t border-slate-100 pt-3">
                    <summary class="cursor-pointer text-xs font-bold text-brand-700">＋ Add Google Drive class recording</summary>
                    <form method="POST" action="{{ route('video-learning.live-classes.recordings.store', $class) }}" class="mt-3 space-y-2 text-xs">@csrf
                        <input name="title" required placeholder="Recording title" class="w-full rounded-lg border-slate-200 bg-slate-50">
                        <input name="playback_url" type="url" required placeholder="Google Drive share link" class="w-full rounded-lg border-slate-200 bg-slate-50">
                        <input name="duration_seconds" type="number" min="1" placeholder="Duration in seconds (optional)" class="w-full rounded-lg border-slate-200 bg-slate-50">
                        <button class="rounded-lg bg-brand-600 text-white px-4 py-2 font-bold">Add recording</button>
                    </form>
                </details>
                @endif
                @if($class->recordings->where('status', 'ready')->count())
                <div class="border-t border-slate-100 pt-3 space-y-2">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Available replays</div>
                    @foreach($class->recordings->where('status', 'ready') as $recording)
                    <a href="{{ route('video-learning.recordings.show', $recording) }}" class="flex items-center justify-between gap-2 rounded-lg bg-slate-50 hover:bg-brand-50 p-2.5 text-xs"><span class="font-semibold text-slate-700"><i class="fa-brands fa-google-drive text-emerald-600 mr-1"></i>{{ $recording->title }}</span><span class="text-brand-700 font-bold">Watch <i class="fa-solid fa-arrow-up-right-from-square ml-1"></i></span></a>
                    @endforeach
                </div>
                @endif
            </article>
            @empty
            <div class="xl:col-span-2 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center"><i class="fa-solid fa-video text-3xl text-slate-300"></i><p class="font-bold text-slate-700 mt-3">No Zoom sessions scheduled</p><p class="text-xs text-slate-400 mt-1">Create a live session and paste its Zoom invitation link.</p></div>
            @endforelse
        </div>
    </section>

    <section class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-slate-100"><h2 class="font-extrabold text-base text-slate-900">Google Drive recordings</h2><p class="text-xs text-slate-500">Manual uploads of Zoom recordings and replay links</p></div>
        <div class="divide-y divide-slate-100">
            @forelse($recordings as $recording)
            <div class="p-4 flex items-center justify-between gap-3">
                <div class="min-w-0"><h3 class="font-bold text-sm text-slate-800">{{ $recording->title }}</h3><p class="text-[11px] text-slate-500">{{ $recording->liveClass->title ?? 'Live class' }} · {{ $recording->liveClass->course->name ?? 'Course' }} · {{ $recording->completed_at?->format('d M Y') }}</p></div>
                <a href="{{ route('video-learning.recordings.show', $recording) }}" class="rounded-lg bg-slate-100 hover:bg-brand-50 text-brand-700 px-3 py-2 text-xs font-bold whitespace-nowrap">Watch replay</a>
            </div>
            @empty
            <div class="p-8 text-center text-xs text-slate-400">No Drive recordings added yet.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
