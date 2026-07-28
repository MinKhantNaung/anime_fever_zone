<?php

namespace Tests\Feature\Livewire\Video;

use App\Livewire\Video\Index;
use App\Models\User;
use Database\Factories\VideoFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class IndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->blogger()->create());
    }

    public function test_it_renders_for_a_blogger(): void
    {
        Livewire::test(Index::class)->assertOk();
    }

    public function test_it_lists_videos_whether_published_or_not(): void
    {
        VideoFactory::new()->published()->create(['title' => 'A Published Video']);
        VideoFactory::new()->create(['title' => 'A Draft Video']);

        Livewire::test(Index::class)
            ->assertOk()
            ->assertSee('A Published Video')
            ->assertSee('A Draft Video');
    }

    public function test_it_paginates_ten_videos_at_a_time(): void
    {
        VideoFactory::new()->count(12)->create();

        $videos = Livewire::test(Index::class)->viewData('videos');

        $this->assertCount(10, $videos->items());
        $this->assertSame(12, $videos->total());
    }

    public function test_it_shows_the_newest_videos_first(): void
    {
        $first = VideoFactory::new()->create();
        $second = VideoFactory::new()->create();

        $videos = Livewire::test(Index::class)->viewData('videos');

        $this->assertSame([$second->id, $first->id], $videos->pluck('id')->all());
    }

    public function test_a_plain_user_cannot_open_it(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)->assertForbidden();
    }

    /** Deleting */
    public function test_a_blogger_can_delete_a_video(): void
    {
        $video = VideoFactory::new()->create();

        Livewire::test(Index::class)
            ->call('deleteVideo', $video)
            ->assertDispatched('swal', [
                'title' => 'Video deleted successfully',
                'icon' => 'success',
                'iconColor' => 'green',
            ])
            ->assertDispatched('video-reload');

        $this->assertDatabaseMissing('videos', ['id' => $video->id]);
    }

    /** Publishing */
    public function test_a_blogger_can_publish_a_video(): void
    {
        $video = VideoFactory::new()->create();

        Livewire::test(Index::class)
            ->call('togglePublish', $video)
            ->assertDispatched('swal', [
                'title' => 'Video status updated successfully',
                'icon' => 'success',
                'iconColor' => 'green',
            ])
            ->assertDispatched('video-reload');

        $this->assertTrue($video->fresh()->is_publish);
    }

    public function test_toggling_twice_unpublishes_a_video(): void
    {
        $video = VideoFactory::new()->published()->create();

        Livewire::test(Index::class)->call('togglePublish', $video);

        $this->assertFalse($video->fresh()->is_publish);
    }

    public function test_toggling_publish_leaves_the_slug_alone(): void
    {
        $video = VideoFactory::new()->create(['title' => 'Stable Title']);

        Livewire::test(Index::class)->call('togglePublish', $video);

        $this->assertSame('stable-title', $video->fresh()->slug);
    }
}
