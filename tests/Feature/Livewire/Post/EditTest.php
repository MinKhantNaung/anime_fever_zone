<?php

namespace Tests\Feature\Livewire\Post;

use App\Livewire\Post\Edit;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class EditTest extends TestCase
{
    use RefreshDatabase;

    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->blogger()->create());
        $this->post = Post::factory()->withMedia()->create(['heading' => 'Original Heading']);
    }

    /** Mount */
    public function test_it_preloads_the_posts_current_values(): void
    {
        Livewire::test(Edit::class, ['post' => $this->post])
            ->assertOk()
            ->assertSet('topic_id', $this->post->topic_id)
            ->assertSet('heading', 'Original Heading')
            ->assertSet('body', $this->post->body)
            ->assertSet('is_publish', $this->post->is_publish);
    }

    public function test_it_preloads_the_posts_current_tags(): void
    {
        $tags = Tag::factory()->count(2)->create();
        $this->post->tags()->attach($tags);

        Livewire::test(Edit::class, ['post' => $this->post])
            ->assertSet('selectedTags', $tags->pluck('id')->all());
    }

    public function test_a_plain_user_cannot_open_it(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Edit::class, ['post' => $this->post])->assertForbidden();
    }

    /** Update */
    public function test_a_blogger_can_update_a_post(): void
    {
        $topic = Topic::factory()->create();

        Livewire::test(Edit::class, ['post' => $this->post])
            ->set('topic_id', $topic->id)
            ->set('heading', 'Revised Heading')
            ->set('body', 'Revised body.')
            ->set('is_publish', true)
            ->call('updatePost')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', [
            'id' => $this->post->id,
            'topic_id' => $topic->id,
            'heading' => 'Revised Heading',
            'slug' => 'revised-heading',
            'body' => 'Revised body.',
            'is_publish' => true,
        ]);
    }

    public function test_updating_replaces_the_tag_selection(): void
    {
        $original = Tag::factory()->create();
        $replacement = Tag::factory()->create();
        $this->post->tags()->attach($original);

        Livewire::test(Edit::class, ['post' => $this->post])
            ->set('selectedTags', [$replacement->id])
            ->call('updatePost')
            ->assertHasNoErrors();

        $this->assertSame([$replacement->id], $this->post->fresh()->tags->pluck('id')->all());
    }

    public function test_updating_without_a_new_image_keeps_the_existing_one(): void
    {
        $originalMediaId = $this->post->media->id;

        Livewire::test(Edit::class, ['post' => $this->post])
            ->set('heading', 'Revised Heading')
            ->call('updatePost')
            ->assertHasNoErrors();

        $this->assertSame($originalMediaId, $this->post->fresh()->media->id);
    }

    public function test_uploading_a_new_image_replaces_the_old_one(): void
    {
        $originalMediaId = $this->post->media->id;

        Livewire::test(Edit::class, ['post' => $this->post])
            ->set('media', UploadedFile::fake()->image('new-cover.webp'))
            ->call('updatePost')
            ->assertHasNoErrors();

        $this->assertNotSame($originalMediaId, $this->post->fresh()->media->id);
        $this->assertDatabaseMissing('media', ['id' => $originalMediaId]);
    }

    public function test_it_closes_the_modal_and_announces_success(): void
    {
        Livewire::test(Edit::class, ['post' => $this->post])
            ->call('updatePost')
            ->assertDispatched('close')
            ->assertDispatched('post-event')
            ->assertDispatched('swal', [
                'title' => config('messages.post.update'),
                'icon' => 'success',
                'iconColor' => 'green',
            ]);
    }

    /** Validation */
    public function test_the_heading_and_body_are_required(): void
    {
        Livewire::test(Edit::class, ['post' => $this->post])
            ->set('heading', '')
            ->set('body', '')
            ->call('updatePost')
            ->assertHasErrors(['heading' => 'required', 'body' => 'required']);

        $this->assertSame('Original Heading', $this->post->fresh()->heading);
    }

    public function test_the_heading_must_be_unique_across_other_posts(): void
    {
        Post::factory()->create(['heading' => 'Taken Heading']);

        Livewire::test(Edit::class, ['post' => $this->post])
            ->set('heading', 'Taken Heading')
            ->call('updatePost')
            ->assertHasErrors(['heading' => 'unique']);
    }

    public function test_a_post_may_keep_its_own_heading(): void
    {
        Livewire::test(Edit::class, ['post' => $this->post])
            ->set('heading', 'Original Heading')
            ->call('updatePost')
            ->assertHasNoErrors();
    }

    public function test_a_replacement_image_must_be_a_webp(): void
    {
        Livewire::test(Edit::class, ['post' => $this->post])
            ->set('media', UploadedFile::fake()->image('cover.png'))
            ->call('updatePost')
            ->assertHasErrors(['media' => 'mimes']);
    }

    public function test_the_topic_must_exist(): void
    {
        Livewire::test(Edit::class, ['post' => $this->post])
            ->set('topic_id', 9999)
            ->call('updatePost')
            ->assertHasErrors(['topic_id' => 'exists']);
    }
}
