<?php

namespace Tests\Feature\Livewire\Video;

use App\Livewire\Video\PublicIndex;
use Database\Factories\VideoFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class PublicIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_with_no_videos(): void
    {
        Livewire::test(PublicIndex::class)->assertOk();
    }

    public function test_it_lists_published_videos(): void
    {
        $video = VideoFactory::new()->published()->create(['title' => 'Best Openings Of 2024']);

        Livewire::test(PublicIndex::class)
            ->assertOk()
            ->assertSee('Best Openings Of 2024');

        $this->assertSame(
            [$video->id],
            Livewire::test(PublicIndex::class)->viewData('videos')->pluck('id')->all()
        );
    }

    public function test_it_hides_unpublished_videos(): void
    {
        VideoFactory::new()->create(['title' => 'A Hidden Draft']);

        Livewire::test(PublicIndex::class)
            ->assertOk()
            ->assertDontSee('A Hidden Draft');
    }

    public function test_it_paginates_twelve_videos_at_a_time(): void
    {
        VideoFactory::new()->count(14)->published()->create();

        $videos = Livewire::test(PublicIndex::class)->viewData('videos');

        $this->assertCount(12, $videos->items());
        $this->assertTrue($videos->hasMorePages());
    }

    public function test_it_shows_the_newest_videos_first(): void
    {
        $older = VideoFactory::new()->published()->create(['created_at' => now()->subWeek()]);
        $newer = VideoFactory::new()->published()->create(['created_at' => now()]);

        $videos = Livewire::test(PublicIndex::class)->viewData('videos');

        $this->assertSame([$newer->id, $older->id], $videos->pluck('id')->all());
    }

    /** Route */
    public function test_the_videos_page_is_publicly_reachable(): void
    {
        $this->get(route('videos.index'))->assertOk();
    }
}
