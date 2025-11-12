<?php

namespace App\Livewire\Video;

use App\Models\Video;
use App\Services\AlertService;
use App\Services\VideoService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Edit extends Component
{
    public $title;
    public $description;
    public $youtube_url;
    public $is_publish = false;

    public Video $video;

    protected $videoService;
    protected $alertService;

    public function boot(VideoService $videoService, AlertService $alertService)
    {
        $this->videoService = $videoService;
        $this->alertService = $alertService;
    }

    public function mount(Video $video)
    {
        $this->video = $video;
        $this->title = $video->title;
        $this->description = $video->description;
        $this->youtube_url = $video->youtube_url;
        $this->is_publish = $video->is_publish;
    }

    protected function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'youtube_url' => ['required', 'url'],
            'is_publish' => ['boolean'],
        ];
    }

    public function updateVideo()
    {
        $validated = $this->validate();

        $youtube_id = $this->extractYoutubeId($validated['youtube_url']);

        if (!$youtube_id) {
            $this->addError('youtube_url', 'Invalid YouTube URL');
            return;
        }

        DB::beginTransaction();

        try {
            $this->videoService->update($this->video, [
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'youtube_url' => $validated['youtube_url'],
                'youtube_id' => $youtube_id,
                'is_publish' => $validated['is_publish'] ?? false,
            ]);

            DB::commit();

            $this->alertService->alert($this, 'Video updated successfully', 'success');

            return $this->redirectRoute('blogger.videos.index', navigate: true);
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->alertService->alert($this, config('messages.common.error'), 'error');
        }
    }

    private function extractYoutubeId($url)
    {
        preg_match(
            '/(?:v=|\/)([0-9A-Za-z_-]{11}).*/',
            $url,
            $matches
        );
        return $matches[1] ?? null;
    }

    public function render()
    {
        return view('livewire.video.edit');
    }
}

