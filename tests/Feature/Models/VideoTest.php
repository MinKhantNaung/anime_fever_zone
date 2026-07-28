<?php

namespace Tests\Feature\Models;

use App\Models\Video;
use Database\Factories\VideoFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class VideoTest extends TestCase
{
    use RefreshDatabase;

    /** Store */
    public function test_it_can_be_created(): void
    {
        $video = Video::create([
            'title' => 'Top 10 Fight Scenes',
            'description' => 'Our picks for the best fights.',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_id' => 'dQw4w9WgXcQ',
            'is_publish' => true,
            'is_trending' => true,
        ]);

        $this->assertDatabaseCount('videos', 1);
        $this->assertDatabaseHas('videos', [
            'id' => $video->id,
            'title' => 'Top 10 Fight Scenes',
            'slug' => 'top-10-fight-scenes',
            'youtube_id' => 'dQw4w9WgXcQ',
        ]);
    }

    public function test_it_defaults_to_unpublished_and_not_trending(): void
    {
        $video = Video::create([
            'title' => 'A Draft Video',
            'youtube_url' => 'https://www.youtube.com/watch?v=abc12345678',
            'youtube_id' => 'abc12345678',
        ]);

        $this->assertFalse($video->fresh()->is_publish);
        $this->assertFalse($video->fresh()->is_trending);
    }

    public function test_it_casts_the_publish_and_trending_flags_to_booleans(): void
    {
        $video = VideoFactory::new()->published()->trending()->create()->fresh();

        $this->assertIsBool($video->is_publish);
        $this->assertIsBool($video->is_trending);
        $this->assertTrue($video->is_publish);
        $this->assertTrue($video->is_trending);
    }

    public function test_the_description_is_optional(): void
    {
        $video = VideoFactory::new()->create(['description' => null]);

        $this->assertNull($video->fresh()->description);
    }

    /** Slug generation */
    public function test_it_generates_a_slug_from_the_title_when_created(): void
    {
        $video = VideoFactory::new()->create(['title' => 'Chainsaw Man Opening Breakdown']);

        $this->assertSame('chainsaw-man-opening-breakdown', $video->slug);
    }

    public function test_it_regenerates_the_slug_when_the_title_changes(): void
    {
        $video = VideoFactory::new()->create(['title' => 'Old Title']);

        $video->update(['title' => 'Fresh New Title']);

        $this->assertSame('fresh-new-title', $video->fresh()->slug);
    }

    public function test_it_keeps_the_slug_when_only_other_fields_change(): void
    {
        $video = VideoFactory::new()->create(['title' => 'Stable Title']);

        $video->update(['description' => 'A rewritten description.']);

        $this->assertSame('stable-title', $video->fresh()->slug);
    }

    /** Thumbnails */
    public function test_it_builds_a_max_resolution_thumbnail_url_by_default(): void
    {
        $video = VideoFactory::new()->create(['youtube_id' => 'dQw4w9WgXcQ']);

        $this->assertSame(
            'https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg',
            $video->getThumbnailUrl()
        );
    }

    public function test_it_builds_a_thumbnail_url_for_a_given_quality(): void
    {
        $video = VideoFactory::new()->create(['youtube_id' => 'dQw4w9WgXcQ']);

        $this->assertSame(
            'https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg',
            $video->getThumbnailUrl('hqdefault')
        );
    }

    public function test_it_returns_an_empty_thumbnail_url_without_a_youtube_id(): void
    {
        $video = VideoFactory::new()->make(['youtube_id' => null]);

        $this->assertSame('', $video->getThumbnailUrl());
    }

    /** Queries */
    public function test_published_videos_can_be_filtered(): void
    {
        VideoFactory::new()->count(2)->published()->create();
        VideoFactory::new()->create();

        $this->assertSame(2, Video::where('is_publish', true)->count());
    }

    public function test_trending_videos_can_be_filtered(): void
    {
        VideoFactory::new()->published()->trending()->create();
        VideoFactory::new()->published()->create();

        $this->assertSame(1, Video::where('is_trending', true)->count());
    }
}
