<?php

namespace Tests\Feature\Livewire;

use App\Livewire\TagShow;
use App\Models\Post;
use App\Models\Tag;
use Database\Factories\VideoFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class TagShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_for_a_tag_with_no_posts(): void
    {
        $tag = Tag::factory()->withMedia()->create(['name' => 'Shonen']);

        Livewire::test(TagShow::class, ['slug' => $tag->slug])
            ->assertOk()
            ->assertSet('tag.id', $tag->id);
    }

    public function test_it_aborts_when_the_tag_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        Livewire::test(TagShow::class, ['slug' => 'no-such-tag']);
    }

    public function test_it_lists_published_posts_carrying_the_tag(): void
    {
        $tag = Tag::factory()->withMedia()->create(['name' => 'Shonen']);
        $post = Post::factory()->withMedia()->published()->create();
        $post->tags()->attach($tag);

        Livewire::test(TagShow::class, ['slug' => 'shonen'])
            ->assertOk()
            ->assertSee($post->heading);
    }

    public function test_it_excludes_posts_without_the_tag(): void
    {
        $tag = Tag::factory()->withMedia()->create(['name' => 'Shonen']);
        $tagged = Post::factory()->withMedia()->published()->create();
        $tagged->tags()->attach($tag);
        Post::factory()->withMedia()->published()->create();

        $posts = Livewire::test(TagShow::class, ['slug' => 'shonen'])->viewData('posts');

        $this->assertCount(1, $posts->items());
        $this->assertSame($tagged->id, $posts->items()[0]->id);
    }

    public function test_it_excludes_unpublished_posts_carrying_the_tag(): void
    {
        $tag = Tag::factory()->withMedia()->create(['name' => 'Shonen']);
        $draft = Post::factory()->withMedia()->draft()->create();
        $draft->tags()->attach($tag);

        $posts = Livewire::test(TagShow::class, ['slug' => 'shonen'])->viewData('posts');

        $this->assertCount(0, $posts->items());
    }

    public function test_it_paginates_ten_posts_at_a_time(): void
    {
        $tag = Tag::factory()->withMedia()->create(['name' => 'Shonen']);
        Post::factory()->count(12)->withMedia()->published()->create()
            ->each(fn (Post $post) => $post->tags()->attach($tag));

        $posts = Livewire::test(TagShow::class, ['slug' => 'shonen'])->viewData('posts');

        $this->assertCount(10, $posts->items());
        $this->assertTrue($posts->hasMorePages());
    }

    public function test_it_loads_featured_posts_and_videos_for_the_sidebar(): void
    {
        $tag = Tag::factory()->withMedia()->create(['name' => 'Shonen']);
        Post::factory()->count(2)->withMedia()->published()->featured()->create();
        VideoFactory::new()->count(3)->published()->create();

        Livewire::test(TagShow::class, ['slug' => $tag->slug])
            ->assertCount('featuredPosts', 2)
            ->assertCount('videos', 3);
    }

    /** Route */
    public function test_the_tag_page_is_publicly_reachable(): void
    {
        $tag = Tag::factory()->withMedia()->create(['name' => 'Shonen']);

        $this->get(route('tag', $tag->slug))->assertOk();
    }
}
