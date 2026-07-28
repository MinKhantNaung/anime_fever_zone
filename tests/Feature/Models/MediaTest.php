<?php

namespace Tests\Feature\Models;

use App\Models\Media;
use App\Models\Post;
use App\Models\Section;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MediaTest extends TestCase
{
    use RefreshDatabase;

    /** Store */
    public function test_it_can_be_created(): void
    {
        $post = Post::factory()->create();

        $media = Media::create([
            'mediable_type' => Post::class,
            'mediable_id' => $post->id,
            'url' => 'media/cover.webp',
            'mime' => 'image',
        ]);

        $this->assertDatabaseCount('media', 1);
        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'mediable_type' => Post::class,
            'mediable_id' => $post->id,
            'url' => 'media/cover.webp',
            'mime' => 'image',
        ]);
    }

    public function test_it_can_store_a_video(): void
    {
        $media = Media::factory()->video()->create();

        $this->assertSame('video', $media->mime);
        $this->assertDatabaseHas('media', ['id' => $media->id, 'mime' => 'video']);
    }

    /** Relationships */
    public function test_it_morphs_to_a_post(): void
    {
        $post = Post::factory()->create();
        $media = Media::factory()->for($post, 'mediable')->create();

        $this->assertInstanceOf(Post::class, $media->mediable);
        $this->assertSame($post->id, $media->mediable->id);
    }

    public function test_it_morphs_to_a_user(): void
    {
        $user = User::factory()->create();
        $media = Media::factory()->for($user, 'mediable')->create();

        $this->assertInstanceOf(User::class, $media->mediable);
        $this->assertSame($user->id, $media->mediable->id);
    }

    public function test_it_morphs_to_a_tag(): void
    {
        $tag = Tag::factory()->create();
        $media = Media::factory()->for($tag, 'mediable')->create();

        $this->assertInstanceOf(Tag::class, $media->mediable);
        $this->assertSame($tag->id, $media->mediable->id);
    }

    public function test_it_morphs_to_a_section(): void
    {
        $section = Section::factory()->create();
        $media = Media::factory()->for($section, 'mediable')->create();

        $this->assertInstanceOf(Section::class, $media->mediable);
        $this->assertSame($section->id, $media->mediable->id);
    }

    public function test_the_same_owner_id_on_different_types_stays_separate(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        Media::factory()->for($post, 'mediable')->create(['url' => 'media/post.webp']);
        Media::factory()->for($user, 'mediable')->create(['url' => 'media/user.webp']);

        $this->assertSame('media/post.webp', $post->media->url);
        $this->assertSame('media/user.webp', $user->media->url);
    }
}
