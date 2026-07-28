<?php

namespace Tests\Feature\Livewire\Tag;

use App\Livewire\Tag\Index;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class IndexTest extends TestCase
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
        Livewire::test(Index::class)->assertOk();
    }

    public function test_it_lists_the_tags(): void
    {
        Tag::factory()->withMedia()->create(['name' => 'Listed Tag']);

        Livewire::test(Index::class)
            ->assertOk()
            ->assertSee('Listed Tag');
    }

    public function test_it_paginates_two_tags_at_a_time(): void
    {
        Tag::factory()->count(5)->withMedia()->create();

        $tags = Livewire::test(Index::class)->viewData('tags');

        $this->assertCount(2, $tags->items());
        $this->assertSame(5, $tags->total());
    }

    public function test_it_shows_the_newest_tags_first(): void
    {
        $first = Tag::factory()->withMedia()->create();
        $second = Tag::factory()->withMedia()->create();

        $tags = Livewire::test(Index::class)->viewData('tags');

        $this->assertSame([$second->id, $first->id], $tags->pluck('id')->all());
    }

    public function test_a_plain_user_cannot_open_it(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)->assertForbidden();
    }

    /** Deleting */
    public function test_a_blogger_can_delete_a_tag(): void
    {
        $tag = Tag::factory()->withMedia()->create();

        Livewire::test(Index::class)
            ->call('deleteTag', $tag)
            ->assertDispatched('swal', [
                'title' => config('messages.tag.destroy'),
                'icon' => 'success',
                'iconColor' => 'green',
            ])
            ->assertDispatched('tag-reload');

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_deleting_a_tag_removes_its_media(): void
    {
        $tag = Tag::factory()->withMedia()->create();

        Livewire::test(Index::class)->call('deleteTag', $tag);

        $this->assertDatabaseCount('media', 0);
    }

    public function test_deleting_a_tag_unlinks_it_from_posts_without_deleting_them(): void
    {
        $tag = Tag::factory()->withMedia()->create();
        $post = Post::factory()->create();
        $post->tags()->attach($tag);

        Livewire::test(Index::class)->call('deleteTag', $tag);

        $this->assertDatabaseCount('post_tags', 0);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }
}
