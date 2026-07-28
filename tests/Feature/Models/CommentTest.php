<?php

namespace Tests\Feature\Models;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Post;
use App\Models\Presenters\CommentPresenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CommentTest extends TestCase
{
    use RefreshDatabase;

    /** Store */
    public function test_it_can_be_created(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $comment = Comment::factory()->for($post, 'commentable')->create([
            'user_id' => $user->id,
            'body' => 'Best episode of the season.',
        ]);

        $this->assertDatabaseCount('comments', 1);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'user_id' => $user->id,
            'body' => 'Best episode of the season.',
            'commentable_type' => Post::class,
            'commentable_id' => $post->id,
            'parent_id' => null,
        ]);
    }

    public function test_it_only_mass_assigns_the_body(): void
    {
        $this->assertSame(['body'], (new Comment)->getFillable());
    }

    public function test_it_can_be_created_as_a_reply(): void
    {
        $parent = Comment::factory()->create();

        $reply = Comment::factory()->create(['parent_id' => $parent->id]);

        $this->assertDatabaseHas('comments', [
            'id' => $reply->id,
            'parent_id' => $parent->id,
        ]);
    }

    /** Relationships */
    public function test_it_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $comment->user);
        $this->assertSame($user->id, $comment->user->id);
    }

    public function test_it_has_many_children(): void
    {
        $parent = Comment::factory()->create();
        Comment::factory()->count(2)->create(['parent_id' => $parent->id]);
        Comment::factory()->create();

        $this->assertCount(2, $parent->children);
        $this->assertInstanceOf(Comment::class, $parent->children->first());
    }

    public function test_its_children_are_ordered_oldest_first(): void
    {
        $parent = Comment::factory()->create();

        $newer = Comment::factory()->create([
            'parent_id' => $parent->id,
            'created_at' => now()->subMinute(),
        ]);
        $older = Comment::factory()->create([
            'parent_id' => $parent->id,
            'created_at' => now()->subHour(),
        ]);

        $this->assertSame([$older->id, $newer->id], $parent->children->pluck('id')->all());
    }

    public function test_it_morphs_to_a_commentable(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->for($post, 'commentable')->create();

        $this->assertInstanceOf(Post::class, $comment->commentable);
        $this->assertSame($post->id, $comment->commentable->id);
    }

    public function test_it_has_many_likes(): void
    {
        $comment = Comment::factory()->create();

        CommentLike::factory()->count(3)->create(['comment_id' => $comment->id]);
        CommentLike::factory()->create();

        $this->assertCount(3, $comment->likes);
        $this->assertInstanceOf(CommentLike::class, $comment->likes->first());
    }

    public function test_it_eager_loads_a_likes_count(): void
    {
        $comment = Comment::factory()->create();
        CommentLike::factory()->count(2)->create(['comment_id' => $comment->id]);

        $this->assertSame(2, Comment::find($comment->id)->likes_count);
    }

    public function test_deleting_a_comment_cascades_to_its_replies(): void
    {
        $parent = Comment::factory()->create();
        Comment::factory()->count(2)->create(['parent_id' => $parent->id]);

        $parent->delete();

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_deleting_a_user_cascades_to_their_comments(): void
    {
        $user = User::factory()->create();
        Comment::factory()->create(['user_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseCount('comments', 0);
    }

    /** Behaviour */
    public function test_a_top_level_comment_is_a_parent(): void
    {
        $this->assertTrue(Comment::factory()->create()->isParent());
    }

    public function test_a_reply_is_not_a_parent(): void
    {
        $parent = Comment::factory()->create();
        $reply = Comment::factory()->create(['parent_id' => $parent->id]);

        $this->assertFalse($reply->isParent());
    }

    public function test_the_parent_scope_excludes_replies(): void
    {
        $parent = Comment::factory()->create();
        Comment::factory()->create(['parent_id' => $parent->id]);

        $results = Comment::parent()->get();

        $this->assertCount(1, $results);
        $this->assertSame($parent->id, $results->first()->id);
    }

    public function test_it_exposes_a_presenter(): void
    {
        $comment = Comment::factory()->create();

        $this->assertInstanceOf(CommentPresenter::class, $comment->presenter());
        $this->assertSame($comment->id, $comment->presenter()->comment->id);
    }

    public function test_the_presenter_renders_the_body_as_markdown(): void
    {
        $comment = Comment::factory()->create(['body' => 'This is **bold**.']);

        $this->assertStringContainsString(
            '<strong>bold</strong>',
            (string) $comment->presenter()->markdownBody()
        );
    }

    public function test_the_presenter_strips_unsafe_html_from_the_body(): void
    {
        $comment = Comment::factory()->create(['body' => 'Hi <script>alert(1)</script>']);

        $html = (string) $comment->presenter()->markdownBody();

        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_the_presenter_returns_a_relative_created_at(): void
    {
        $comment = Comment::factory()->create(['created_at' => now()->subHours(3)]);

        $this->assertSame('3 hours ago', $comment->presenter()->relativeCreatedAt());
    }

    public function test_the_presenter_links_mentions_of_known_users(): void
    {
        User::factory()->create(['name' => 'luffy']);
        $comment = Comment::factory()->create();

        $this->assertStringContainsString(
            '<a href="/users/luffy">@luffy</a>',
            $comment->presenter()->replaceUserMentions('hello @luffy')
        );
    }

    public function test_the_presenter_leaves_mentions_of_unknown_users_alone(): void
    {
        $comment = Comment::factory()->create();

        $this->assertSame(
            'hello @nobody',
            $comment->presenter()->replaceUserMentions('hello @nobody')
        );
    }

    /** HasLikes trait */
    public function test_a_guest_like_is_detected_by_ip_and_user_agent(): void
    {
        $this->fakeVisitor('10.0.0.1', 'PHPUnit Browser');

        $comment = Comment::factory()->create();
        CommentLike::factory()->guest()->create([
            'comment_id' => $comment->id,
            'ip' => '10.0.0.1',
            'user_agent' => 'PHPUnit Browser',
        ]);

        $this->assertSame(1, $comment->isLiked());
    }

    public function test_a_like_from_a_different_visitor_is_not_detected(): void
    {
        $this->fakeVisitor('10.0.0.1', 'PHPUnit Browser');

        $comment = Comment::factory()->create();
        CommentLike::factory()->guest()->create([
            'comment_id' => $comment->id,
            'ip' => '10.0.0.2',
            'user_agent' => 'Another Browser',
        ]);

        $this->assertSame(0, $comment->isLiked());
    }

    public function test_a_guest_can_remove_their_own_like(): void
    {
        $this->fakeVisitor('10.0.0.1', 'PHPUnit Browser');

        $comment = Comment::factory()->create();
        CommentLike::factory()->guest()->create([
            'comment_id' => $comment->id,
            'ip' => '10.0.0.1',
            'user_agent' => 'PHPUnit Browser',
        ]);

        $this->assertTrue((bool) $comment->removeLike());
        $this->assertDatabaseCount('comment_likes', 0);
    }

    public function test_removing_a_like_leaves_other_visitors_likes_untouched(): void
    {
        $this->fakeVisitor('10.0.0.1', 'PHPUnit Browser');

        $comment = Comment::factory()->create();
        CommentLike::factory()->guest()->create([
            'comment_id' => $comment->id,
            'ip' => '10.0.0.1',
            'user_agent' => 'PHPUnit Browser',
        ]);
        CommentLike::factory()->guest()->create([
            'comment_id' => $comment->id,
            'ip' => '10.0.0.9',
            'user_agent' => 'Another Browser',
        ]);

        $comment->removeLike();

        $this->assertDatabaseCount('comment_likes', 1);
        $this->assertDatabaseHas('comment_likes', ['ip' => '10.0.0.9']);
    }

    public function test_an_authenticated_user_can_remove_their_own_like(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $comment = Comment::factory()->create();
        CommentLike::factory()->create([
            'comment_id' => $comment->id,
            'user_id' => $user->id,
        ]);

        $this->assertTrue((bool) $comment->removeLike());
        $this->assertDatabaseCount('comment_likes', 0);
    }

    /**
     * Point the current request at a specific visitor so the HasLikes trait,
     * which reads request()->ip() and request()->userAgent(), has something
     * to match on.
     */
    private function fakeVisitor(string $ip, string $userAgent): void
    {
        request()->server->set('REMOTE_ADDR', $ip);
        request()->headers->set('User-Agent', $userAgent);
    }
}
