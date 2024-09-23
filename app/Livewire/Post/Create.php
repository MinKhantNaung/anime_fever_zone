<?php

namespace App\Livewire\Post;

use App\Models\Post;
use App\Models\Tag;
use App\Models\Topic;
use App\Services\AlertService;
use App\Services\FileService;
use App\Services\MediaService;
use App\Services\PostService;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use LivewireUI\Modal\ModalComponent;

class Create extends ModalComponent
{
    use WithFileUploads;

    public $media;
    public $topic_id;
    public $heading;
    public $body;
    public $is_publish = false;
    public $selectedTags = null;

    // Models, Services
    protected $topic;
    protected $tag;
    protected $postService;
    protected $mediaService;
    protected $fileService;
    protected $alertService;

    public function boot(
        Topic $topic,
        Tag $tag,
        PostService $postService,
        MediaService $mediaService,
        FileService $fileService,
        AlertService $alertService
    ) {
        $this->topic = $topic;
        $this->tag = $tag;
        $this->postService = $postService;
        $this->mediaService = $mediaService;
        $this->fileService = $fileService;
        $this->alertService = $alertService;
    }

    public static function modalMaxWidth(): string
    {
        return '5xl';
    }

    public function createPost()
    {
        $validated = $this->validateRequests();

        DB::beginTransaction();
        try {
            $post = $this->postService->store($validated);

            $this->postService->attachTags($post, $this->selectedTags);

            $url = $this->fileService->storeFile($this->media);

            $this->mediaService->store(Post::class, $post, $url, 'image');

            DB::commit();

            $this->reset();
            $this->dispatch('close');
            $this->dispatch('post-event');

            $this->alertService->alert($this, config('messages.post.create'), 'success');
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->alertService->alert($this, config('messages.common.error'), 'error');
        }
    }

    protected function validateRequests()
    {
        return $this->validate([
            'media' => 'required|file|mimes:png,jpg,jpeg,svg,webp|max:5120',
            'topic_id' => 'required|integer',
            'heading' => 'required|string|max:255|unique:posts,heading',
            'body' => 'required|string',
            'is_publish' => 'required|boolean',
            'selectedTags' => 'nullable|array'
        ]);
    }

    public function render()
    {
        $topics = $this->topic->getAllByName();
        $tags = $this->tag->getAllByName();

        return view('livewire.post.create', [
            'topics' => $topics,
            'tags' => $tags
        ]);
    }
}
