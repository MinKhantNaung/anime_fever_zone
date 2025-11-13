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
        // Get related videos (excluding current video)
        $relatedVideos = Video::where('is_publish', true)
            ->where('id', '!=', $this->video->id)
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        return view('livewire.video.show', [
            'relatedVideos' => $relatedVideos,
        ])
            ->title(ucwords(str_replace('-', ' ', $this->video->title)) . ' - Anime Fever Zone');
    }
}

