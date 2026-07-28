<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Topic as TopicComponent;
use App\Models\Post;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class TopicTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_for_a_topic_with_no_posts(): void
    {
        $topic = Topic::factory()->create(['name' => 'Anime News']);

        Livewire::test(TopicComponent::class, ['slug' => $topic->slug])
            ->assertOk()
            ->assertCount('featuredPosts', 0);
    }

    public function test_it_lists_published_posts_for_the_topic(): void
    {
        $topic = Topic::factory()->create(['name' => 'Anime News']);
        $post = Post::factory()->withMedia()->published()->for($topic)->create();

        Livewire::test(TopicComponent::class, ['slug' => 'anime-news'])
            ->assertOk()
            ->assertSee($post->heading);
    }

    public function test_it_excludes_posts_from_other_topics(): void
    {
        $topic = Topic::factory()->create(['name' => 'Anime News']);
        Post::factory()->withMedia()->published()->for($topic)->create();
        Post::factory()->withMedia()->published()->create(['heading' => 'Unrelated Heading']);

        $posts = Livewire::test(TopicComponent::class, ['slug' => 'anime-news'])->viewData('posts');

        $this->assertCount(1, $posts->items());
    }

    public function test_it_excludes_unpublished_posts(): void
    {
        $topic = Topic::factory()->create(['name' => 'Anime News']);
        Post::factory()->withMedia()->published()->for($topic)->create();
        Post::factory()->withMedia()->draft()->for($topic)->create();

        $posts = Livewire::test(TopicComponent::class, ['slug' => 'anime-news'])->viewData('posts');

        $this->assertCount(1, $posts->items());
    }

    public function test_it_paginates_ten_posts_at_a_time(): void
    {
        $topic = Topic::factory()->create(['name' => 'Anime News']);
        Post::factory()->count(12)->withMedia()->published()->for($topic)->create();

        $posts = Livewire::test(TopicComponent::class, ['slug' => 'anime-news'])->viewData('posts');

        $this->assertCount(10, $posts->items());
        $this->assertTrue($posts->hasMorePages());
    }

    public function test_it_loads_featured_posts_for_the_sidebar(): void
    {
        $topic = Topic::factory()->create(['name' => 'Anime News']);
        Post::factory()->count(2)->withMedia()->published()->featured()->create();

        Livewire::test(TopicComponent::class, ['slug' => $topic->slug])
            ->assertCount('featuredPosts', 2);
    }

    public function test_an_unknown_slug_simply_shows_no_posts(): void
    {
        Post::factory()->withMedia()->published()->create();

        $posts = Livewire::test(TopicComponent::class, ['slug' => 'no-such-topic'])->viewData('posts');

        $this->assertCount(0, $posts->items());
    }

    /** Route */
    public function test_the_topic_page_is_publicly_reachable(): void
    {
        $topic = Topic::factory()->create(['name' => 'Anime News']);

        $this->get(route('topic', $topic->slug))->assertOk();
    }
}
