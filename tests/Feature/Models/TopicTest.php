<?php

namespace Tests\Feature\Models;

use App\Models\Post;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TopicTest extends TestCase
{
    use RefreshDatabase;

    /** Store */
    public function test_it_can_be_created(): void
    {
        $topic = Topic::create(['name' => 'Anime News']);

        $this->assertDatabaseCount('topics', 1);
        $this->assertDatabaseHas('topics', [
            'id' => $topic->id,
            'name' => 'Anime News',
            'slug' => 'anime-news',
        ]);
    }

    public function test_it_generates_a_slug_from_the_name_when_created(): void
    {
        $topic = Topic::factory()->create(['name' => 'Seasonal Anime Roundup']);

        $this->assertSame('seasonal-anime-roundup', $topic->slug);
    }

    public function test_it_regenerates_the_slug_when_the_name_is_updated(): void
    {
        $topic = Topic::factory()->create(['name' => 'Old Name']);

        $topic->update(['name' => 'New Name']);

        $this->assertSame('new-name', $topic->fresh()->slug);
    }

    public function test_it_ignores_a_manually_supplied_slug(): void
    {
        $topic = Topic::factory()->create([
            'name' => 'Manga Corner',
            'slug' => 'something-else',
        ]);

        $this->assertSame('manga-corner', $topic->slug);
    }

    /** Relationships */
    public function test_it_has_many_posts(): void
    {
        $topic = Topic::factory()->create();
        Post::factory()->count(3)->for($topic)->create();
        Post::factory()->create();

        $this->assertCount(3, $topic->posts);
        $this->assertInstanceOf(Post::class, $topic->posts->first());
    }

    public function test_it_has_no_posts_when_newly_created(): void
    {
        $this->assertCount(0, Topic::factory()->create()->posts);
    }

    public function test_deleting_a_topic_cascades_to_its_posts(): void
    {
        $topic = Topic::factory()->create();
        Post::factory()->count(2)->for($topic)->create();

        $topic->delete();

        $this->assertDatabaseCount('posts', 0);
    }

    /** Query methods */
    public function test_it_returns_every_topic_with_only_id_name_and_slug(): void
    {
        Topic::factory()->count(2)->create();

        $topics = (new Topic)->getAllByName();

        $this->assertCount(2, $topics);
        $this->assertSame(
            ['id', 'name', 'slug'],
            array_keys($topics->first()->getAttributes())
        );
    }
}
