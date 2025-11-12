<?php

namespace App\Livewire\Video;

use App\Models\Video;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Title('Videos - Anime Fever Zone')]
    public function render()
    {
        $videos = Video::where('is_publish', true)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('livewire.video.index', [
            'videos' => $videos,
        ]);
    }
}

