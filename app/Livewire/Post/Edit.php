<?php

namespace App\Livewire\Post;

use App\Models\Post;
use App\Models\Tag;
use App\Models\Topic;
use App\Services\AlertService;
use App\Services\FileService;
use App\Services\MediaService;
use App\Services\PostService;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;
use LivewireUI\Modal\ModalComponent;

class Edit extends ModalComponent
{
    use WithFileUploads;

    public $media;

    public $topic_id;

    public $heading;

    public $body;

    public $is_publish = false;

    public $selectedTags = null;

    public Post $post;

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

    public function mount()
    {
        $this->topic_id = $this->post->topic_id;
        $this->heading = $this->post->heading;
        $this->body = $this->post->body;
        $this->is_publish = $this->post->is_publish;
        $this->selectedTags = $this->post->tags()->pluck('tags.id')->toArray();
    }

    public function updatePost()
    {
        // validate
        $validated = $this->validateRequests();

        DB::beginTransaction();

        try {
            $this->postService->update($this->post, $validated);

            $this->post->tags()->detach();
            $this->postService->attachTags($this->post, $this->selectedTags);

            if ($validated['media']) {
                $this->updateMedia($validated['media']);
            }

            DB::commit();

            $this->reset();
            $this->dispatch('close');
            $this->dispatch('post-event');

            $this->alertService->alert($this, config('messages.post.update'), 'success');
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->alertService->alert($this, config('messages.common.error'), 'error');
        }
    }

    protected function validateRequests()
    {
        return $this->validate([
            'media' => ['nullable', 'file', 'image', 'mimes:webp', 'max:5120'],
            'topic_id' => ['required', 'integer', 'exists:topics,id'],
            'heading' => ['required', 'string', 'max:255', 'unique:posts,heading,' . $this->post->id],
            'body' => ['required', 'string'],
            'is_publish' => ['required', 'boolean'],
            'selectedTags' => ['nullable', 'array'],
            'selectedTags.*' => ['integer', 'exists:tags,id'],
        ]);
    }

    protected function updateMedia($newMedia)
    {
        // delete previous media
        $media = $this->post->media;

        $this->mediaService->destroy($media);

        // add updated media
        $this->mediaService->store(Post::class, $this->post, $newMedia, 'image');
    }

    public function render()
    {
        $topics = $this->topic->getAllByName();
        $tags = $this->tag->getAllByName();

        return view('livewire.post.edit', [
            'topics' => $topics,
            'tags' => $tags,
        ]);
    }
}
