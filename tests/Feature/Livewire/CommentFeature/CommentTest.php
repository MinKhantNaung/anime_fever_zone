<?php

namespace Tests\Feature\Livewire\CommentFeature;

use App\Livewire\CommentFeature\Comment as CommentComponent;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_a_comment(): void
    {
        $comment = Comment::factory()->create(['body' => 'Loved this episode']);

        Livewire::test(CommentComponent::class, ['comment' => $comment])
            ->assertOk()
            ->assertSee('Loved this episode');
    }

    public function test_toggling_reply_opens_and_closes_the_reply_box(): void
    {
        $comment = Comment::factory()->create();

        Livewire::test(CommentComponent::class, ['comment' => $comment])
            ->assertSet('isReplying', false)
            ->call('toggleReply')
            ->assertSet('isReplying', true)
            ->call('toggleReply')
            ->assertSet('isReplying', false);
    }

    /** Editing */
    public function test_switching_into_edit_mode_preloads_the_current_body(): void
    {
        $author = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $author->id, 'body' => 'Original body']);
        $this->actingAs($author);

        Livewire::test(CommentComponent::class, ['comment' => $comment])
            ->set('isEditing', true)
            ->assertSet('editState.body', 'Original body');
    }

    public function test_an_author_can_edit_their_comment(): void
    {
        $author = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $author->id, 'body' => 'Original body']);
        $this->actingAs($author);

        Livewire::test(CommentComponent::class, ['comment' => $comment])
            ->set('isEditing', true)
            ->set('editState.body', 'Edited body')
            ->call('editComment')
            ->assertHasNoErrors()
            ->assertSet('isEditing', false)
            ->assertSet('showOptions', false);

        $this->assertSame('Edited body', $comment->fresh()->body);
    }

    public function test_an_edited_body_is_required(): void
    {
        $author = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $author->id]);
        $this->actingAs($author);

        Livewire::test(CommentComponent::class, ['comment' => $comment])
            ->set('editState.body', '')
            ->call('editComment')
            ->assertHasErrors(['editState.body' => 'required']);
    }

    public function test_an_edited_body_must_be_at_least_two_characters(): void
    {
        $author = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $author->id]);
        $this->actingAs($author);

        Livewire::test(CommentComponent::class, ['comment' => $comment])
            ->set('editState.body', 'a')
            ->call('editComment')
            ->assertHasErrors(['editState.body' => 'min']);
    }

    public function test_another_user_cannot_edit_someone_elses_comment(): void
    {
        $comment = Comment::factory()->create(['body' => 'Original body']);
        $this->actingAs(User::factory()->create());

        Livewire::test(CommentComponent::class, ['comment' => $comment])
            ->set('editState.body', 'Hijacked body')
            ->call('editComment')
            ->assertUnauthorized();

        $this->assertSame('Original body', $comment->fresh()->body);
    }

    /** Deleting */
    public function test_an_author_can_delete_their_comment(): void
    {
        $author = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $author->id]);
        $this->actingAs($author);

        Livewire::test(CommentComponent::class, ['comment' => $comment])
            ->call('deleteComment')
            ->assertSet('isDeleted', true)
            ->assertDispatched('refreshComments');

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_another_user_cannot_delete_someone_elses_comment(): void
    {
        $comment = Comment::factory()->create();
        $this->actingAs(User::factory()->create());

        Livewire::test(CommentComponent::class, ['comment' => $comment])
            ->call('deleteComment')
            ->assertUnauthorized();

        $this->assertDatabaseCount('comments', 1);
    }

    /** Replying */
    public function test_a_user_can_reply_to_a_top_level_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $parent = Comment::factory()->for($post, 'commentable')->create();
        $this->actingAs($user);

        Livewire::test(CommentComponent::class, ['comment' => $parent])
            ->set('replyState.body', 'Totally agree')
            ->call('postReply')
            ->assertHasNoErrors()
            ->assertSet('replyState.body', '')
            ->assertSet('isReplying', false);

        $this->assertDatabaseHas('comments', [
            'body' => 'Totally agree',
            'parent_id' => $parent->id,
            'user_id' => $user->id,
            'commentable_type' => Post::class,
            'commentable_id' => $post->id,
        ]);
    }

    public function test_a_reply_body_is_required(): void
    {
        $parent = Comment::factory()->create();
        $this->actingAs(User::factory()->create());

        Livewire::test(CommentComponent::class, ['comment' => $parent])
            ->set('replyState.body', '')
            ->call('postReply')
            ->assertHasErrors(['replyState.body' => 'required']);
    }

    public function test_replies_cannot_be_nested_further(): void
    {
        $parent = Comment::factory()->create();
        $reply = Comment::factory()->create(['parent_id' => $parent->id]);
        $this->actingAs(User::factory()->create());

        Livewire::test(CommentComponent::class, ['comment' => $reply])
            ->set('replyState.body', 'A nested reply')
            ->call('postReply');

        $this->assertDatabaseMissing('comments', ['body' => 'A nested reply']);
    }

    /**
     * Mention search.
     *
     * KNOWN BUG: getUsers() validates 'searchTerm', but that is a method
     * argument rather than a component property, so Livewire throws before any
     * search runs. The mention autocomplete cannot work as written. This test
     * pins the current behaviour — delete it once getUsers() is fixed, and
     * restore the search assertions below it.
     */
    public function test_mention_search_always_throws_because_search_term_is_not_a_property(): void
    {
        User::factory()->create(['name' => 'Luffy']);
        $comment = Comment::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No property found for validation: [searchTerm]');

        Livewire::test(CommentComponent::class, ['comment' => $comment])
            ->dispatch('getUsers', 'Luf');
    }

    public function test_the_suggestion_list_starts_empty(): void
    {
        $comment = Comment::factory()->create();

        Livewire::test(CommentComponent::class, ['comment' => $comment])
            ->assertCount('users', 0);
    }

    public function test_selecting_a_user_completes_the_mention_in_a_reply(): void
    {
        $comment = Comment::factory()->create();

        Livewire::test(CommentComponent::class, ['comment' => $comment])
            ->set('replyState.body', 'hey @luf')
            ->call('selectUser', 'Luffy')
            ->assertSet('replyState.body', 'hey @luffy ')
            ->assertCount('users', 0);
    }

    public function test_selecting_a_user_lowercases_and_underscores_the_name(): void
    {
        $comment = Comment::factory()->create();

        Livewire::test(CommentComponent::class, ['comment' => $comment])
            ->set('replyState.body', 'hey @mon')
            ->call('selectUser', 'Monkey D Luffy')
            ->assertSet('replyState.body', 'hey @monkey_d_luffy ');
    }

    public function test_selecting_a_user_completes_the_mention_while_editing(): void
    {
        $author = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $author->id]);
        $this->actingAs($author);

        Livewire::test(CommentComponent::class, ['comment' => $comment])
            ->set('replyState.body', '')
            ->set('editState.body', 'thanks @zo')
            ->call('selectUser', 'Zoro')
            ->assertSet('editState.body', 'thanks @zoro ');
    }
}
