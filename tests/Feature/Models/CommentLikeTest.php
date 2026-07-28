<?php

namespace Tests\Feature\Models;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CommentLikeTest extends TestCase
{
    use RefreshDatabase;

    /** Store */
    public function test_it_can_be_created(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $like = CommentLike::factory()->create([
            'user_id' => $user->id,
            'comment_id' => $comment->id,
            'ip' => '192.168.1.10',
            'user_agent' => 'PHPUnit Browser',
        ]);

        $this->assertDatabaseCount('comment_likes', 1);
        $this->assertDatabaseHas('comment_likes', [
            'id' => $like->id,
            'user_id' => $user->id,
            'comment_id' => $comment->id,
            'ip' => '192.168.1.10',
            'user_agent' => 'PHPUnit Browser',
        ]);
    }

    public function test_it_can_be_created_by_a_guest(): void
    {
        $like = CommentLike::factory()->guest()->create();

        $this->assertNull($like->user_id);
        $this->assertDatabaseHas('comment_likes', [
            'id' => $like->id,
            'user_id' => null,
        ]);
    }

    public function test_it_mass_assigns_the_user_ip_and_user_agent(): void
    {
        $this->assertSame(
            ['user_id', 'ip', 'user_agent'],
            (new CommentLike)->getFillable()
        );
    }

    /** Scopes */
    public function test_the_for_ip_scope_filters_by_ip(): void
    {
        CommentLike::factory()->create(['ip' => '10.0.0.1']);
        CommentLike::factory()->create(['ip' => '10.0.0.2']);

        $results = CommentLike::forIp('10.0.0.1')->get();

        $this->assertCount(1, $results);
        $this->assertSame('10.0.0.1', $results->first()->ip);
    }

    public function test_the_for_user_agent_scope_filters_by_user_agent(): void
    {
        CommentLike::factory()->create(['user_agent' => 'Firefox']);
        CommentLike::factory()->create(['user_agent' => 'Chrome']);

        $results = CommentLike::forUserAgent('Firefox')->get();

        $this->assertCount(1, $results);
        $this->assertSame('Firefox', $results->first()->user_agent);
    }

    public function test_the_scopes_can_be_combined_to_identify_one_visitor(): void
    {
        $match = CommentLike::factory()->create([
            'ip' => '10.0.0.1',
            'user_agent' => 'Firefox',
        ]);
        CommentLike::factory()->create(['ip' => '10.0.0.1', 'user_agent' => 'Chrome']);
        CommentLike::factory()->create(['ip' => '10.0.0.2', 'user_agent' => 'Firefox']);

        $results = CommentLike::forIp('10.0.0.1')->forUserAgent('Firefox')->get();

        $this->assertCount(1, $results);
        $this->assertSame($match->id, $results->first()->id);
    }

    public function test_the_for_ip_scope_returns_nothing_for_an_unknown_ip(): void
    {
        CommentLike::factory()->create(['ip' => '10.0.0.1']);

        $this->assertCount(0, CommentLike::forIp('203.0.113.1')->get());
    }
}
