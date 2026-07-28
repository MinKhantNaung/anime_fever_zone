<?php

namespace Tests\Feature\Livewire\Components;

use App\Livewire\Components\TopicNav;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class TopicNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_with_no_topics(): void
    {
        Livewire::test(TopicNav::class)
            ->assertOk()
            ->assertCount('topics', 0);
    }

    public function test_it_loads_every_topic_on_mount(): void
    {
        Topic::factory()->count(3)->create();

        Livewire::test(TopicNav::class)
            ->assertOk()
            ->assertCount('topics', 3);
    }

    public function test_it_lists_each_topic_by_name(): void
    {
        $topic = Topic::factory()->create(['name' => 'Seasonal Picks']);

        Livewire::test(TopicNav::class)
            ->assertOk()
            ->assertSee($topic->name);
    }

    public function test_it_only_selects_the_columns_the_nav_needs(): void
    {
        Topic::factory()->create();

        $topics = Livewire::test(TopicNav::class)->get('topics');

        $this->assertSame(['id', 'name', 'slug'], array_keys($topics->first()->getAttributes()));
    }
}
