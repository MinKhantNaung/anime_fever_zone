<?php

namespace App\Services;

use App\Models\Section;

final class SectionService
{
    public function __construct(
        protected Section $section,
        protected MediaService $mediaService
    ) {}

    public function store($postId, array $validated)
    {
        $section = $this->section->create([
            'post_id' => $postId,
            'heading' => $validated['heading'],
            'body' => $validated['body']
        ]);

        return $section;
    }

    public function update(Section $section, array $validated)
    {
        $section->update([
            'heading' => $validated['heading'],
            'body' => $validated['body']
        ]);

        return $section;
    }

    public function destroy($section)
    {
        // delete section's all media
        $medias = $section->media;

        foreach ($medias as $media) {
            $this->mediaService->destroy($media);
        }

        $section->delete();
    }
}
