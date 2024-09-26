<?php

namespace App\Services;

use App\Models\Tag;

final class TagService
{
    public function __construct(protected Tag $tag, protected MediaService $mediaService) {}

    public function store(array $validated)
    {
        $tag = $this->tag->create([
            'name' => $validated['name'],
            'body' => $validated['body'],
        ]);

        return $tag;
    }

    public function update(Tag $tag, array $validated)
    {
        $tag = $tag->update([
            'name' => $validated['name'],
            'body' => $validated['body']
        ]);
    }

    public function destroy(Tag $tag)
    {
        $media = $tag->media;

        $this->mediaService->destroy($media);

        // Remove relationships between tag and associated post
        $tag->posts()->detach();

        $tag->delete();
    }
}
