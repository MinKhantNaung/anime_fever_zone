<?php

namespace Tests\Feature\Livewire\Video;

use App\Livewire\Video\Show;
use Database\Factories\VideoFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_a_published_video(): void
    {
        $video = VideoFactory::new()->published()->create(['title' => 'Chainsaw Man Breakdown']);

        Livewire::test(Show::class, ['slug' => $video->slug])
            ->assertOk()
            ->assertSet('video.id', $video->id)
            ->assertSee('Chainsaw Man Breakdown');
    }

    public function test_it_aborts_for_an_unpublished_video(): void
    {
        $video = VideoFactory::new()->create();

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(Show::class, ['slug' => $video->slug]);
    }

    public function test_it_aborts_for_an_unknown_slug(): void
    {
        $this->expectException(ModelNotFoundException::class);

        Livewire::test(Show::class, ['slug' => 'no-such-video']);
    }

    /** Sidebar */
    public function test_it_lists_trending_videos_excluding_the_current_one(): void
    {
        $video = VideoFactory::new()->published()->trending()->create();
        VideoFactory::new()->count(2)->published()->trending()->create();

        $trending = Livewire::test(Show::class, ['slug' => $video->slug])->viewData('trendingVideos');

        $this->assertCount(2, $trending);
        $this->assertNotContains($video->id, $trending->pluck('id')->all());
    }

    public function test_the_trending_sidebar_skips_unpublished_and_untrending_videos(): void
    {
        $video = VideoFactory::new()->published()->create();
        VideoFactory::new()->published()->create();
        VideoFactory::new()->trending()->create();

        $trending = Livewire::test(Show::class, ['slug' => $video->slug])->viewData('trendingVideos');

        $this->assertCount(0, $trending);
    }

    public function test_the_trending_sidebar_holds_at_most_four_videos(): void
    {
        $video = VideoFactory::new()->published()->trending()->create();
        VideoFactory::new()->count(6)->published()->trending()->create();

        $trending = Livewire::test(Show::class, ['slug' => $video->slug])->viewData('trendingVideos');

        $this->assertCount(4, $trending);
    }

    /** Recommendations */
    public function test_it_recommends_other_published_videos(): void
    {
        $video = VideoFactory::new()->published()->create();
        VideoFactory::new()->count(3)->published()->create();
        VideoFactory::new()->create();

        $recommended = Livewire::test(Show::class, ['slug' => $video->slug])->viewData('recommendedVideos');

        $this->assertCount(3, $recommended);
        $this->assertNotContains($video->id, $recommended->pluck('id')->all());
    }

    public function test_it_recommends_at_most_four_videos(): void
    {
        $video = VideoFactory::new()->published()->create();
        VideoFactory::new()->count(9)->published()->create();

        $recommended = Livewire::test(Show::class, ['slug' => $video->slug])->viewData('recommendedVideos');

        $this->assertCount(4, $recommended);
    }

    /** Route */
    public function test_the_video_page_is_publicly_reachable(): void
    {
        $video = VideoFactory::new()->published()->create();

        $this->get(route('video.show', $video->slug))->assertOk();
    }
}
