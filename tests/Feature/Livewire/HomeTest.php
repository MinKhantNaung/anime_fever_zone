<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Home;
use App\Models\Post;
use Database\Factories\VideoFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_with_no_content(): void
    {
        Livewire::test(Home::class)
            ->assertOk()
            ->assertCount('featuredPosts', 0)
            ->assertCount('videos', 0);
    }

    /** Featured posts */
    public function test_it_loads_published_featured_posts(): void
    {
        Post::factory()->count(2)->withMedia()->published()->featured()->create();

        Livewire::test(Home::class)->assertCount('featuredPosts', 2);
    }

    public function test_it_ignores_unpublished_featured_posts(): void
    {
        Post::factory()->withMedia()->published()->featured()->create();
        Post::factory()->withMedia()->draft()->featured()->create();

        Livewire::test(Home::class)->assertCount('featuredPosts', 1);
    }

    public function test_it_ignores_published_posts_that_are_not_featured(): void
    {
        Post::factory()->withMedia()->published()->create();

        Livewire::test(Home::class)->assertCount('featuredPosts', 0);
    }

    public function test_it_shows_at_most_five_featured_posts(): void
    {
        Post::factory()->count(8)->withMedia()->published()->featured()->create();

        Livewire::test(Home::class)->assertCount('featuredPosts', 5);
    }

    /** Videos */
    public function test_it_loads_published_videos(): void
    {
        VideoFactory::new()->count(3)->published()->create();
        VideoFactory::new()->create();

        Livewire::test(Home::class)->assertCount('videos', 3);
    }

    public function test_it_shows_at_most_ten_videos(): void
    {
        VideoFactory::new()->count(12)->published()->create();

        Livewire::test(Home::class)->assertCount('videos', 10);
    }

    /** Post feed */
    public function test_it_lists_published_posts_in_the_feed(): void
    {
        $published = Post::factory()->withMedia()->published()->create(['heading' => 'A Published Post']);
        Post::factory()->withMedia()->draft()->create(['heading' => 'A Draft Post']);

        Livewire::test(Home::class)
            ->assertOk()
            ->assertSee($published->heading)
            ->assertDontSee('A Draft Post');
    }

    public function test_the_feed_paginates_ten_posts_at_a_time(): void
    {
        Post::factory()->count(12)->withMedia()->published()->create();

        $posts = Livewire::test(Home::class)->viewData('posts');

        $this->assertCount(10, $posts->items());
        $this->assertTrue($posts->hasMorePages());
    }

    public function test_the_feed_orders_by_most_recently_updated(): void
    {
        $older = Post::factory()->withMedia()->published()->create();
        $newer = Post::factory()->withMedia()->published()->create();

        $older->updated_at = now()->subWeek();
        $older->save();

        $posts = Livewire::test(Home::class)->viewData('posts');

        $this->assertSame($newer->id, $posts->items()[0]->id);
    }

    /** Route */
    public function test_the_home_page_is_publicly_reachable(): void
    {
        $this->get(route('home'))->assertOk();
    }
}
