<?php

namespace Tests\Feature\Livewire\Components;

use App\Livewire\Components\NavBar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class NavBarTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_for_a_guest(): void
    {
        Livewire::test(NavBar::class)->assertOk();
    }

    public function test_it_renders_for_a_signed_in_user(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(NavBar::class)->assertOk();
    }

    public function test_it_renders_the_users_avatar_when_they_have_one(): void
    {
        $user = User::factory()->withMedia()->create();
        $this->actingAs($user);

        Livewire::test(NavBar::class)
            ->assertOk()
            ->assertSeeHtml($user->media->url);
    }

    public function test_logging_out_ends_the_session(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(NavBar::class)->call('logout');

        $this->assertGuest();
    }

    public function test_logging_out_redirects_home(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(NavBar::class)
            ->call('logout')
            ->assertRedirect('/');
    }
}
