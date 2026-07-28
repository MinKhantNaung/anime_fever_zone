<?php

namespace Tests\Feature\Models;

use App\Models\Post;
use App\Models\PostTag;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PostTagTest extends TestCase
{
    use RefreshDatabase;

    /** Store */
    public function test_it_can_be_created(): void
    {
        $post = Post::factory()->create();
        $tag = Tag::factory()->create();

        $postTag = PostTag::create([
            'post_id' => $post->id,
            'tag_id' => $tag->id,
        ]);

        $this->assertDatabaseCount('post_tags', 1);
        $this->assertDatabaseHas('post_tags', [
            'id' => $postTag->id,
            'post_id' => $post->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_it_mass_assigns_the_post_and_tag(): void
    {
        $this->assertSame(['post_id', 'tag_id'], (new PostTag)->getFillable());
    }

    public function test_it_backs_the_post_tags_table(): void
    {
        $this->assertSame('post_tags', (new PostTag)->getTable());
    }

    /** It is the pivot behind Post::tags() and Tag::posts() */
    public function test_a_row_created_directly_is_visible_through_the_relationships(): void
    {
        $post = Post::factory()->create();
        $tag = Tag::factory()->create();

        PostTag::create(['post_id' => $post->id, 'tag_id' => $tag->id]);

        $this->assertTrue($post->tags->contains($tag));
        $this->assertTrue($tag->posts->contains($post));
    }

    public function test_attaching_through_the_relationship_writes_a_row(): void
    {
        $post = Post::factory()->create();
        $tag = Tag::factory()->create();

        $post->tags()->attach($tag);

        $this->assertSame(1, PostTag::count());
        $this->assertSame($post->id, PostTag::first()->post_id);
        $this->assertSame($tag->id, PostTag::first()->tag_id);
    }

    public function test_a_post_can_be_linked_to_several_tags(): void
    {
        $post = Post::factory()->create();
        $tags = Tag::factory()->count(3)->create();

        $post->tags()->attach($tags);

        $this->assertSame(3, PostTag::where('post_id', $post->id)->count());
    }
}
