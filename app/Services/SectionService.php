<?php

namespace App\Services;

final class SectionService
{
    public function __construct(
        protected MediaService $mediaService
    ) {}

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
