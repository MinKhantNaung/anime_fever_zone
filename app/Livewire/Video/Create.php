<?php

namespace App\Livewire\Video;

use App\Models\Video;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class Create extends Component
{
    public $title;
    public $youtube_url;
    public ?Video $video;

    public function mount()
    {
        $this->video = Video::find(1);
    }

    protected $rules = [
        'title' => 'required|string|max:255',
        'youtube_url' => 'required|url',
    ];

    public function save()
    {
        $this->validate();

        $youtube_id = $this->extractYoutubeId($this->youtube_url);

        Video::create([
            'title' => $this->title,
            'youtube_url' => $this->youtube_url,
            'youtube_id' => $youtube_id,
        ]);

        session()->flash('success', 'Video added successfully!');
        $this->reset(['title', 'youtube_url']);
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
