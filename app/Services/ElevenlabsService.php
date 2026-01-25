<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class ElevenlabsService
{
    protected string $apiKey;

    protected string $baseUrl;

    protected string $voiceId;

    protected string $modelId;

    protected string $outputFormat;

    public function __construct()
    {
        $config = config('prism.providers.elevenlabs');

        $this->apiKey = $config['api_key'] ?? '';
        $this->baseUrl = rtrim($config['url'] ?? 'https://api.elevenlabs.io/v1/', '/');
        $this->voiceId = $config['voice_id'] ?? 'EXAVITQu4vr4xnSDxMaL';
        $this->modelId = $config['model_id'] ?? 'eleven_multilingual_v2';
        $this->outputFormat = $config['output_format'] ?? 'mp3_22050_32';
    }

    public function textToSpeech(string $text, ?string $voiceId = null, ?string $modelId = null): ?string
    {
        if (empty($this->apiKey)) {
            Log::error('Elevenlabs API key is not configured');

            return null;
        }

        $voiceId = $voiceId ?? $this->voiceId;
        $modelId = $modelId ?? $this->modelId;

        // Create a cache key based on text content
        $cacheKey = 'elevenlabs_' . md5($text . $voiceId . $modelId) . '.mp3';
        $cachePath = 'audio/' . $cacheKey;

        // Check if audio already exists in cache
        if (Storage::disk('public')->exists($cachePath)) {
            $filePath = storage_path('app/public/' . $cachePath);

            return url(Storage::url($cachePath)) . '?' . filemtime($filePath);
        }

        try {
            $url = "{$this->baseUrl}/text-to-speech/{$voiceId}";

            $response = Http::timeout(config('prism.request_timeout', 30))
                ->withHeaders([
                    'xi-api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'text' => $text,
                    'model_id' => $modelId,
                    'output_format' => $this->outputFormat,
                    'voice_settings' => [
                        'stability' => (float) (config('prism.providers.elevenlabs.stability') ?? 0.5),
                        'similarity_boost' => (float) (config('prism.providers.elevenlabs.similarity_boost') ?? 0.75),
                        'style' => (float) (config('prism.providers.elevenlabs.style') ?? 0.0),
                        'use_speaker_boost' => config('prism.providers.elevenlabs.use_speaker_boost') ?? true,
                    ],
                ]);

            if ($response->successful()) {
                // Store the audio file
                Storage::disk('public')->put($cachePath, $response->body());

                return url(Storage::url($cachePath));
            }

            // Check if quota exceeded
            $body = $response->json();
            if (isset($body['detail']['status']) && $body['detail']['status'] === 'quota_exceeded') {
                return 'limit_reached';
            }

            Log::error('Elevenlabs API request failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (RequestException $e) {
            Log::error('Elevenlabs API request exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function getVoices(): array
    {
        if (empty($this->apiKey)) {
            return [];
        }

        try {
            $url = "{$this->baseUrl}/voices";

            $response = Http::timeout(config('prism.request_timeout', 30))
                ->withHeaders([
                    'xi-api-key' => $this->apiKey,
                ])
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();

                return $data['voices'] ?? [];
            }

            return [];
        } catch (RequestException $e) {
            Log::error('Elevenlabs get voices exception', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
