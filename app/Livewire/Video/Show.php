<?php

namespace App\Livewire\Video;

use App\Models\Video;
use Livewire\Component;

class Show extends Component
{
    public Video $video;

    public function mount($slug)
    {
        $this->video = Video::where('is_publish', true)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function render()
    {
        // Get trending videos for sidebar (excluding current video)
        $trendingVideos = Video::where('is_publish', true)
            ->where('is_trending', true)
            ->where('id', '!=', $this->video->id)
            ->orderBy('updated_at', 'desc')
            ->take(4)
            ->get();

        // Get recommended videos to show below the video (excluding current video)
        $recommendedVideos = Video::where('is_publish', true)
            ->where('id', '!=', $this->video->id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('livewire.video.show', [
            'trendingVideos' => $trendingVideos,
            'recommendedVideos' => $recommendedVideos,
        ])
            ->title(ucwords(str_replace('-', ' ', $this->video->title)) . ' - Anime Fever Zone');
    }
}
