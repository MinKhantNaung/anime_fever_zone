<?php

namespace App\Livewire\Tag;

use App\Models\Tag;
use App\Services\AlertService;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Services\TagService;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    protected $tagService;
    protected $alertService;

    public function boot(TagService $tagService, AlertService $alertService)
    {
        $this->tagService = $tagService;
        $this->alertService = $alertService;
    }

    public function deleteTag(Tag $tag)
    {
        DB::beginTransaction();

        try {
            $this->tagService->destroy($tag);

            DB::commit();

            $this->alertService->alert($this, config('messages.tag.destroy'), 'success');

            $this->dispatch('tag-reload');
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->alertService->alert($this, config('messages.common.error'), 'error');
        }
    }

    #[On('tag-reload')]
    public function render()
    {
        $tags = Tag::with('media')
            ->select('id', 'name', 'slug', 'body')
            ->orderBy('id', 'desc')
            ->paginate(2);

        return view('livewire.tag.index', [
            'tags' => $tags
        ])
        ->title('Admin');
    }
}
