<?php

namespace Tests\Feature\Livewire\Post;

use App\Livewire\Post\Create;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Topic;
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

    private Topic $topic;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->blogger()->create());
        $this->topic = Topic::factory()->create();
    }

    public function test_it_renders_for_a_blogger(): void
    {
        Livewire::test(Create::class)->assertOk();
    }

    public function test_it_offers_the_available_topics_and_tags(): void
    {
        Tag::factory()->count(2)->create();

        $component = Livewire::test(Create::class);

        $this->assertCount(1, $component->viewData('topics'));
        $this->assertCount(2, $component->viewData('tags'));
    }

    public function test_a_plain_user_cannot_open_it(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Create::class)
            ->set('heading', 'Sneaky Post')
            ->call('createPost')
            ->assertForbidden();

        $this->assertDatabaseCount('posts', 0);
    }

    /** Store */
    public function test_a_blogger_can_create_a_post(): void
    {
        $this->validForm()
            ->call('createPost')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', [
            'topic_id' => $this->topic->id,
            'heading' => 'Attack On Titan Recap',
            'slug' => 'attack-on-titan-recap',
            'body' => 'A recap of the final season.',
            'is_publish' => true,
        ]);
    }

    public function test_creating_a_post_stores_its_cover_image(): void
    {
        $this->validForm()->call('createPost');

        $post = Post::first();

        $this->assertNotNull($post->media);
        $this->assertSame('image', $post->media->mime);
        $this->assertCount(1, Storage::disk('public')->files('media'));
    }

    public function test_creating_a_post_attaches_the_selected_tags(): void
    {
        $tags = Tag::factory()->count(2)->create();

        $this->validForm()
            ->set('selectedTags', $tags->pluck('id')->all())
            ->call('createPost');

        $this->assertEqualsCanonicalizing(
            $tags->pluck('id')->all(),
            Post::first()->tags->pluck('id')->all()
        );
    }

    public function test_a_post_can_be_created_without_tags(): void
    {
        $this->validForm()->call('createPost')->assertHasNoErrors();

        $this->assertCount(0, Post::first()->tags);
    }

    public function test_it_closes_the_modal_and_announces_success(): void
    {
        $this->validForm()
            ->call('createPost')
            ->assertDispatched('close')
            ->assertDispatched('post-event')
            ->assertDispatched('swal', [
                'title' => config('messages.post.create'),
                'icon' => 'success',
                'iconColor' => 'green',
            ]);
    }

    /** Validation */
    public function test_the_required_fields_are_enforced(): void
    {
        Livewire::test(Create::class)
            ->call('createPost')
            ->assertHasErrors([
                'media' => 'required',
                'topic_id' => 'required',
                'heading' => 'required',
                'body' => 'required',
            ]);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_the_heading_must_be_unique(): void
    {
        Post::factory()->create(['heading' => 'Attack On Titan Recap']);

        $this->validForm()
            ->call('createPost')
            ->assertHasErrors(['heading' => 'unique']);

        $this->assertSame(1, Post::count());
    }

    public function test_the_heading_is_capped_at_255_characters(): void
    {
        $this->validForm()
            ->set('heading', str_repeat('a', 256))
            ->call('createPost')
            ->assertHasErrors(['heading' => 'max']);
    }

    public function test_the_topic_must_exist(): void
    {
        $this->validForm()
            ->set('topic_id', 9999)
            ->call('createPost')
            ->assertHasErrors(['topic_id' => 'exists']);
    }

    public function test_the_cover_image_must_be_a_webp(): void
    {
        $this->validForm()
            ->set('media', UploadedFile::fake()->image('cover.jpg'))
            ->call('createPost')
            ->assertHasErrors(['media' => 'mimes']);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_the_cover_image_is_capped_at_five_megabytes(): void
    {
        $this->validForm()
            ->set('media', UploadedFile::fake()->image('cover.webp')->size(5121))
            ->call('createPost')
            ->assertHasErrors(['media' => 'max']);
    }

    public function test_the_selected_tags_must_exist(): void
    {
        $this->validForm()
            ->set('selectedTags', [9999])
            ->call('createPost')
            ->assertHasErrors(['selectedTags.0' => 'exists']);

        $this->assertDatabaseCount('posts', 0);
    }

    /**
     * A component filled in with everything a valid submission needs.
     */
    private function validForm(): Testable
    {
        return Livewire::test(Create::class)
            ->set('media', UploadedFile::fake()->image('cover.webp'))
            ->set('topic_id', $this->topic->id)
            ->set('heading', 'Attack On Titan Recap')
            ->set('body', 'A recap of the final season.')
            ->set('is_publish', true);
    }
}
