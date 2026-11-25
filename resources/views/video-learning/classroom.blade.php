@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('video-learning.index') }}" class="text-xs text-brand-600 hover:underline">← Back to Video Learning</a>
            <h1 class="text-xl font-bold text-slate-900 mt-1">{{ $liveClass->title }}</h1>
            <p class="text-xs text-slate-500">{{ $liveClass->course->name ?? 'Course' }} · Faculty: {{ $liveClass->faculty->name ?? 'Faculty' }}</p>
        </div>
        <span class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700"><span class="h-2 w-2 rounded-full bg-rose-500 animate-pulse"></span> LIVE · Recording enabled</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_260px] gap-4">
        <div class="bg-slate-950 rounded-2xl p-3 sm:p-4">
            <div id="video-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-3 min-h-[420px]">
                <div id="local-media" class="rounded-xl bg-slate-800 min-h-[260px] flex items-center justify-center text-slate-300 text-sm">Connecting to classroom…</div>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-2 pt-4">
                <button id="mic-toggle" class="{{ $canPublish ? '' : 'hidden' }} rounded-lg bg-slate-700 px-4 py-2 text-white text-xs font-semibold">Mute microphone</button>
                <button id="camera-toggle" class="{{ $canPublish ? '' : 'hidden' }} rounded-lg bg-slate-700 px-4 py-2 text-white text-xs font-semibold">Turn camera off</button>
                <button id="leave-room" class="rounded-lg bg-rose-600 px-4 py-2 text-white text-xs font-semibold">Leave class</button>
            </div>
            <p id="connection-status" class="text-center text-[11px] text-slate-400 mt-3">Connecting securely…</p>
        </div>
        <aside class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3">
            <h2 class="font-bold text-sm">Class information</h2>
            <div class="text-xs text-slate-600 space-y-2">
                <p><strong>Course:</strong> {{ $liveClass->course->name ?? '—' }}</p>
                <p><strong>Room:</strong> {{ $liveClass->provider_room_name }}</p>
                <p><strong>Recording:</strong> Automatically enabled</p>
                <p class="text-slate-400">Please inform participants that this session is being recorded.</p>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
import { Room, RoomEvent, Track } from 'https://cdn.jsdelivr.net/npm/livekit-client/dist/livekit-client.esm.mjs';

const room = new Room({ adaptiveStream: true, dynacast: true });\nconst canPublish = @json($canPublish);
const statusEl = document.getElementById('connection-status');
const grid = document.getElementById('video-grid');
const local = document.getElementById('local-media');

function attachTrack(track, element, identity) {
    const wrapper = document.createElement('div');
    wrapper.className = 'relative min-h-[220px] overflow-hidden rounded-xl bg-slate-800';
    wrapper.dataset.identity = identity;
    const media = track.attach();
    media.className = 'h-full w-full min-h-[220px] object-cover';
    wrapper.appendChild(media);
    const name = document.createElement('span');
    name.className = 'absolute bottom-2 left-2 rounded bg-black/60 px-2 py-1 text-xs text-white';
    name.textContent = identity;
    wrapper.appendChild(name);
    if (element === 'local') local.replaceWith(wrapper);
    else grid.appendChild(wrapper);
}

room.on(RoomEvent.TrackSubscribed, (track, publication, participant) => {
    if (track.kind === Track.Kind.Video || track.kind === Track.Kind.Audio) attachTrack(track, 'remote', participant.name || participant.identity);
});
room.on(RoomEvent.TrackUnsubscribed, (track) => track.detach().forEach(el => el.remove()));
room.on(RoomEvent.Disconnected, () => statusEl.textContent = 'Disconnected from classroom.');

try {
    await room.connect(@json($livekitUrl), @json($token), { autoSubscribe: true });
    if (canPublish) {
        await room.localParticipant.enableCameraAndMicrophone();
        room.localParticipant.videoTrackPublications.forEach(p => p.track && attachTrack(p.track, 'local', 'You'));
        statusEl.textContent = 'Connected. Your camera and microphone are available. This class is being recorded.';
    } else {
        statusEl.textContent = 'Connected in student mode. You can watch and listen; the faculty is recording this class.';
    }
} catch (error) {
    statusEl.textContent = 'Could not connect: ' + (error?.message || 'Please check camera, microphone and network permissions.');
}

document.getElementById('mic-toggle').addEventListener('click', async () => {
    if (!canPublish) return;
    const enabled = room.localParticipant.isMicrophoneEnabled;
    await room.localParticipant.setMicrophoneEnabled(!enabled);
    document.getElementById('mic-toggle').textContent = enabled ? 'Unmute microphone' : 'Mute microphone';
});
document.getElementById('camera-toggle').addEventListener('click', async () => {
    if (!canPublish) return;
    const enabled = room.localParticipant.isCameraEnabled;
    await room.localParticipant.setCameraEnabled(!enabled);
    document.getElementById('camera-toggle').textContent = enabled ? 'Turn camera on' : 'Turn camera off';
});
document.getElementById('leave-room').addEventListener('click', async () => {
    await room.disconnect();
    window.location.href = @json(route('video-learning.index'));
});
</script>
@endpush
