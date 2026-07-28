<?php

namespace Tests\Feature\Livewire\Actions;

use App\Livewire\Actions\Logout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_the_current_user_out(): void
    {
        $this->actingAs(User::factory()->create());
        $this->assertAuthenticated();

        app(Logout::class)();

        $this->assertGuest();
    }

    public function test_it_regenerates_the_csrf_token(): void
    {
        $this->actingAs(User::factory()->create());

        $before = session()->token();

        app(Logout::class)();

        $this->assertNotSame($before, session()->token());
    }

    public function test_it_is_safe_to_invoke_without_a_signed_in_user(): void
    {
        app(Logout::class)();

        $this->assertGuest();
    }
}
