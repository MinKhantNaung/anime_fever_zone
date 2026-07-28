<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Info\About;
use App\Livewire\Offline;
use App\Livewire\Privacy;
use App\Livewire\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class StaticPagesTest extends TestCase
{
    use RefreshDatabase;

    /** Components render on their own */
    public function test_the_about_component_renders(): void
    {
        Livewire::test(About::class)->assertOk();
    }

    public function test_the_privacy_component_renders(): void
    {
        Livewire::test(Privacy::class)->assertOk();
    }

    public function test_the_terms_component_renders(): void
    {
        Livewire::test(Term::class)->assertOk();
    }

    public function test_the_offline_component_renders(): void
    {
        Livewire::test(Offline::class)->assertOk();
    }

    /** Full pages, where the layout applies the title */
    public function test_the_about_page_is_public_and_titled(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('About | Anime Fever Zone');
    }

    public function test_the_privacy_page_is_public_and_titled(): void
    {
        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('Privacy Policy | Anime Fever Zone');
    }

    public function test_the_terms_page_is_public_and_titled(): void
    {
        $this->get(route('term'))
            ->assertOk()
            ->assertSee('Terms & Conditions | Anime Fever Zone');
    }
}
