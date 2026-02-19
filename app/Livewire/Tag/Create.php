<?php

namespace App\Livewire\Tag;

use App\Models\Tag;
use App\Services\AlertService;
use App\Services\MediaService;
use App\Services\TagService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public $media;

    public $name;

    public $body = '';

    protected TagService $tagService;

    protected MediaService $mediaService;

    protected AlertService $alertService;

    public function boot(TagService $tagService, MediaService $mediaService, AlertService $alertService): void
    {
        $this->authorize('create', Tag::class);

        $this->tagService = $tagService;
        $this->mediaService = $mediaService;
        $this->alertService = $alertService;
    }

    public function createTag()
    {
        $this->authorize('create', Tag::class);

        $validated = $this->validateRequests();

        DB::beginTransaction();

        try {
            $tag = $this->tagService->store($validated);

            $this->mediaService->store(Tag::class, $tag, $this->media, 'image');

            DB::commit();

            $this->alertService->alert($this, config('messages.tag.create'), 'success');

            $this->reset();
            $this->dispatch('tag-reload');

            return $this->redirectRoute('tags.index', navigate: true);
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->alertService->alert($this, config('messages.common.error'), 'error');
        }
    }

    protected function validateRequests(): array
    {
        return $this->validate([
            'media' => 'required|file|image|mimes:webp|max:5120',
            'name' => 'required|string|max:225|unique:tags,name',
            'body' => 'required|string',
        ]);
    }

    public function render()
    {
        return view('livewire.tags.create');
    }
}
