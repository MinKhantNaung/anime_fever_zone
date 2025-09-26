<?php

namespace App\Livewire\Section;

use App\Models\Post;
use App\Models\Section;
use App\Services\AlertService;
use App\Services\FileService;
use App\Services\MediaService;
use App\Services\SectionService;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;
use LivewireUI\Modal\ModalComponent;

class Create extends ModalComponent
{
    use WithFileUploads;

    public $media = [];
    public $heading;
    public $body;

    public Post $post;

    protected $sectionService;
    protected $mediaService;
    protected $alertService;
    protected $fileService;

    public function boot(SectionService $sectionService, MediaService $mediaService, AlertService $alertService, FileService $fileService)
    {
        $this->sectionService = $sectionService;
        $this->mediaService = $mediaService;
        $this->alertService = $alertService;
        $this->fileService = $fileService;
    }

    public function mount()
    {
        $this->body = '';
    }

    public function addSection()
    {
        $validated = $this->validateRequests();

        DB::beginTransaction();

        try {
            $section = $this->sectionService->store($this->post->id, $validated);

            foreach ($this->media as $media) {
                // get mime type
                $mime = $this->fileService->getMime($media);

                $this->mediaService->store(Section::class, $section, $media, $mime);
            }

            DB::commit();

            $this->alertService->alert($this, config('messages.section.create'), 'success');

            $this->dispatch('section-reload');

            return $this->redirectRoute('sections.index', $this->post->id, navigate: true);
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->alertService->alert($this, config('messages.common.error'), 'error');
        }
    }

    protected function validateRequests()
    {
        return $this->validate([
            'media' => ['nullable', 'array'],
            'media.*' => ['file', 'mimes:webp,mp4', 'max:102400'],
            'heading' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);
    }

    public function render()
    {
        return view('livewire.section.create');
    }
}
