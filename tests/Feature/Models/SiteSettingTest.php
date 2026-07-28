<?php

namespace Tests\Feature\Models;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SiteSettingTest extends TestCase
{
    use RefreshDatabase;

    /** Store */
    public function test_it_can_be_created(): void
    {
        $setting = SiteSetting::create(['email_verify_status' => true]);

        $this->assertDatabaseCount('site_settings', 1);
        $this->assertDatabaseHas('site_settings', [
            'id' => $setting->id,
            'email_verify_status' => true,
        ]);
    }

    public function test_email_verification_is_off_by_default(): void
    {
        $setting = SiteSetting::create([]);

        $this->assertFalse((bool) $setting->fresh()->email_verify_status);
    }

    public function test_it_mass_assigns_the_email_verify_status(): void
    {
        $this->assertSame(['email_verify_status'], (new SiteSetting)->getFillable());
    }

    public function test_email_verification_can_be_toggled(): void
    {
        $setting = SiteSetting::factory()->create();

        $setting->update(['email_verify_status' => true]);
        $this->assertTrue((bool) $setting->fresh()->email_verify_status);

        $setting->update(['email_verify_status' => false]);
        $this->assertFalse((bool) $setting->fresh()->email_verify_status);
    }
}
