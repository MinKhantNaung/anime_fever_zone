<?php

namespace App\Livewire;

use App\Models\Post;
use Livewire\Component;
use Livewire\WithPagination;

class Topic extends Component
{
    use WithPagination;

    public $slug;

    public $featuredPosts;

    public function mount()
    {
        $this->featuredPosts = Post::with('media')
            ->select('id', 'heading', 'slug')
            ->orderBy('updated_at', 'desc')
            ->where('is_publish', true)
            ->where('is_feature', true)
            ->take(5)
            ->get();
    }

    public function render()
    {
        $posts = Post::with('media', 'topic', 'tags')
            ->select('id', 'topic_id', 'heading', 'slug', 'body', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->whereHas('topic', function ($query): void {
                $query->where('slug', $this->slug);
            })
            ->where('is_publish', true)
            ->simplePaginate(10);

        return view('livewire.topic', [
            'posts' => $posts,
        ])->title('Anime Fever Zone-' . ucfirst($this->slug));
    }
}
