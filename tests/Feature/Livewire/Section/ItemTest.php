<?php

namespace Tests\Feature\Livewire\Section;

use App\Livewire\Section\Item;
use App\Models\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_a_section(): void
    {
        $section = Section::factory()->create(['heading' => 'The Marineford Arc']);

        Livewire::test(Item::class, ['section' => $section])
            ->assertOk()
            ->assertSee('The Marineford Arc');
    }

    public function test_it_renders_a_section_without_a_heading(): void
    {
        $section = Section::factory()->withoutHeading()->create(['body' => 'Just a paragraph.']);

        Livewire::test(Item::class, ['section' => $section])
            ->assertOk()
            ->assertSee('Just a paragraph.');
    }

    public function test_it_eager_loads_the_sections_media(): void
    {
        $section = Section::factory()->create();
        $section->media()->create(['url' => 'media/one.webp', 'mime' => 'image']);

        $component = Livewire::test(Item::class, ['section' => $section]);

        $this->assertTrue($component->get('section')->relationLoaded('media'));
        $component->assertSeeHtml('media/one.webp');
    }

    public function test_it_renders_a_section_with_several_media_files(): void
    {
        $section = Section::factory()->create();
        $section->media()->create(['url' => 'media/one.webp', 'mime' => 'image']);
        $section->media()->create(['url' => 'media/two.webp', 'mime' => 'image']);

        Livewire::test(Item::class, ['section' => $section])
            ->assertOk()
            ->assertSeeHtml('media/one.webp')
            ->assertSeeHtml('media/two.webp');
    }
}
