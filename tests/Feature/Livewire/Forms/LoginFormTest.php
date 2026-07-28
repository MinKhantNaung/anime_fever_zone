<?php

namespace Tests\Feature\Livewire\Forms;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

final class LoginFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_page_renders(): void
    {
        Livewire::test('pages.auth.login')->assertOk();
    }

    /** Validation */
    public function test_the_email_is_required(): void
    {
        Livewire::test('pages.auth.login')
            ->set('form.email', '')
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasErrors(['form.email' => 'required']);

        $this->assertGuest();
    }

    public function test_the_password_is_required(): void
    {
        Livewire::test('pages.auth.login')
            ->set('form.email', 'user@animefeverzone.test')
            ->set('form.password', '')
            ->call('login')
            ->assertHasErrors(['form.password' => 'required']);

        $this->assertGuest();
    }

    public function test_the_email_must_be_a_valid_address(): void
    {
        Livewire::test('pages.auth.login')
            ->set('form.email', 'not-an-email')
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasErrors(['form.email' => 'email']);

        $this->assertGuest();
    }

    /** Authentication */
    public function test_a_user_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        Livewire::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_user_is_redirected_home_after_authenticating(): void
    {
        $user = User::factory()->create();

        Livewire::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password')
            ->call('login')
            ->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_a_user_cannot_authenticate_with_the_wrong_password(): void
    {
        $user = User::factory()->create();

        Livewire::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password')
            ->call('login')
            ->assertHasErrors('form.email');

        $this->assertGuest();
    }

    public function test_it_reports_the_generic_failure_message_for_an_unknown_email(): void
    {
        Livewire::test('pages.auth.login')
            ->set('form.email', 'nobody@animefeverzone.test')
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasErrors(['form.email' => [trans('auth.failed')]]);

        $this->assertGuest();
    }

    public function test_a_user_can_ask_to_be_remembered(): void
    {
        $user = User::factory()->create();

        Livewire::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password')
            ->set('form.remember', true)
            ->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->remember_token);
    }

    /** Rate limiting */
    public function test_it_locks_out_after_five_failed_attempts(): void
    {
        Event::fake([Lockout::class]);

        $user = User::factory()->create();
        $component = Livewire::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password');

        foreach (range(1, 5) as $ignored) {
            $component->call('login')->assertHasErrors('form.email');
        }

        $component->call('login');

        $this->assertStringContainsString(
            'Too many login attempts',
            $component->errors()->first('form.email')
        );

        Event::assertDispatched(Lockout::class);
        $this->assertGuest();
    }

    public function test_the_throttle_counter_is_keyed_per_email(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $component = Livewire::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password');

        foreach (range(1, 5) as $ignored) {
            $component->call('login');
        }

        // A different address still has a clean slate and can sign in.
        Livewire::test('pages.auth.login')
            ->set('form.email', $other->email)
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($other);
    }

    public function test_a_successful_login_clears_the_throttle_counter(): void
    {
        $user = User::factory()->create();

        $component = Livewire::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password');

        foreach (range(1, 3) as $ignored) {
            $component->call('login');
        }

        $component->set('form.password', 'password')->call('login')->assertHasNoErrors();

        $throttleKey = str($user->email)->lower()->append('|127.0.0.1')->transliterate()->value();

        $this->assertSame(0, RateLimiter::attempts($throttleKey));
    }
}
