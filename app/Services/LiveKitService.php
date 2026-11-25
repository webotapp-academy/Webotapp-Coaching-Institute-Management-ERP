<?php

namespace App\\Services;

use Illuminate\\Support\\Facades\\Http;
use Illuminate\\Support\\Str;
use RuntimeException;

class LiveKitService
{
    public function isConfigured(): bool
    {
        return filled(config('services.livekit.url'))
            && filled(config('services.livekit.api_key'))
            && filled(config('services.livekit.api_secret'));
    }

    private function jwt(array $claims): string
    {
        $key = config('services.livekit.api_key');
        $secret = config('services.livekit.api_secret');

        if (!$key || !$secret) {
            throw new RuntimeException('LiveKit credentials are not configured.');
        }

        $now = time();
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = array_merge([
            'iss' => $key,
            'nbf' => $now - 5,
            'iat' => $now,
            'exp' => $now + 3600,
        ], $claims);

        $encode = fn ($value) => rtrim(strtr(base64_encode(json_encode($value, JSON_UNESCAPED_SLASHES)), '+/', '-_'), '=');
        $unsigned = $encode($header) . '.' . $encode($payload);
        $signature = hash_hmac('sha256', $unsigned, $secret, true);

        return $unsigned . '.' . rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    }

    private function post(string $service, string $method, array $payload, array $grant)
    {
        $url = rtrim(config('services.livekit.url'), '/') . '/twirp/livekit.' . $service . '/' . $method;

        return Http::asJson()
            ->withToken($this->jwt(['video' => $grant]))
            ->timeout(20)
            ->post($url, $payload)
            ->throw()
            ->json();
    }

    public function createRoom(string $roomName): array
    {
        return $this->post('RoomService', 'CreateRoom', [
            'name' => $roomName,
            'empty_timeout' => 600,
            'departure_timeout' => 60,
        ], ['roomCreate' => true]);
    }

    public function startRecording(string $roomName): array
    {
        $bucket = config('services.livekit.recording_bucket');
        $accessKey = config('services.livekit.recording_access_key');
        $secretKey = config('services.livekit.recording_secret_key');
        $region = config('services.livekit.recording_region');

        if (!$bucket || !$accessKey || !$secretKey || !$region) {
            throw new RuntimeException('Recording storage is not configured. Set the LiveKit recording S3 environment variables.');
        }

        return $this->post('Egress', 'StartRoomCompositeEgress', [
            'room_name' => $roomName,
            'layout' => 'grid',
            'file' => [
                'filepath' => 'class-recordings/' . $roomName . '/' . Str::uuid() . '.mp4',
                's3' => [
                    'access_key' => $accessKey,
                    'secret' => $secretKey,
                    'region' => $region,
                    'bucket' => $bucket,
                ],
            ],
        ], ['roomRecord' => true]);
    }

    public function createJoinToken(string $roomName, string $identity, string $displayName, bool $canPublish): string
    {
        return $this->jwt([
            'sub' => $identity,
            'name' => $displayName,
            'video' => [
                'roomJoin' => true,
                'room' => $roomName,
                'canPublish' => $canPublish,
                'canSubscribe' => true,
                'canPublishData' => true,
            ],
        ]);
    }

    public function serverUrl(): string
    {
        return (string) config('services.livekit.url');
    }
}
