@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-4">
    <a href="{{ route('video-learning.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">← Back to Video Learning</a>
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-100">
            <span class="text-[10px] uppercase tracking-wider font-bold text-emerald-700">Google Drive class recording</span>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1">{{ $recording->title }}</h1>
            <p class="text-xs text-slate-500 mt-1">{{ $recording->liveClass->title }} · {{ $recording->liveClass->course->name ?? 'Course' }}</p>
        </div>
        <div class="bg-black aspect-video">
            <iframe src="{{ $embedUrl }}" class="w-full h-full" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen title="{{ $recording->title }}"></iframe>
        </div>
        <div class="p-5 flex flex-wrap items-center justify-between gap-3">
            <p class="text-xs text-slate-500">This recording is hosted in Google Drive. Access depends on the sharing permissions set on the Drive file.</p>
            <a href="{{ $recording->playback_url }}" target="_blank" rel="noopener noreferrer" class="rounded-lg bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 text-xs font-bold"><i class="fa-brands fa-google-drive mr-1"></i> Open in Google Drive</a>
        </div>
    </div>
</div>
@endsection
