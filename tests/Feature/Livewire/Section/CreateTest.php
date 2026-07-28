<?php

namespace Tests\Feature\Livewire\Section;

use App\Livewire\Section\Create;
use App\Models\Post;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class CreateTest extends TestCase
{
    use RefreshDatabase;

    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->blogger()->create());
        $this->post = Post::factory()->withMedia()->create();
    }

    public function test_it_renders_for_a_blogger(): void
    {
        Livewire::test(Create::class, ['post' => $this->post])
            ->assertOk()
            ->assertSet('body', '');
    }

    public function test_a_plain_user_cannot_open_it(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Create::class, ['post' => $this->post])->assertForbidden();
    }

    /** Store */
    public function test_a_blogger_can_add_a_section(): void
    {
        Livewire::test(Create::class, ['post' => $this->post])
            ->set('heading', 'The Marineford Arc')
            ->set('body', 'Where it all changed.')
            ->call('addSection')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sections', [
            'post_id' => $this->post->id,
            'heading' => 'The Marineford Arc',
            'body' => 'Where it all changed.',
        ]);
    }

    public function test_a_section_can_be_added_without_a_heading(): void
    {
        Livewire::test(Create::class, ['post' => $this->post])
            ->set('body', 'Just a paragraph.')
            ->call('addSection')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sections', [
            'post_id' => $this->post->id,
            'heading' => null,
        ]);
    }

    public function test_a_section_can_be_added_with_images(): void
    {
        Livewire::test(Create::class, ['post' => $this->post])
            ->set('media', [
                UploadedFile::fake()->image('one.webp'),
                UploadedFile::fake()->image('two.webp'),
            ])
            ->set('body', 'With two images.')
            ->call('addSection')
            ->assertHasNoErrors();

        $section = Section::first();

        $this->assertCount(2, $section->media);
        $this->assertSame(['image', 'image'], $section->media->pluck('mime')->all());
        $this->assertCount(2, Storage::disk('public')->files('media'));
    }

    public function test_an_uploaded_video_is_recorded_with_the_video_mime(): void
    {
        Livewire::test(Create::class, ['post' => $this->post])
            ->set('media', [UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4')])
            ->set('body', 'With a clip.')
            ->call('addSection')
            ->assertHasNoErrors();

        $this->assertSame('video', Section::first()->media->first()->mime);
    }

    public function test_it_announces_success_and_returns_to_the_section_list(): void
    {
        Livewire::test(Create::class, ['post' => $this->post])
            ->set('body', 'A paragraph.')
            ->call('addSection')
            ->assertDispatched('swal', [
                'title' => config('messages.section.create'),
                'icon' => 'success',
                'iconColor' => 'green',
            ])
            ->assertDispatched('section-reload')
            ->assertRedirect(route('sections.index', $this->post->id));
    }

    /** Validation */
    public function test_the_body_is_required(): void
    {
        Livewire::test(Create::class, ['post' => $this->post])
            ->set('body', '')
            ->call('addSection')
            ->assertHasErrors(['body' => 'required']);

        $this->assertDatabaseCount('sections', 0);
    }

    public function test_the_heading_is_capped_at_255_characters(): void
    {
        Livewire::test(Create::class, ['post' => $this->post])
            ->set('heading', str_repeat('a', 256))
            ->set('body', 'A paragraph.')
            ->call('addSection')
            ->assertHasErrors(['heading' => 'max']);
    }

    public function test_uploads_must_be_webp_images_or_mp4_videos(): void
    {
        Livewire::test(Create::class, ['post' => $this->post])
            ->set('media', [UploadedFile::fake()->image('photo.jpg')])
            ->set('body', 'A paragraph.')
            ->call('addSection')
            ->assertHasErrors(['media.0' => 'mimetypes']);

        $this->assertDatabaseCount('sections', 0);
    }
}
