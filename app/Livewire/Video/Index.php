<?php

namespace App\Livewire\Video;

use App\Models\Video;
use App\Services\AlertService;
use App\Services\VideoService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $videoService;

    protected $alertService;

    public function boot(VideoService $videoService, AlertService $alertService)
    {
        $this->videoService = $videoService;
        $this->alertService = $alertService;
    }

    public function deleteVideo(Video $video)
    {
        DB::beginTransaction();

        try {
            $this->videoService->destroy($video);

            DB::commit();

            $this->alertService->alert($this, 'Video deleted successfully', 'success');

            $this->dispatch('video-reload');
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->alertService->alert($this, config('messages.common.error'), 'error');
        }
    }

    public function togglePublish(Video $video)
    {
        DB::beginTransaction();

        try {
            $this->videoService->togglePublish($video);

            DB::commit();

            $this->alertService->alert($this, 'Video status updated successfully', 'success');

            $this->dispatch('video-reload');
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->alertService->alert($this, config('messages.common.error'), 'error');
        }
    }

    #[On('video-reload')]
    public function render()
    {
        $videos = Video::orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.video.index', [
            'videos' => $videos,
        ]);
    }
}
