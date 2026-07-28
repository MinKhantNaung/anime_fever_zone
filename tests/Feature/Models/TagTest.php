<?php

namespace Tests\Feature\Models;

use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TagTest extends TestCase
{
    use RefreshDatabase;

    /** Store */
    public function test_it_can_be_created(): void
    {
        $tag = Tag::create([
            'name' => 'Shonen Jump',
            'body' => 'Everything from Weekly Shonen Jump.',
        ]);

        $this->assertDatabaseCount('tags', 1);
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Shonen Jump',
            'slug' => 'shonen-jump',
            'body' => 'Everything from Weekly Shonen Jump.',
        ]);
    }

    public function test_it_generates_a_slug_from_the_name_when_created(): void
    {
        $tag = Tag::factory()->create(['name' => 'Slice Of Life']);

        $this->assertSame('slice-of-life', $tag->slug);
    }

    public function test_it_regenerates_the_slug_when_the_name_is_updated(): void
    {
        $tag = Tag::factory()->create(['name' => 'Isekai']);

        $tag->update(['name' => 'Isekai Fantasy']);

        $this->assertSame('isekai-fantasy', $tag->fresh()->slug);
    }

    public function test_it_ignores_a_manually_supplied_slug(): void
    {
        $tag = Tag::factory()->create([
            'name' => 'Mecha',
            'slug' => 'not-mecha',
        ]);

        $this->assertSame('mecha', $tag->slug);
    }

    /** Relationships */
    public function test_it_has_one_media(): void
    {
        $tag = Tag::factory()->create();

        $media = $tag->media()->create([
            'url' => 'media/tag-banner.webp',
            'mime' => 'image',
        ]);

        $this->assertInstanceOf(Media::class, $tag->fresh()->media);
        $this->assertSame($media->id, $tag->fresh()->media->id);
        $this->assertDatabaseHas('media', [
            'mediable_type' => Tag::class,
            'mediable_id' => $tag->id,
        ]);
    }

    public function test_its_media_is_null_when_none_has_been_uploaded(): void
    {
        $this->assertNull(Tag::factory()->create()->media);
    }

    public function test_it_belongs_to_many_posts(): void
    {
        $tag = Tag::factory()->create();
        $posts = Post::factory()->count(2)->create();
        Post::factory()->create();

        $tag->posts()->attach($posts);

        $this->assertCount(2, $tag->fresh()->posts);
        $this->assertInstanceOf(Post::class, $tag->fresh()->posts->first());
        $this->assertEqualsCanonicalizing(
            $posts->pluck('id')->all(),
            $tag->fresh()->posts->pluck('id')->all()
        );
    }

    public function test_it_can_sync_its_posts(): void
    {
        $tag = Tag::factory()->create();
        $first = Post::factory()->create();
        $second = Post::factory()->create();

        $tag->posts()->attach($first);
        $tag->posts()->sync([$second->id]);

        $this->assertSame([$second->id], $tag->fresh()->posts->pluck('id')->all());
    }

    /** Query methods */
    public function test_it_returns_every_tag_with_only_id_and_name(): void
    {
        Tag::factory()->count(3)->create();

        $tags = (new Tag)->getAllByName();

        $this->assertCount(3, $tags);
        $this->assertSame(['id', 'name'], array_keys($tags->first()->getAttributes()));
    }
}
