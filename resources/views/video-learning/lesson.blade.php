@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-4">
    <a href="{{ route('video-learning.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">← Back to Video Learning</a>
    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_300px] gap-5">
        <section class="space-y-3">
            <div class="overflow-hidden rounded-2xl bg-black aspect-video flex items-center justify-center">
                @if($embedUrl)
                    <iframe src="{{ $embedUrl }}" class="w-full h-full" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen referrerpolicy="strict-origin-when-cross-origin" title="{{ $lesson->title }}"></iframe>
                @elseif(preg_match('/\.(mp4|webm|ogg)(?:\?.*)?$/i', $lesson->source_url))
                    <video id="lesson-player" class="w-full h-full" controls playsinline preload="metadata" src="{{ $lesson->source_url }}"></video>
                @else
                    <div class="text-center text-white p-8"><i class="fa-solid fa-link text-3xl mb-3"></i><p class="font-semibold">Open recorded lesson</p><a href="{{ $lesson->source_url }}" target="_blank" rel="noopener" class="inline-block mt-3 rounded-lg bg-brand-600 px-4 py-2 text-xs font-bold">Open video source</a></div>
                @endif
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <span class="text-[10px] uppercase tracking-wider font-bold text-brand-700">{{ $lesson->videoCourse->course->name ?? 'Course' }}</span>
                <h1 class="text-xl font-extrabold text-slate-900 mt-1">{{ $lesson->title }}</h1>
                <p class="text-sm text-slate-500 mt-2">{{ $lesson->description }}</p>
            </div>
        </section>
        <aside class="bg-white border border-slate-200 rounded-2xl p-5 h-fit">
            <h2 class="font-extrabold text-sm">Learning progress</h2>
            <p class="text-xs text-slate-500 mt-1">Your progress is saved for this lesson.</p>
            <div class="mt-4 flex items-center justify-between text-xs"><span class="text-slate-500">Status</span><strong id="progress-status">{{ $progress->completed ? 'Completed' : 'In progress' }}</strong></div>
            <div class="mt-4 h-2 rounded-full bg-slate-100 overflow-hidden"><div id="progress-bar" class="h-full bg-emerald-500" style="width: {{ $progress->completed ? 100 : 0 }}%"></div></div>
            <button id="complete-lesson" class="mt-5 w-full rounded-lg bg-brand-600 hover:bg-brand-700 text-white py-2.5 text-xs font-bold">Mark lesson completed</button>
            <p id="progress-message" class="text-[10px] text-slate-400 mt-2">Progress updates when you complete the lesson.</p>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const video = document.getElementById('lesson-player');
    const complete = document.getElementById('complete-lesson');
    const status = document.getElementById('progress-status');
    const bar = document.getElementById('progress-bar');
    const message = document.getElementById('progress-message');
    let watched = {{ (int) $progress->watched_seconds }};\n    let lastSaved = watched;
    async function save(done) {
        if (video && Number.isFinite(video.currentTime)) watched = Math.floor(video.currentTime);
        const response = await fetch(@json(route('video-learning.progress', $lesson)), {
            method: 'POST',
            headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())},
            body: JSON.stringify({watched_seconds:watched, completed:done})
        });
        if (!response.ok) throw new Error('Progress could not be saved');
        status.textContent = done ? 'Completed' : 'In progress';
        bar.style.width = done ? '100%' : '0%';
        message.textContent = done ? 'Lesson marked as completed.' : 'Progress saved.';\n        lastSaved = watched;
    }
    if (video) {
        video.addEventListener('timeupdate', () => {\n            watched = Math.floor(video.currentTime);\n            if (watched - lastSaved >= 15) save(false).catch(() => {});\n        });
        video.addEventListener('pause', () => save(false).catch(() => {}));
        video.addEventListener('ended', () => save(true).catch(() => {}));
    }
    complete.addEventListener('click', () => save(true).catch(error => message.textContent = error.message));
})();
</script>
@endpush
