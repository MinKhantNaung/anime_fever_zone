<?php

namespace Tests\Feature\Livewire\Profile;

use App\Livewire\Profile\UpdatePasswordForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

final class UpdatePasswordFormTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_it_renders(): void
    {
        Livewire::test(UpdatePasswordForm::class)
            ->assertOk()
            ->assertSet('current_password', '')
            ->assertSet('password', '');
    }

    /** Updating */
    public function test_a_user_can_change_their_password(): void
    {
        Livewire::test(UpdatePasswordForm::class)
            ->set('current_password', 'password')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('new-password-123', $this->user->fresh()->password));
    }

    public function test_it_clears_the_form_after_a_successful_change(): void
    {
        Livewire::test(UpdatePasswordForm::class)
            ->set('current_password', 'password')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('updatePassword')
            ->assertSet('current_password', '')
            ->assertSet('password', '')
            ->assertSet('password_confirmation', '');
    }

    public function test_it_announces_success(): void
    {
        Livewire::test(UpdatePasswordForm::class)
            ->set('current_password', 'password')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('updatePassword')
            ->assertDispatched('password-updated')
            ->assertDispatched('swal', [
                'title' => config('messages.password.update'),
                'icon' => 'success',
                'iconColor' => 'green',
            ]);
    }

    /** Validation */
    public function test_the_current_password_must_be_correct(): void
    {
        Livewire::test(UpdatePasswordForm::class)
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('updatePassword')
            ->assertHasErrors(['current_password' => 'current_password']);

        $this->assertTrue(Hash::check('password', $this->user->fresh()->password));
    }

    public function test_the_current_password_is_required(): void
    {
        Livewire::test(UpdatePasswordForm::class)
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('updatePassword')
            ->assertHasErrors(['current_password' => 'required']);
    }

    public function test_the_new_password_is_required(): void
    {
        Livewire::test(UpdatePasswordForm::class)
            ->set('current_password', 'password')
            ->call('updatePassword')
            ->assertHasErrors(['password' => 'required']);
    }

    public function test_the_new_password_must_be_confirmed(): void
    {
        Livewire::test(UpdatePasswordForm::class)
            ->set('current_password', 'password')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'does-not-match')
            ->call('updatePassword')
            ->assertHasErrors(['password' => 'confirmed']);

        $this->assertTrue(Hash::check('password', $this->user->fresh()->password));
    }

    public function test_the_new_password_must_meet_the_minimum_length(): void
    {
        Livewire::test(UpdatePasswordForm::class)
            ->set('current_password', 'password')
            ->set('password', 'short')
            ->set('password_confirmation', 'short')
            ->call('updatePassword')
            ->assertHasErrors(['password']);
    }

    public function test_a_failed_attempt_clears_the_password_fields(): void
    {
        Livewire::test(UpdatePasswordForm::class)
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('updatePassword')
            ->assertSet('current_password', '')
            ->assertSet('password', '')
            ->assertSet('password_confirmation', '');
    }
}
