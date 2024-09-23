<?php

namespace App\Services;

use App\Models\Media;

final class MediaService
{
    public function __construct(protected Media $media, protected FileService $fileService) {}

    public function store($mainModelClass, $mainModel, $mediaFile, $mime)
    {
        $mediaUrl = $this->fileService->storeFile($mediaFile);

        $media = $this->media->create([
            'mediable_id' => $mainModel->id,    // eg: $post->id
            'mediable_type' => $mainModelClass, // eg: Post::class
            'url' => $mediaUrl,                 // eg: file url
            'mime' => $mime                     // eg: 'image'
        ]);

        return $media;
    }

    public function destroy($mediaModel)
    {
        $media = $this->fileService->deleteFile($mediaModel);

        $media->delete();
    }
}
