<?php

namespace App\Services;

use App\Models\Post;

final class PostService
{
    public function __construct(
        protected Post $post,
        protected SectionService $sectionService,
        protected MediaService $mediaService
    ) {}

    public function store(array $validated)
    {
        $post = $this->post->create([
            'topic_id' => $validated['topic_id'],
            'heading' => $validated['heading'],
            'body' => $validated['body'],
            'is_publish' => $validated['is_publish'],
        ]);

        return $post;
    }

    public function update($post, array $validated)
    {
        $post->update([
            'topic_id' => $validated['topic_id'],
            'heading' => $validated['heading'],
            'body' => $validated['body'],
            'is_publish' => $validated['is_publish'],
        ]);
    }

    public function destroy($post)
    {
        // delete related media
        $media = $post->media;
        $this->mediaService->destroy($media);

        // delete its sections
        $sections = $post->sections;
        foreach ($sections as $section) {
            $this->sectionService->destroy($section);
        }

        $post->tags()->detach();

        $post->delete();
    }

    public function attachTags($post, $selectedTags)
    {
        if ($selectedTags != null) {
            $post->tags()->attach($selectedTags);
        }
    }

    public function toggleIsFeature($post)
    {
        $post->update([
            'is_feature' => ! $post->is_feature,
        ]);
    }
}
