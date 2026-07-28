<?php

namespace Tests\Feature\Livewire\Post;

use App\Livewire\Post\Index;
use App\Mail\PostMail;
use App\Models\Post;
use App\Models\Section;
use App\Models\Subscriber;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class IndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->blogger()->create());
    }

    public function test_it_renders_for_a_blogger(): void
    {
        Livewire::test(Index::class)->assertOk();
    }

    public function test_it_lists_the_posts(): void
    {
        $post = Post::factory()->withMedia()->create(['heading' => 'A Listed Post']);

        Livewire::test(Index::class)
            ->assertOk()
            ->assertSee('A Listed Post');

        $this->assertSame(
            [$post->id],
            Livewire::test(Index::class)->viewData('posts')->pluck('id')->all()
        );
    }

    public function test_it_paginates_five_posts_at_a_time(): void
    {
        Post::factory()->count(7)->withMedia()->create();

        $posts = Livewire::test(Index::class)->viewData('posts');

        $this->assertCount(5, $posts->items());
        $this->assertSame(7, $posts->total());
    }

    public function test_a_plain_user_cannot_open_it(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)->assertForbidden();
    }

    /** Deleting */
    public function test_a_blogger_can_delete_a_post(): void
    {
        $post = Post::factory()->withMedia()->create();

        Livewire::test(Index::class)
            ->call('deletePost', $post)
            ->assertDispatched('post-event')
            ->assertDispatched('swal', [
                'title' => config('messages.post.destroy'),
                'icon' => 'success',
                'iconColor' => 'green',
            ]);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_deleting_a_post_removes_its_media_and_tag_links(): void
    {
        $post = Post::factory()->withMedia()->create();
        $post->tags()->attach(Tag::factory()->create());

        Livewire::test(Index::class)->call('deletePost', $post);

        $this->assertDatabaseCount('media', 0);
        $this->assertDatabaseCount('post_tags', 0);
    }

    /**
     * KNOWN BUG: SectionService::destroy() lazy-loads $section->media on
     * sections that came from the $post->sections collection. Lazy loading is
     * disabled outside production (AppServiceProvider), so the whole delete
     * throws, rolls back and surfaces only as a generic error toast — a post
     * with sections cannot be deleted in any non-production environment.
     * Eager-load sections.media in PostService::destroy() to fix, then flip
     * this test to assert the post and its sections are gone.
     */
    public function test_deleting_a_post_that_has_sections_currently_fails(): void
    {
        $post = Post::factory()->withMedia()->create();
        Section::factory()->count(2)->create(['post_id' => $post->id]);

        Livewire::test(Index::class)
            ->call('deletePost', $post)
            ->assertDispatched('swal', [
                'title' => config('messages.common.error'),
                'icon' => 'error',
                'iconColor' => 'red',
            ]);

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
        $this->assertDatabaseCount('sections', 2);
    }

    /** Featuring */
    public function test_a_blogger_can_feature_a_post(): void
    {
        $post = Post::factory()->withMedia()->create(['is_feature' => false]);

        Livewire::test(Index::class)
            ->call('toggleFeature', $post)
            ->assertDispatched('post-event');

        $this->assertTrue((bool) $post->fresh()->is_feature);
    }

    public function test_featuring_a_post_twice_puts_it_back(): void
    {
        $post = Post::factory()->withMedia()->create(['is_feature' => true]);

        Livewire::test(Index::class)->call('toggleFeature', $post);

        $this->assertFalse((bool) $post->fresh()->is_feature);
    }

    /** Announcing to subscribers */
    public function test_it_emails_active_subscribers_about_a_post(): void
    {
        Mail::fake();

        $post = Post::factory()->withMedia()->published()->create();
        Subscriber::factory()->active()->create(['email' => 'active@animefeverzone.test']);

        Livewire::test(Index::class)
            ->call('sendMailToSubscribers', $post)
            ->assertDispatched('swal', [
                'title' => config('messages.email.new_post_announce'),
                'icon' => 'success',
                'iconColor' => 'green',
            ]);

        Mail::assertSent(PostMail::class, fn ($mail) => $mail->hasTo('active@animefeverzone.test'));
    }

    public function test_it_skips_subscribers_who_have_not_confirmed(): void
    {
        Mail::fake();

        $post = Post::factory()->withMedia()->published()->create();
        Subscriber::factory()->create(['email' => 'pending@animefeverzone.test']);

        Livewire::test(Index::class)->call('sendMailToSubscribers', $post);

        Mail::assertNothingSent();
    }

    public function test_it_emails_every_active_subscriber(): void
    {
        Mail::fake();

        $post = Post::factory()->withMedia()->published()->create();
        Subscriber::factory()->count(3)->active()->create();

        Livewire::test(Index::class)->call('sendMailToSubscribers', $post);

        Mail::assertSent(PostMail::class, 3);
    }
}
