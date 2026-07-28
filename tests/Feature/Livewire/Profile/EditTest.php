<?php

namespace Tests\Feature\Livewire\Profile;

use App\Livewire\Profile\Edit;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class EditTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        SiteSetting::factory()->create();
        $this->user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@animefeverzone.test',
        ]);
        $this->actingAs($this->user);
    }

    /** Mount */
    public function test_it_preloads_the_signed_in_users_details(): void
    {
        Livewire::test(Edit::class)
            ->assertOk()
            ->assertSet('name', 'Original Name')
            ->assertSet('email', 'original@animefeverzone.test');
    }

    public function test_it_reflects_the_current_email_verification_setting(): void
    {
        SiteSetting::first()->update(['email_verify_status' => true]);

        Livewire::test(Edit::class)->assertSet('checked', true);
    }

    public function test_it_defaults_the_toggle_to_off_when_no_setting_row_exists(): void
    {
        SiteSetting::query()->delete();

        Livewire::test(Edit::class)->assertSet('checked', false);
    }

    /** Saving */
    public function test_a_user_can_update_their_name_and_email(): void
    {
        Livewire::test(Edit::class)
            ->set('name', 'Revised Name')
            ->set('email', 'revised@animefeverzone.test')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Revised Name',
            'email' => 'revised@animefeverzone.test',
        ]);
    }

    public function test_changing_the_email_resets_the_verification_timestamp(): void
    {
        $this->assertNotNull($this->user->email_verified_at);

        Livewire::test(Edit::class)
            ->set('email', 'revised@animefeverzone.test')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $this->assertNull($this->user->fresh()->email_verified_at);
    }

    public function test_keeping_the_same_email_leaves_verification_intact(): void
    {
        Livewire::test(Edit::class)
            ->set('name', 'Revised Name')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $this->assertNotNull($this->user->fresh()->email_verified_at);
    }

    public function test_it_announces_success_and_asks_the_page_to_reload(): void
    {
        Livewire::test(Edit::class)
            ->set('name', 'Revised Name')
            ->call('saveProfile')
            ->assertDispatched('profile-reload')
            ->assertDispatched('swal', [
                'title' => config('messages.profile.update'),
                'icon' => 'success',
                'iconColor' => 'green',
            ]);
    }

    /** Avatar */
    public function test_a_user_can_upload_an_avatar(): void
    {
        Livewire::test(Edit::class)
            ->set('media', UploadedFile::fake()->image('avatar.webp'))
            ->call('saveProfile')
            ->assertHasNoErrors();

        $this->assertNotNull($this->user->fresh()->media);
        $this->assertCount(1, Storage::disk('public')->files('media'));
    }

    public function test_uploading_a_new_avatar_replaces_the_old_one(): void
    {
        $user = User::factory()->withMedia()->create();
        $this->actingAs($user);
        $originalMediaId = $user->media->id;

        Livewire::test(Edit::class)
            ->set('media', UploadedFile::fake()->image('avatar.webp'))
            ->call('saveProfile')
            ->assertHasNoErrors();

        $this->assertNotSame($originalMediaId, $user->fresh()->media->id);
        $this->assertDatabaseMissing('media', ['id' => $originalMediaId]);
    }

    public function test_saving_without_an_upload_keeps_the_existing_avatar(): void
    {
        $user = User::factory()->withMedia()->create();
        $this->actingAs($user);
        $originalMediaId = $user->media->id;

        Livewire::test(Edit::class)
            ->set('name', 'Revised Name')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $this->assertSame($originalMediaId, $user->fresh()->media->id);
    }

    /** Validation */
    public function test_the_name_and_email_are_required(): void
    {
        Livewire::test(Edit::class)
            ->set('name', '')
            ->set('email', '')
            ->call('saveProfile')
            ->assertHasErrors(['name' => 'required', 'email' => 'required']);

        $this->assertSame('Original Name', $this->user->fresh()->name);
    }

    public function test_the_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'taken@animefeverzone.test']);

        Livewire::test(Edit::class)
            ->set('email', 'taken@animefeverzone.test')
            ->call('saveProfile')
            ->assertHasErrors(['email' => 'unique']);
    }

    public function test_a_user_may_keep_their_own_email(): void
    {
        Livewire::test(Edit::class)
            ->set('email', 'original@animefeverzone.test')
            ->call('saveProfile')
            ->assertHasNoErrors();
    }

    public function test_the_email_must_be_lowercase(): void
    {
        Livewire::test(Edit::class)
            ->set('email', 'Revised@AnimeFeverZone.test')
            ->call('saveProfile')
            ->assertHasErrors(['email' => 'lowercase']);
    }

    public function test_the_avatar_must_be_a_webp(): void
    {
        Livewire::test(Edit::class)
            ->set('media', UploadedFile::fake()->image('avatar.jpg'))
            ->call('saveProfile')
            ->assertHasErrors(['media' => 'mimes']);
    }

    public function test_the_avatar_is_capped_at_five_megabytes(): void
    {
        Livewire::test(Edit::class)
            ->set('media', UploadedFile::fake()->image('avatar.webp')->size(5121))
            ->call('saveProfile')
            ->assertHasErrors(['media' => 'max']);
    }

    /** Email verification toggle */
    public function test_it_can_turn_email_verification_on(): void
    {
        Livewire::test(Edit::class)
            ->set('checked', true)
            ->call('isChecked')
            ->assertDispatched('swal', [
                'title' => config('messages.email.verify_toggle'),
                'icon' => 'success',
                'iconColor' => 'green',
            ]);

        $this->assertTrue((bool) SiteSetting::first()->email_verify_status);
    }

    public function test_it_can_turn_email_verification_off(): void
    {
        SiteSetting::first()->update(['email_verify_status' => true]);

        Livewire::test(Edit::class)
            ->set('checked', false)
            ->call('isChecked');

        $this->assertFalse((bool) SiteSetting::first()->email_verify_status);
    }

    /** Route */
    public function test_a_guest_is_redirected_to_login(): void
    {
        auth()->logout();

        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }
}
