<?php

namespace App\Services;

use App\Models\Post;

final class PostService
{
    public function __construct(protected Post $post) {}

    public function store(array $validated)
    {
        $post = $this->post->create([
            'topic_id' => $validated['topic_id'],
            'heading' => $validated['heading'],
            'body' => $validated['body'],
            'is_publish' => $validated['is_publish']
        ]);

        return $post;
    }

    public function update($post, array $validated)
    {
        $post->update([
            'topic_id' => $validated['topic_id'],
            'heading' => $validated['heading'],
            'body' => $validated['body'],
            'is_publish' => $validated['is_publish']
        ]);
    }

    public function attachTags($postModel, $selectedTags)
    {
        if ($selectedTags != null) {
            $postModel->tags()->attach($selectedTags);
        }
    }
}
