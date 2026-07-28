<?php

namespace Tests\Feature\Models;

use App\Models\Media;
use App\Models\Post;
use App\Models\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SectionTest extends TestCase
{
    use RefreshDatabase;

    /** Store */
    public function test_it_can_be_created(): void
    {
        $post = Post::factory()->create();

        $section = Section::create([
            'post_id' => $post->id,
            'heading' => 'The Marineford Arc',
            'body' => 'Where it all changed.',
        ]);

        $this->assertDatabaseCount('sections', 1);
        $this->assertDatabaseHas('sections', [
            'id' => $section->id,
            'post_id' => $post->id,
            'heading' => 'The Marineford Arc',
            'body' => 'Where it all changed.',
        ]);
    }

    public function test_it_can_be_created_without_a_heading(): void
    {
        $section = Section::factory()->withoutHeading()->create();

        $this->assertNull($section->heading);
        $this->assertDatabaseHas('sections', ['id' => $section->id, 'heading' => null]);
    }

    public function test_it_mass_assigns_the_post_heading_and_body(): void
    {
        $this->assertSame(
            ['post_id', 'heading', 'body'],
            (new Section)->getFillable()
        );
    }

    /** Relationships */
    public function test_it_has_many_media(): void
    {
        $section = Section::factory()->create();

        $section->media()->create(['url' => 'media/one.webp', 'mime' => 'image']);
        $section->media()->create(['url' => 'media/two.webp', 'mime' => 'image']);

        $this->assertCount(2, $section->fresh()->media);
        $this->assertInstanceOf(Media::class, $section->fresh()->media->first());
        $this->assertDatabaseHas('media', [
            'mediable_type' => Section::class,
            'mediable_id' => $section->id,
        ]);
    }

    public function test_its_media_is_empty_when_none_has_been_uploaded(): void
    {
        $this->assertCount(0, Section::factory()->create()->media);
    }

    public function test_it_only_returns_its_own_media(): void
    {
        $section = Section::factory()->create();
        $other = Section::factory()->create();

        $section->media()->create(['url' => 'media/mine.webp', 'mime' => 'image']);
        $other->media()->create(['url' => 'media/theirs.webp', 'mime' => 'image']);

        $this->assertCount(1, $section->media);
        $this->assertSame('media/mine.webp', $section->media->first()->url);
    }

    public function test_it_is_reachable_from_its_post(): void
    {
        $post = Post::factory()->create();
        $section = Section::factory()->create(['post_id' => $post->id]);

        $this->assertTrue($post->sections->contains($section));
    }
}
