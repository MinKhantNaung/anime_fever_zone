<?php

namespace App\Services;

use App\Models\Video;

final class VideoService
{
    public function __construct(protected Video $video) {}

    public function store(array $validated)
    {
        $video = $this->video->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'youtube_url' => $validated['youtube_url'],
            'youtube_id' => $validated['youtube_id'],
            'is_publish' => $validated['is_publish'] ?? false,
            'is_trending' => $validated['is_trending'] ?? false,
        ]);

        return $video;
    }

    public function update(Video $video, array $validated)
    {
        $video->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'youtube_url' => $validated['youtube_url'],
            'youtube_id' => $validated['youtube_id'],
            'is_publish' => $validated['is_publish'] ?? false,
            'is_trending' => $validated['is_trending'] ?? false,
        ]);
    }

    public function destroy(Video $video)
    {
        $video->delete();
    }

    public function togglePublish(Video $video)
    {
        $video->update([
            'is_publish' => ! $video->is_publish,
        ]);
    }
}
