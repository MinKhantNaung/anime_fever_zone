<?php

namespace App\Livewire\Section;

use App\Models\Media;
use App\Models\Section;
use App\Services\AlertService;
use App\Services\FileService;
use App\Services\MediaService;
use App\Services\SectionService;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;
use LivewireUI\Modal\ModalComponent;

class Edit extends ModalComponent
{
    use WithFileUploads;

    public $media = [];
    public $heading;
    public $body;

    public Section $section;

    protected $sectionService;
    protected $mediaService;
    protected $fileService;
    protected $alertService;

    public function boot(
        SectionService $sectionService,
        MediaService $mediaService,
        FileService $fileService,
        AlertService $alertService
    ) {
        $this->sectionService = $sectionService;
        $this->mediaService = $mediaService;
        $this->fileService = $fileService;
        $this->alertService = $alertService;
    }

    public function mount()
    {
        $this->heading = $this->section->heading;
        $this->body = $this->section->body;
    }

    public function updateSection()
    {
        $validated = $this->validateRequests();

        DB::beginTransaction();

        try {
            $section = $this->sectionService->update($this->section, $validated);

            if (count($this->media) > 0) {
                $prev_media = $this->section->media;

                // delete previous media
                foreach ($prev_media as $media) {
                    $this->mediaService->destroy($media);
                }

                foreach ($this->media as $media) {
                    // get mime type
                    $mime = $this->fileService->getMime($media);

                    $this->mediaService->store(Section::class, $section, $media, $mime);
                }
            }

            DB::commit();

            // success toast
            $this->alertService->alert($this, config('messages.section.update'), 'success');

            return $this->redirectRoute('sections.index', ['post' => $this->section->post_id], navigate: true);
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->alertService->alert($this, config('messages.common.error'), 'error');
        }
    }

    protected function validateRequests()
    {
        return $this->validate([
            'media' => ['nullable', 'array'],
            'media.*' => ['file', 'mimes:webp,mp4', 'max:512000'],
            'heading' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);
    }

    public function render()
    {
        return view('livewire.section.edit');
    }
}
