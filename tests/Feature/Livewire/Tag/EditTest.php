<?php

namespace Tests\Feature\Livewire\Tag;

use App\Livewire\Tag\Edit;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class EditTest extends TestCase
{
    use RefreshDatabase;

    private Tag $tag;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->blogger()->create());
        $this->tag = Tag::factory()->withMedia()->create([
            'name' => 'Original Name',
            'body' => 'Original body.',
        ]);
    }

    public function test_it_preloads_the_tags_current_values(): void
    {
        Livewire::test(Edit::class, ['tag' => $this->tag])
            ->assertOk()
            ->assertSet('name', 'Original Name')
            ->assertSet('body', 'Original body.');
    }

    public function test_a_plain_user_cannot_open_it(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Edit::class, ['tag' => $this->tag])->assertForbidden();
    }

    /** Update */
    public function test_a_blogger_can_update_a_tag(): void
    {
        Livewire::test(Edit::class, ['tag' => $this->tag])
            ->set('name', 'Revised Name')
            ->set('body', 'Revised body.')
            ->call('updateTag')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tags', [
            'id' => $this->tag->id,
            'name' => 'Revised Name',
            'slug' => 'revised-name',
            'body' => 'Revised body.',
        ]);
    }

    public function test_updating_without_a_new_image_keeps_the_existing_one(): void
    {
        $originalMediaId = $this->tag->media->id;

        Livewire::test(Edit::class, ['tag' => $this->tag])
            ->set('name', 'Revised Name')
            ->call('updateTag')
            ->assertHasNoErrors();

        $this->assertSame($originalMediaId, $this->tag->fresh()->media->id);
    }

    public function test_uploading_a_new_image_replaces_the_old_one(): void
    {
        $originalMediaId = $this->tag->media->id;

        Livewire::test(Edit::class, ['tag' => $this->tag])
            ->set('media', UploadedFile::fake()->image('new-banner.webp'))
            ->call('updateTag')
            ->assertHasNoErrors();

        $this->assertNotSame($originalMediaId, $this->tag->fresh()->media->id);
        $this->assertDatabaseMissing('media', ['id' => $originalMediaId]);
    }

    public function test_it_announces_success_and_returns_to_the_list(): void
    {
        Livewire::test(Edit::class, ['tag' => $this->tag])
            ->call('updateTag')
            ->assertDispatched('swal', [
                'title' => config('messages.tag.update'),
                'icon' => 'success',
                'iconColor' => 'green',
            ])
            ->assertRedirect(route('tags.index'));
    }

    /** Validation */
    public function test_the_name_and_body_are_required(): void
    {
        Livewire::test(Edit::class, ['tag' => $this->tag])
            ->set('name', '')
            ->set('body', '')
            ->call('updateTag')
            ->assertHasErrors(['name' => 'required', 'body' => 'required']);

        $this->assertSame('Original Name', $this->tag->fresh()->name);
    }

    public function test_the_name_must_be_unique_across_other_tags(): void
    {
        Tag::factory()->create(['name' => 'Taken Name']);

        Livewire::test(Edit::class, ['tag' => $this->tag])
            ->set('name', 'Taken Name')
            ->call('updateTag')
            ->assertHasErrors(['name' => 'unique']);
    }

    public function test_a_tag_may_keep_its_own_name(): void
    {
        Livewire::test(Edit::class, ['tag' => $this->tag])
            ->set('name', 'Original Name')
            ->call('updateTag')
            ->assertHasNoErrors();
    }

    public function test_a_replacement_image_must_be_a_webp(): void
    {
        Livewire::test(Edit::class, ['tag' => $this->tag])
            ->set('media', UploadedFile::fake()->image('banner.png'))
            ->call('updateTag')
            ->assertHasErrors(['media' => 'mimes']);
    }
}
