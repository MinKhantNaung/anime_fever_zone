<?php

namespace App\Livewire\Tag;

use App\Models\Tag;
use App\Services\AlertService;
use App\Services\MediaService;
use App\Services\TagService;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;
use LivewireUI\Modal\ModalComponent;

class Edit extends ModalComponent
{
    use WithFileUploads;

    public $media;

    public $name;

    public $body;

    public Tag $tag;

    protected $tagService;

    protected $mediaService;

    protected $alertService;

    public function boot(TagService $tagService, MediaService $mediaService, AlertService $alertService)
    {
        $this->authorize('update', $this->tag);

        $this->tagService = $tagService;
        $this->mediaService = $mediaService;
        $this->alertService = $alertService;
    }

    public function mount()
    {
        $this->name = $this->tag->name;
        $this->body = $this->tag->body;
    }

    public function updateTag()
    {
        $this->authorize('update', $this->tag);

        // validate
        $validated = $this->validateRequests();

        DB::beginTransaction();
        try {
            $this->tagService->update($this->tag, $validated);

            if ($this->media) {
                // delete previous media
                $media = $this->tag->media;

                $this->mediaService->destroy($media);

                // add updated media
                $this->mediaService->store(Tag::class, $this->tag, $this->media, 'image');
            }

            DB::commit();

            $this->alertService->alert($this, config('messages.tag.update'), 'success');

            return $this->redirectRoute('tags.index', navigate: true);
        } catch (\Throwable $e) {
            DB::rollback();

            $this->alertService->alert($this, config('messages.common.error'), 'error');
        }
    }

    protected function validateRequests()
    {
        return $this->validate([
            'media' => ['nullable', 'file', 'mimes:webp', 'max:5120'],
            'name' => ['required', 'string', 'max:255', 'unique:tags,name,' . $this->tag->id],
            'body' => ['required', 'string'],
        ]);
    }

    public function render()
    {
        return view('livewire.tag.edit');
    }
}
