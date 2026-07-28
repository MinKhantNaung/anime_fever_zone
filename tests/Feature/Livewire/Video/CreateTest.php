<?php

namespace Tests\Feature\Livewire\Video;

use App\Livewire\Video\Create;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->blogger()->create());
    }

    public function test_it_renders_for_a_blogger(): void
    {
        Livewire::test(Create::class)
            ->assertOk()
            ->assertSet('is_publish', false)
            ->assertSet('is_trending', false);
    }

    public function test_a_plain_user_cannot_open_it(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Create::class)->assertForbidden();
    }

    /** Store */
    public function test_a_blogger_can_create_a_video(): void
    {
        Livewire::test(Create::class)
            ->set('title', 'Top 10 Fight Scenes')
            ->set('description', 'Our picks.')
            ->set('youtube_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->set('is_publish', true)
            ->set('is_trending', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('videos', [
            'title' => 'Top 10 Fight Scenes',
            'slug' => 'top-10-fight-scenes',
            'description' => 'Our picks.',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_id' => 'dQw4w9WgXcQ',
            'is_publish' => true,
            'is_trending' => true,
        ]);
    }

    public function test_it_extracts_the_id_from_a_short_youtube_url(): void
    {
        Livewire::test(Create::class)
            ->set('title', 'A Short Link')
            ->set('youtube_url', 'https://youtu.be/dQw4w9WgXcQ')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('dQw4w9WgXcQ', Video::first()->youtube_id);
    }

    public function test_a_video_defaults_to_unpublished_and_not_trending(): void
    {
        Livewire::test(Create::class)
            ->set('title', 'A Draft Video')
            ->set('youtube_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->call('save')
            ->assertHasNoErrors();

        $video = Video::first();

        $this->assertFalse($video->is_publish);
        $this->assertFalse($video->is_trending);
    }

    public function test_it_announces_success_and_returns_to_the_list(): void
    {
        Livewire::test(Create::class)
            ->set('title', 'Top 10 Fight Scenes')
            ->set('youtube_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->call('save')
            ->assertDispatched('swal', [
                'title' => 'Video created successfully',
                'icon' => 'success',
                'iconColor' => 'green',
            ])
            ->assertRedirect(route('blogger.videos.index'));
    }

    /** Validation */
    public function test_the_title_and_url_are_required(): void
    {
        Livewire::test(Create::class)
            ->call('save')
            ->assertHasErrors(['title' => 'required', 'youtube_url' => 'required']);

        $this->assertDatabaseCount('videos', 0);
    }

    public function test_the_title_is_capped_at_255_characters(): void
    {
        Livewire::test(Create::class)
            ->set('title', str_repeat('a', 256))
            ->set('youtube_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->call('save')
            ->assertHasErrors(['title' => 'max']);
    }

    public function test_the_url_must_be_a_url(): void
    {
        Livewire::test(Create::class)
            ->set('title', 'A Video')
            ->set('youtube_url', 'not-a-url')
            ->call('save')
            ->assertHasErrors(['youtube_url' => 'url']);
    }

    public function test_a_url_without_a_youtube_id_is_rejected(): void
    {
        Livewire::test(Create::class)
            ->set('title', 'A Video')
            ->set('youtube_url', 'https://example.com/short')
            ->call('save')
            ->assertHasErrors('youtube_url');

        $this->assertDatabaseCount('videos', 0);
    }

    public function test_the_description_is_optional(): void
    {
        Livewire::test(Create::class)
            ->set('title', 'A Video')
            ->set('description', '')
            ->set('youtube_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('videos', 1);
    }
}
