<?php

namespace Tests\Feature\Livewire\Topic;

use App\Livewire\Topic\Create;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->blogger()->create());
    }

    public function test_it_renders_for_a_blogger(): void
    {
        Livewire::test(Create::class)
            ->assertOk()
            ->assertSet('editMode', false);
    }

    public function test_it_lists_the_existing_topics(): void
    {
        Topic::factory()->create(['name' => 'Anime News']);

        $component = Livewire::test(Create::class);

        $this->assertCount(1, $component->viewData('topics'));
        $component->assertSee('Anime News');
    }

    public function test_a_plain_user_cannot_open_it(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Create::class)->assertForbidden();
    }

    /** Creating */
    public function test_a_blogger_can_create_a_topic(): void
    {
        Livewire::test(Create::class)
            ->set('name', 'Anime News')
            ->call('createNew')
            ->assertHasNoErrors()
            ->assertDispatched('topic-created')
            ->assertDispatched('swal');

        $this->assertDatabaseHas('topics', [
            'name' => 'Anime News',
            'slug' => 'anime-news',
        ]);
    }

    public function test_the_name_is_required(): void
    {
        Livewire::test(Create::class)
            ->set('name', '')
            ->call('createNew')
            ->assertHasErrors(['name' => 'required']);

        $this->assertDatabaseCount('topics', 0);
    }

    public function test_the_name_must_be_unique(): void
    {
        Topic::factory()->create(['name' => 'Anime News']);

        Livewire::test(Create::class)
            ->set('name', 'Anime News')
            ->call('createNew')
            ->assertHasErrors(['name' => 'unique']);

        $this->assertSame(1, Topic::count());
    }

    public function test_the_name_is_capped_at_255_characters(): void
    {
        Livewire::test(Create::class)
            ->set('name', str_repeat('a', 256))
            ->call('createNew')
            ->assertHasErrors(['name' => 'max']);
    }

    /** Editing */
    public function test_selecting_a_topic_switches_into_edit_mode(): void
    {
        $topic = Topic::factory()->create(['name' => 'Anime News']);

        Livewire::test(Create::class)
            ->call('updateEditMode', $topic)
            ->assertSet('editMode', true)
            ->assertSet('name', 'Anime News')
            ->assertSet('topic.id', $topic->id);
    }

    public function test_a_blogger_can_rename_a_topic(): void
    {
        $topic = Topic::factory()->create(['name' => 'Anime News']);

        Livewire::test(Create::class)
            ->call('updateEditMode', $topic)
            ->set('name', 'Manga News')
            ->call('createNew')
            ->assertHasNoErrors()
            ->assertDispatched('topic-created');

        $this->assertDatabaseHas('topics', [
            'id' => $topic->id,
            'name' => 'Manga News',
            'slug' => 'manga-news',
        ]);
        $this->assertSame(1, Topic::count());
    }

    public function test_a_topic_may_keep_its_own_name_while_editing(): void
    {
        $topic = Topic::factory()->create(['name' => 'Anime News']);

        Livewire::test(Create::class)
            ->call('updateEditMode', $topic)
            ->call('createNew')
            ->assertHasNoErrors();
    }

    public function test_renaming_to_another_topics_name_is_rejected(): void
    {
        $topic = Topic::factory()->create(['name' => 'Anime News']);
        Topic::factory()->create(['name' => 'Manga News']);

        Livewire::test(Create::class)
            ->call('updateEditMode', $topic)
            ->set('name', 'Manga News')
            ->call('createNew')
            ->assertHasErrors(['name' => 'unique']);

        $this->assertSame('Anime News', $topic->fresh()->name);
    }

    public function test_saving_leaves_edit_mode(): void
    {
        $topic = Topic::factory()->create(['name' => 'Anime News']);

        Livewire::test(Create::class)
            ->call('updateEditMode', $topic)
            ->set('name', 'Manga News')
            ->call('createNew')
            ->assertSet('editMode', false)
            ->assertSet('name', null);
    }

    /** Deleting */
    public function test_a_blogger_can_delete_a_topic(): void
    {
        $topic = Topic::factory()->create();

        Livewire::test(Create::class)
            ->call('deleteTopic', $topic)
            ->assertDispatched('topic-created')
            ->assertDispatched('swal');

        $this->assertDatabaseMissing('topics', ['id' => $topic->id]);
    }

    public function test_deleting_a_topic_clears_edit_mode(): void
    {
        $topic = Topic::factory()->create();

        Livewire::test(Create::class)
            ->call('updateEditMode', $topic)
            ->call('deleteTopic', $topic)
            ->assertSet('editMode', false)
            ->assertSet('name', null)
            ->assertSet('topic', null);
    }

    public function test_deleting_a_topic_cascades_to_its_posts(): void
    {
        $topic = Topic::factory()->create();
        Post::factory()->count(2)->for($topic)->create();

        Livewire::test(Create::class)->call('deleteTopic', $topic);

        $this->assertDatabaseCount('posts', 0);
    }
}
