<?php

namespace App\Livewire\Video;

use App\Models\Video;
use App\Services\AlertService;
use App\Services\VideoService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Create extends Component
{
    public $title;

    public $description = '';

    public $youtube_url;

    public $is_publish = false;

    public $is_trending = false;

    protected $videoService;

    protected $alertService;

    public function boot(VideoService $videoService, AlertService $alertService)
    {
        $this->videoService = $videoService;
        $this->alertService = $alertService;
    }

    protected function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'youtube_url' => ['required', 'url'],
            'is_publish' => ['boolean'],
            'is_trending' => ['boolean'],
        ];
    }

    public function save()
    {
        $this->authorize('create', Video::class);

        $validated = $this->validate();

        $youtube_id = $this->extractYoutubeId($validated['youtube_url']);

        if (! $youtube_id) {
            $this->addError('youtube_url', 'Invalid YouTube URL');

            return;
        }

        DB::beginTransaction();

        try {
            $this->videoService->store([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'youtube_url' => $validated['youtube_url'],
                'youtube_id' => $youtube_id,
                'is_publish' => $validated['is_publish'] ?? false,
                'is_trending' => $validated['is_trending'] ?? false,
            ]);

            DB::commit();

            $this->alertService->alert($this, 'Video created successfully', 'success');

            $this->reset(['title', 'description', 'youtube_url', 'is_publish', 'is_trending']);

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
        return view('livewire.video.create');
    }
}
