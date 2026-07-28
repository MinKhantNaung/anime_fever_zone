<?php

namespace Tests\Feature\Livewire\Tag;

use App\Livewire\Tag\Create;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

final class CreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->blogger()->create());
    }

    public function test_it_renders_for_a_blogger(): void
    {
        Livewire::test(Create::class)->assertOk();
    }

    public function test_a_plain_user_cannot_open_it(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Create::class)->assertForbidden();
    }

    /** Store */
    public function test_a_blogger_can_create_a_tag(): void
    {
        $this->validForm()->call('createTag')->assertHasNoErrors();

        $this->assertDatabaseHas('tags', [
            'name' => 'Shonen Jump',
            'slug' => 'shonen-jump',
            'body' => 'Everything from Weekly Shonen Jump.',
        ]);
    }

    public function test_creating_a_tag_stores_its_banner_image(): void
    {
        $this->validForm()->call('createTag');

        $tag = Tag::first();

        $this->assertNotNull($tag->media);
        $this->assertSame('image', $tag->media->mime);
        $this->assertCount(1, Storage::disk('public')->files('media'));
    }

    public function test_it_announces_success_and_returns_to_the_list(): void
    {
        $this->validForm()
            ->call('createTag')
            ->assertDispatched('swal', [
                'title' => config('messages.tag.create'),
                'icon' => 'success',
                'iconColor' => 'green',
            ])
            ->assertDispatched('tag-reload')
            ->assertRedirect(route('tags.index'));
    }

    /** Validation */
    public function test_the_required_fields_are_enforced(): void
    {
        Livewire::test(Create::class)
            ->set('body', '')
            ->call('createTag')
            ->assertHasErrors([
                'media' => 'required',
                'name' => 'required',
                'body' => 'required',
            ]);

        $this->assertDatabaseCount('tags', 0);
    }

    public function test_the_name_must_be_unique(): void
    {
        Tag::factory()->create(['name' => 'Shonen Jump']);

        $this->validForm()
            ->call('createTag')
            ->assertHasErrors(['name' => 'unique']);

        $this->assertSame(1, Tag::count());
    }

    public function test_the_banner_must_be_a_webp(): void
    {
        $this->validForm()
            ->set('media', UploadedFile::fake()->image('banner.jpg'))
            ->call('createTag')
            ->assertHasErrors(['media' => 'mimes']);

        $this->assertDatabaseCount('tags', 0);
    }

    public function test_the_banner_is_capped_at_five_megabytes(): void
    {
        $this->validForm()
            ->set('media', UploadedFile::fake()->image('banner.webp')->size(5121))
            ->call('createTag')
            ->assertHasErrors(['media' => 'max']);
    }

    private function validForm(): Testable
    {
        return Livewire::test(Create::class)
            ->set('media', UploadedFile::fake()->image('banner.webp'))
            ->set('name', 'Shonen Jump')
            ->set('body', 'Everything from Weekly Shonen Jump.');
    }
}
