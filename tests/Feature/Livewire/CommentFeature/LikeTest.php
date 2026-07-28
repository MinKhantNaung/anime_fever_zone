<?php

namespace Tests\Feature\Livewire\CommentFeature;

use App\Livewire\CommentFeature\Like;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class LikeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * HasLikes identifies a guest by IP + user agent. Livewire builds its own
     * request for component tests, which always reports these values, so
     * fixtures that should count as "this visitor" have to use them.
     */
    private const VISITOR_IP = '127.0.0.1';

    private const VISITOR_AGENT = 'Symfony';

    public function test_it_renders_with_the_current_like_count(): void
    {
        $comment = Comment::factory()->create();
        CommentLike::factory()->count(2)->create(['comment_id' => $comment->id]);

        Livewire::test(Like::class, ['comment' => Comment::find($comment->id)])
            ->assertOk()
            ->assertSet('count', 2);
    }

    public function test_a_new_comment_starts_at_zero_likes(): void
    {
        $comment = Comment::factory()->create();

        Livewire::test(Like::class, ['comment' => Comment::find($comment->id)])
            ->assertSet('count', 0);
    }

    /** Guests */
    public function test_a_guest_can_like_a_comment(): void
    {
        $comment = Comment::factory()->create();

        Livewire::test(Like::class, ['comment' => Comment::find($comment->id)])
            ->call('like')
            ->assertSet('count', 1);

        $this->assertDatabaseHas('comment_likes', [
            'comment_id' => $comment->id,
            'user_id' => null,
            'ip' => self::VISITOR_IP,
            'user_agent' => self::VISITOR_AGENT,
        ]);
    }

    public function test_a_guest_can_unlike_a_comment_they_already_liked(): void
    {
        $comment = Comment::factory()->create();
        CommentLike::factory()->guest()->create([
            'comment_id' => $comment->id,
            'ip' => self::VISITOR_IP,
            'user_agent' => self::VISITOR_AGENT,
        ]);

        Livewire::test(Like::class, ['comment' => Comment::find($comment->id)])
            ->assertSet('count', 1)
            ->call('like')
            ->assertSet('count', 0);

        $this->assertDatabaseCount('comment_likes', 0);
    }

    public function test_a_like_from_a_different_visitor_does_not_count_as_ours(): void
    {
        $comment = Comment::factory()->create();
        CommentLike::factory()->guest()->create([
            'comment_id' => $comment->id,
            'ip' => '10.0.0.9',
            'user_agent' => 'Another Browser',
        ]);

        Livewire::test(Like::class, ['comment' => Comment::find($comment->id)])
            ->call('like')
            ->assertSet('count', 2);

        $this->assertDatabaseCount('comment_likes', 2);
    }

    /** Signed-in users */
    public function test_a_signed_in_user_can_like_a_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();
        $this->actingAs($user);

        Livewire::test(Like::class, ['comment' => Comment::find($comment->id)])
            ->call('like')
            ->assertSet('count', 1);

        $this->assertDatabaseHas('comment_likes', [
            'comment_id' => $comment->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_a_signed_in_user_can_unlike_their_own_like(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();
        CommentLike::factory()->create([
            'comment_id' => $comment->id,
            'user_id' => $user->id,
        ]);
        $this->actingAs($user);

        Livewire::test(Like::class, ['comment' => Comment::find($comment->id)])
            ->assertSet('count', 1)
            ->call('like')
            ->assertSet('count', 0);

        $this->assertDatabaseCount('comment_likes', 0);
    }
}
