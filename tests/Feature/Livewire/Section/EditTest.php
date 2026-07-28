<?php

namespace Tests\Feature\Livewire\Section;

use App\Livewire\Section\Edit;
use App\Models\Post;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class EditTest extends TestCase
{
    use RefreshDatabase;

    private Section $section;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->blogger()->create());
        $this->section = Section::factory()->create([
            'post_id' => Post::factory()->withMedia()->create()->id,
            'heading' => 'Original Heading',
            'body' => 'Original body.',
        ]);
    }

    public function test_it_preloads_the_sections_current_values(): void
    {
        Livewire::test(Edit::class, ['section' => $this->section])
            ->assertOk()
            ->assertSet('heading', 'Original Heading')
            ->assertSet('body', 'Original body.');
    }

    public function test_a_plain_user_cannot_open_it(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Edit::class, ['section' => $this->section])->assertForbidden();
    }

    /** Update */
    public function test_a_blogger_can_update_a_section(): void
    {
        Livewire::test(Edit::class, ['section' => $this->section])
            ->set('heading', 'Revised Heading')
            ->set('body', 'Revised body.')
            ->call('updateSection')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sections', [
            'id' => $this->section->id,
            'heading' => 'Revised Heading',
            'body' => 'Revised body.',
        ]);
    }

    public function test_a_heading_can_be_cleared(): void
    {
        Livewire::test(Edit::class, ['section' => $this->section])
            ->set('heading', null)
            ->call('updateSection')
            ->assertHasNoErrors();

        $this->assertNull($this->section->fresh()->heading);
    }

    public function test_updating_without_new_uploads_keeps_the_existing_media(): void
    {
        $media = $this->section->media()->create(['url' => 'media/kept.webp', 'mime' => 'image']);

        Livewire::test(Edit::class, ['section' => $this->section])
            ->set('body', 'Revised body.')
            ->call('updateSection')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    public function test_new_uploads_replace_all_existing_media(): void
    {
        $old = $this->section->media()->create(['url' => 'media/old.webp', 'mime' => 'image']);

        Livewire::test(Edit::class, ['section' => $this->section])
            ->set('media', [UploadedFile::fake()->image('new.webp')])
            ->call('updateSection')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('media', ['id' => $old->id]);
        $this->assertCount(1, $this->section->fresh()->media);
    }

    public function test_it_announces_success_and_returns_to_the_section_list(): void
    {
        Livewire::test(Edit::class, ['section' => $this->section])
            ->call('updateSection')
            ->assertDispatched('swal', [
                'title' => config('messages.section.update'),
                'icon' => 'success',
                'iconColor' => 'green',
            ])
            ->assertRedirect(route('sections.index', ['post' => $this->section->post_id]));
    }

    /** Validation */
    public function test_the_body_is_required(): void
    {
        Livewire::test(Edit::class, ['section' => $this->section])
            ->set('body', '')
            ->call('updateSection')
            ->assertHasErrors(['body' => 'required']);

        $this->assertSame('Original body.', $this->section->fresh()->body);
    }

    public function test_uploads_must_be_webp_images_or_mp4_videos(): void
    {
        Livewire::test(Edit::class, ['section' => $this->section])
            ->set('media', [UploadedFile::fake()->image('photo.png')])
            ->call('updateSection')
            ->assertHasErrors(['media.0' => 'mimetypes']);
    }
}
