<?php

namespace Tests\Feature\Livewire\Section;

use App\Livewire\Section\Index;
use App\Models\Post;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class IndexTest extends TestCase
{
    use RefreshDatabase;

    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->blogger()->create());
        $this->post = Post::factory()->withMedia()->create();
    }

    public function test_it_renders_for_a_blogger(): void
    {
        Livewire::test(Index::class, ['post' => $this->post])->assertOk();
    }

    public function test_it_eager_loads_the_posts_relations(): void
    {
        $post = Livewire::test(Index::class, ['post' => $this->post])->get('post');

        $this->assertTrue($post->relationLoaded('sections'));
        $this->assertTrue($post->relationLoaded('media'));
        $this->assertTrue($post->relationLoaded('topic'));
        $this->assertTrue($post->relationLoaded('tags'));
    }

    public function test_it_shows_the_posts_sections(): void
    {
        Section::factory()->create([
            'post_id' => $this->post->id,
            'heading' => 'A Listed Section',
        ]);

        $post = Livewire::test(Index::class, ['post' => $this->post])->get('post');

        $this->assertCount(1, $post->sections);
    }

    public function test_a_plain_user_cannot_open_it(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class, ['post' => $this->post])->assertForbidden();
    }

    /** Removing */
    public function test_a_blogger_can_remove_a_section(): void
    {
        $section = Section::factory()->create(['post_id' => $this->post->id]);

        Livewire::test(Index::class, ['post' => $this->post])
            ->call('removeSection', $section)
            ->assertDispatched('section-reload')
            ->assertDispatched('swal', [
                'title' => config('messages.section.destroy'),
                'icon' => 'success',
                'iconColor' => 'green',
            ]);

        $this->assertDatabaseMissing('sections', ['id' => $section->id]);
    }

    public function test_removing_a_section_removes_its_media(): void
    {
        $section = Section::factory()->create(['post_id' => $this->post->id]);
        $section->media()->create(['url' => 'media/one.webp', 'mime' => 'image']);
        $section->media()->create(['url' => 'media/two.webp', 'mime' => 'image']);

        Livewire::test(Index::class, ['post' => $this->post])->call('removeSection', $section);

        $this->assertDatabaseMissing('media', ['mediable_type' => Section::class]);
    }

    public function test_removing_a_section_leaves_the_post_and_its_siblings_alone(): void
    {
        $section = Section::factory()->create(['post_id' => $this->post->id]);
        $sibling = Section::factory()->create(['post_id' => $this->post->id]);

        Livewire::test(Index::class, ['post' => $this->post])->call('removeSection', $section);

        $this->assertDatabaseHas('sections', ['id' => $sibling->id]);
        $this->assertDatabaseHas('posts', ['id' => $this->post->id]);
    }
}
