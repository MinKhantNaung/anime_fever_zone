<?php

namespace Tests\Feature\Livewire\CommentFeature;

use App\Livewire\CommentFeature\Comments;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_for_a_post_with_no_comments(): void
    {
        Livewire::test(Comments::class, ['model' => Post::factory()->create()])
            ->assertOk();
    }

    public function test_it_lists_the_posts_comments(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->for($post, 'commentable')->create(['body' => 'First!']);

        Livewire::test(Comments::class, ['model' => $post])
            ->assertOk()
            ->assertSee('First!');

        $this->assertSame(
            [$comment->id],
            Livewire::test(Comments::class, ['model' => $post])->viewData('comments')->pluck('id')->all()
        );
    }

    public function test_it_ignores_comments_on_other_posts(): void
    {
        $post = Post::factory()->create();
        Comment::factory()->for(Post::factory()->create(), 'commentable')->create();

        $comments = Livewire::test(Comments::class, ['model' => $post])->viewData('comments');

        $this->assertCount(0, $comments);
    }

    public function test_it_lists_only_top_level_comments(): void
    {
        $post = Post::factory()->create();
        $parent = Comment::factory()->for($post, 'commentable')->create();
        Comment::factory()->for($post, 'commentable')->create(['parent_id' => $parent->id]);

        $comments = Livewire::test(Comments::class, ['model' => $post])->viewData('comments');

        $this->assertCount(1, $comments);
        $this->assertSame($parent->id, $comments->first()->id);
    }

    public function test_it_shows_the_newest_comments_first(): void
    {
        $post = Post::factory()->create();
        $older = Comment::factory()->for($post, 'commentable')->create(['created_at' => now()->subDay()]);
        $newer = Comment::factory()->for($post, 'commentable')->create(['created_at' => now()]);

        $comments = Livewire::test(Comments::class, ['model' => $post])->viewData('comments');

        $this->assertSame([$newer->id, $older->id], $comments->pluck('id')->all());
    }

    public function test_it_paginates_using_the_configured_page_size(): void
    {
        $post = Post::factory()->create();
        Comment::factory()->count(8)->for($post, 'commentable')->create();

        $comments = Livewire::test(Comments::class, ['model' => $post])->viewData('comments');

        $this->assertSame(config('commentify.pagination_count'), $comments->perPage());
        $this->assertCount(config('commentify.pagination_count'), $comments->items());
    }

    /** Posting */
    public function test_a_signed_in_user_can_post_a_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $this->actingAs($user);

        Livewire::test(Comments::class, ['model' => $post])
            ->set('newCommentState.body', 'Great write-up!')
            ->call('postComment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'body' => 'Great write-up!',
            'user_id' => $user->id,
            'commentable_type' => Post::class,
            'commentable_id' => $post->id,
            'parent_id' => null,
        ]);
    }

    public function test_posting_clears_the_comment_box(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Comments::class, ['model' => Post::factory()->create()])
            ->set('newCommentState.body', 'Great write-up!')
            ->call('postComment')
            ->assertSet('newCommentState.body', '')
            ->assertSet('showDropdown', false);
    }

    public function test_posting_flashes_a_confirmation_message(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Comments::class, ['model' => Post::factory()->create()])
            ->set('newCommentState.body', 'Great write-up!')
            ->call('postComment')
            ->assertSee('Comment Posted Successfully!');
    }

    public function test_the_comment_body_is_required(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Comments::class, ['model' => Post::factory()->create()])
            ->set('newCommentState.body', '')
            ->call('postComment')
            ->assertHasErrors(['newCommentState.body' => 'required']);

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_the_validation_message_calls_the_field_a_comment(): void
    {
        $this->actingAs(User::factory()->create());

        $component = Livewire::test(Comments::class, ['model' => Post::factory()->create()])
            ->set('newCommentState.body', '')
            ->call('postComment');

        $this->assertStringContainsString(
            'comment',
            $component->errors()->first('newCommentState.body')
        );
    }

    public function test_it_refreshes_when_a_comment_is_deleted_elsewhere(): void
    {
        $post = Post::factory()->create();
        Comment::factory()->for($post, 'commentable')->create();

        Livewire::test(Comments::class, ['model' => $post])
            ->dispatch('refreshComments')
            ->assertOk();
    }
}
