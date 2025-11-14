<?php

namespace App\Livewire\Video;

use App\Models\Video;
use Livewire\Attributes\Title;
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
        // Get related videos for sidebar (excluding current video)
        $relatedVideos = Video::where('is_publish', true)
            ->where('id', '!=', $this->video->id)
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        // Get recommended videos to show below the video (excluding current video)
        $recommendedVideos = Video::where('is_publish', true)
            ->where('id', '!=', $this->video->id)
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();

        return view('livewire.video.show', [
            'relatedVideos' => $relatedVideos,
            'recommendedVideos' => $recommendedVideos,
        ])
            ->title(ucwords(str_replace('-', ' ', $this->video->title)) . ' - Anime Fever Zone');
    }
}

