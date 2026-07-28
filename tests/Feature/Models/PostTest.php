<?php

namespace Tests\Feature\Models;

use App\Models\Comment;
use App\Models\Media;
use App\Models\Post;
use App\Models\Section;
use App\Models\Tag;
use App\Models\Topic;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PostTest extends TestCase
{
    use RefreshDatabase;

    /** Store */
    public function test_it_can_be_created(): void
    {
        $topic = Topic::factory()->create();

        $post = Post::create([
            'topic_id' => $topic->id,
            'heading' => 'Attack On Titan Final Season',
            'body' => 'A recap of the final season.',
            'is_feature' => true,
            'is_publish' => true,
        ]);

        $this->assertDatabaseCount('posts', 1);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'topic_id' => $topic->id,
            'heading' => 'Attack On Titan Final Season',
            'body' => 'A recap of the final season.',
        ]);
    }

    public function test_it_defaults_to_unfeatured_and_unpublished(): void
    {
        $post = Post::create([
            'topic_id' => Topic::factory()->create()->id,
            'heading' => 'Draft Post',
            'body' => 'Not ready yet.',
        ]);

        $this->assertFalse($post->fresh()->is_publish);
        $this->assertFalse((bool) $post->fresh()->is_feature);
    }

    public function test_it_casts_is_publish_to_a_boolean(): void
    {
        $post = Post::factory()->published()->create();

        $this->assertTrue($post->fresh()->is_publish);
        $this->assertIsBool($post->fresh()->is_publish);
    }

    /** Slug generation */
    public function test_it_generates_a_slug_from_the_heading_when_created(): void
    {
        $post = Post::factory()->create(['heading' => 'One Piece Chapter 1100']);

        $this->assertSame('one-piece-chapter-1100', $post->slug);
    }

    public function test_it_regenerates_the_slug_when_the_heading_is_updated(): void
    {
        $post = Post::factory()->create(['heading' => 'Old Heading']);

        $post->update(['heading' => 'Brand New Heading']);

        $this->assertSame('brand-new-heading', $post->fresh()->slug);
    }

    public function test_it_ignores_a_manually_supplied_slug(): void
    {
        $post = Post::factory()->create([
            'heading' => 'Jujutsu Kaisen',
            'slug' => 'a-slug-of-my-own',
        ]);

        $this->assertSame('jujutsu-kaisen', $post->slug);
    }

    /** Relationships */
    public function test_it_belongs_to_a_topic(): void
    {
        $topic = Topic::factory()->create();
        $post = Post::factory()->for($topic)->create();

        $this->assertInstanceOf(Topic::class, $post->topic);
        $this->assertSame($topic->id, $post->topic->id);
    }

    public function test_it_belongs_to_many_tags(): void
    {
        $post = Post::factory()->create();
        $tags = Tag::factory()->count(3)->create();

        $post->tags()->attach($tags);

        $this->assertCount(3, $post->fresh()->tags);
        $this->assertInstanceOf(Tag::class, $post->fresh()->tags->first());
        $this->assertEqualsCanonicalizing(
            $tags->pluck('id')->all(),
            $post->fresh()->tags->pluck('id')->all()
        );
    }

    public function test_it_stores_tag_links_in_the_post_tags_table(): void
    {
        $post = Post::factory()->create();
        $tag = Tag::factory()->create();

        $post->tags()->attach($tag);

        $this->assertDatabaseHas('post_tags', [
            'post_id' => $post->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_it_can_detach_a_tag(): void
    {
        $post = Post::factory()->create();
        $tag = Tag::factory()->create();

        $post->tags()->attach($tag);
        $post->tags()->detach($tag);

        $this->assertCount(0, $post->fresh()->tags);
        $this->assertDatabaseMissing('post_tags', [
            'post_id' => $post->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_it_has_many_sections(): void
    {
        $post = Post::factory()->create();
        Section::factory()->count(2)->create(['post_id' => $post->id]);
        Section::factory()->create();

        $this->assertCount(2, $post->sections);
        $this->assertInstanceOf(Section::class, $post->sections->first());
    }

    public function test_it_has_one_media(): void
    {
        $post = Post::factory()->create();

        $media = $post->media()->create([
            'url' => 'media/cover.webp',
            'mime' => 'image',
        ]);

        $this->assertInstanceOf(Media::class, $post->fresh()->media);
        $this->assertSame($media->id, $post->fresh()->media->id);
        $this->assertDatabaseHas('media', [
            'mediable_type' => Post::class,
            'mediable_id' => $post->id,
        ]);
    }

    public function test_it_has_many_comments(): void
    {
        $post = Post::factory()->create();

        Comment::factory()->count(2)->for($post, 'commentable')->create();
        Comment::factory()->create();

        $this->assertCount(2, $post->comments);
        $this->assertInstanceOf(Comment::class, $post->comments->first());
    }

    public function test_deleting_a_post_cascades_to_its_sections(): void
    {
        $post = Post::factory()->create();
        Section::factory()->create(['post_id' => $post->id]);

        $post->delete();

        $this->assertDatabaseCount('sections', 0);
    }

    /** Scopes */
    public function test_the_published_scope_only_returns_published_posts(): void
    {
        $published = Post::factory()->published()->create();
        Post::factory()->draft()->create();

        $results = Post::published()->get();

        $this->assertCount(1, $results);
        $this->assertSame($published->id, $results->first()->id);
    }

    public function test_the_published_scope_orders_by_most_recently_updated(): void
    {
        $older = $this->publishedPostUpdatedAt(now()->subDays(5));
        $newer = $this->publishedPostUpdatedAt(now()->subDay());

        $this->assertSame(
            [$newer->id, $older->id],
            Post::published()->pluck('id')->all()
        );
    }

    /** Query methods */
    public function test_it_finds_a_post_by_slug_with_its_relations(): void
    {
        $post = Post::factory()->create(['heading' => 'Demon Slayer Arc']);

        $found = (new Post)->findPostWithSlug('demon-slayer-arc');

        $this->assertSame($post->id, $found->id);
        $this->assertTrue($found->relationLoaded('media'));
        $this->assertTrue($found->relationLoaded('topic'));
        $this->assertTrue($found->relationLoaded('tags'));
        $this->assertTrue($found->relationLoaded('sections'));
    }

    public function test_it_returns_null_when_no_post_matches_the_slug(): void
    {
        $this->assertNull((new Post)->findPostWithSlug('nothing-here'));
    }

    public function test_it_returns_at_most_five_published_featured_posts(): void
    {
        Post::factory()->count(6)->published()->featured()->create();
        Post::factory()->count(2)->draft()->featured()->create();
        Post::factory()->count(2)->published()->create();

        $featured = (new Post)->getFeaturedPosts();

        $this->assertCount(5, $featured);
        $this->assertTrue($featured->every(fn (Post $post) => $post->is_publish));
        $this->assertTrue($featured->every(fn (Post $post) => (bool) $post->is_feature));
    }

    public function test_it_excludes_the_current_post_from_the_post_page_sidebar(): void
    {
        $current = Post::factory()->published()->create();
        Post::factory()->count(3)->published()->create();

        $sidebar = (new Post)->featuredPostsForPostPage($current->id);

        $this->assertCount(3, $sidebar);
        $this->assertNotContains($current->id, $sidebar->pluck('id')->all());
    }

    public function test_the_post_page_sidebar_skips_posts_older_than_a_month(): void
    {
        $current = Post::factory()->published()->create();
        $recent = $this->publishedPostUpdatedAt(now()->subDays(3));
        $this->publishedPostUpdatedAt(now()->subMonths(2));

        $sidebar = (new Post)->featuredPostsForPostPage($current->id);

        $this->assertCount(1, $sidebar);
        $this->assertSame($recent->id, $sidebar->first()->id);
    }

    public function test_it_paginates_all_posts_five_at_a_time_newest_first(): void
    {
        $posts = Post::factory()->count(7)->create();

        $paginator = (new Post)->getAllPerFive();

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertSame(5, $paginator->perPage());
        $this->assertSame(7, $paginator->total());
        $this->assertSame($posts->last()->id, $paginator->items()[0]->id);
    }

    public function test_it_paginates_published_posts_twelve_at_a_time(): void
    {
        Post::factory()->count(13)->published()->create();
        Post::factory()->count(2)->draft()->create();

        $paginator = (new Post)->getPublishedPosts();

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertSame(12, $paginator->perPage());
        $this->assertSame(13, $paginator->total());
    }

    public function test_it_returns_published_posts_for_a_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'Shonen']);
        $published = Post::factory()->published()->create();
        $draft = Post::factory()->draft()->create();
        $published->tags()->attach($tag);
        $draft->tags()->attach($tag);

        Post::factory()->published()->create()->tags()->attach(Tag::factory()->create());

        $results = (new Post)->getPostsOfTag('shonen');

        $this->assertSame(1, $results->total());
        $this->assertSame($published->id, $results->items()[0]->id);
    }

    public function test_it_returns_published_posts_for_a_topic(): void
    {
        $topic = Topic::factory()->create(['name' => 'Movie Reviews']);
        $published = Post::factory()->published()->for($topic)->create();
        Post::factory()->draft()->for($topic)->create();
        Post::factory()->published()->create();

        $results = (new Post)->getPostsOfTopic('movie-reviews');

        $this->assertSame(1, $results->total());
        $this->assertSame($published->id, $results->items()[0]->id);
    }

    public function test_it_returns_an_empty_result_for_an_unknown_tag(): void
    {
        Post::factory()->published()->create();

        $this->assertSame(0, (new Post)->getPostsOfTag('does-not-exist')->total());
    }

    /**
     * Persist a published post with an explicit `updated_at`, bypassing the
     * timestamp Eloquent would otherwise set on save.
     */
    private function publishedPostUpdatedAt(\DateTimeInterface $updatedAt): Post
    {
        $post = Post::factory()->published()->create();

        $post->updated_at = $updatedAt;
        $post->save();

        return $post;
    }
}
