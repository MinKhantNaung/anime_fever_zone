<?php

namespace Tests\Feature\Livewire\Video;

use App\Livewire\Video\Edit;
use App\Models\User;
use App\Models\Video;
use Database\Factories\VideoFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class EditTest extends TestCase
{
    use RefreshDatabase;

    private Video $video;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->blogger()->create());
        $this->video = VideoFactory::new()->create([
            'title' => 'Original Title',
            'description' => 'Original description.',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_id' => 'dQw4w9WgXcQ',
        ]);
    }

    public function test_it_preloads_the_videos_current_values(): void
    {
        Livewire::test(Edit::class, ['video' => $this->video])
            ->assertOk()
            ->assertSet('title', 'Original Title')
            ->assertSet('description', 'Original description.')
            ->assertSet('youtube_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->assertSet('is_publish', false)
            ->assertSet('is_trending', false);
    }

    public function test_a_plain_user_cannot_open_it(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Edit::class, ['video' => $this->video])->assertForbidden();
    }

    /** Update */
    public function test_a_blogger_can_update_a_video(): void
    {
        Livewire::test(Edit::class, ['video' => $this->video])
            ->set('title', 'Revised Title')
            ->set('description', 'Revised description.')
            ->set('is_publish', true)
            ->set('is_trending', true)
            ->call('updateVideo')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('videos', [
            'id' => $this->video->id,
            'title' => 'Revised Title',
            'slug' => 'revised-title',
            'description' => 'Revised description.',
            'is_publish' => true,
            'is_trending' => true,
        ]);
    }

    public function test_changing_the_url_updates_the_youtube_id(): void
    {
        Livewire::test(Edit::class, ['video' => $this->video])
            ->set('youtube_url', 'https://www.youtube.com/watch?v=abcDEF12345')
            ->call('updateVideo')
            ->assertHasNoErrors();

        $this->assertSame('abcDEF12345', $this->video->fresh()->youtube_id);
    }

    public function test_it_announces_success_and_returns_to_the_list(): void
    {
        Livewire::test(Edit::class, ['video' => $this->video])
            ->call('updateVideo')
            ->assertDispatched('swal', [
                'title' => 'Video updated successfully',
                'icon' => 'success',
                'iconColor' => 'green',
            ])
            ->assertRedirect(route('blogger.videos.index'));
    }

    /** Validation */
    public function test_the_title_and_url_are_required(): void
    {
        Livewire::test(Edit::class, ['video' => $this->video])
            ->set('title', '')
            ->set('youtube_url', '')
            ->call('updateVideo')
            ->assertHasErrors(['title' => 'required', 'youtube_url' => 'required']);

        $this->assertSame('Original Title', $this->video->fresh()->title);
    }

    public function test_the_url_must_be_a_url(): void
    {
        Livewire::test(Edit::class, ['video' => $this->video])
            ->set('youtube_url', 'not-a-url')
            ->call('updateVideo')
            ->assertHasErrors(['youtube_url' => 'url']);
    }

    public function test_a_url_without_a_youtube_id_is_rejected(): void
    {
        Livewire::test(Edit::class, ['video' => $this->video])
            ->set('youtube_url', 'https://example.com/short')
            ->call('updateVideo')
            ->assertHasErrors('youtube_url');

        $this->assertSame('dQw4w9WgXcQ', $this->video->fresh()->youtube_id);
    }
}
