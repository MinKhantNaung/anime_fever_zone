<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\Tag;
use App\Models\Video;
use Livewire\Component;
use Livewire\WithPagination;

class TagShow extends Component
{
    use WithPagination;

    public $slug;

    public $tag;

    public $featuredPosts;
    public $videos;

    public function mount()
    {
        $this->tag = Tag::with('media')
            ->select('id', 'name', 'body')
            ->where('slug', $this->slug)
            ->first();

        $this->featuredPosts = Post::with('media')
            ->select('id', 'heading', 'slug')
            ->orderBy('updated_at', 'desc')
            ->where('is_publish', true)
            ->where('is_feature', true)
            ->take(5)
            ->get();

        $this->videos = Video::where('is_publish', true)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }

    public function render()
    {
        $posts = Post::with('media', 'topic', 'tags')
            ->select('id', 'topic_id', 'heading', 'slug', 'body', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->whereHas('tags', function ($query): void {
                $query->where('slug', $this->slug);
            })
            ->where('is_publish', true)
            ->simplePaginate(10);

        return view('livewire.tag-show', [
            'posts' => $posts,
        ])->title(ucfirst($this->slug) . ' | Anime Fever Zone');
    }
}
