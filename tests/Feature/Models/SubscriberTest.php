<?php

namespace Tests\Feature\Models;

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SubscriberTest extends TestCase
{
    use RefreshDatabase;

    /** Store */
    public function test_it_can_be_created(): void
    {
        $subscriber = Subscriber::create([
            'email' => 'fan@animefeverzone.test',
            'token' => 'a-confirmation-token',
            'status' => 'Pending',
        ]);

        $this->assertDatabaseCount('subscribers', 1);
        $this->assertDatabaseHas('subscribers', [
            'id' => $subscriber->id,
            'email' => 'fan@animefeverzone.test',
            'token' => 'a-confirmation-token',
            'status' => 'Pending',
        ]);
    }

    public function test_it_defaults_to_a_pending_status(): void
    {
        $subscriber = Subscriber::create([
            'email' => 'fan@animefeverzone.test',
            'token' => Str::random(40),
        ]);

        $this->assertSame('Pending', $subscriber->fresh()->status);
    }

    public function test_it_mass_assigns_the_email_token_and_status(): void
    {
        $this->assertSame(
            ['email', 'token', 'status'],
            (new Subscriber)->getFillable()
        );
    }

    public function test_it_can_be_activated(): void
    {
        $subscriber = Subscriber::factory()->create();

        $subscriber->update(['status' => 'Active']);

        $this->assertSame('Active', $subscriber->fresh()->status);
        $this->assertDatabaseHas('subscribers', [
            'id' => $subscriber->id,
            'status' => 'Active',
        ]);
    }

    public function test_the_email_is_no_longer_unique(): void
    {
        Subscriber::factory()->count(2)->create(['email' => 'twice@animefeverzone.test']);

        $this->assertSame(2, Subscriber::where('email', 'twice@animefeverzone.test')->count());
    }

    public function test_active_subscribers_can_be_filtered(): void
    {
        Subscriber::factory()->count(2)->active()->create();
        Subscriber::factory()->create();

        $this->assertSame(2, Subscriber::where('status', 'Active')->count());
        $this->assertSame(1, Subscriber::where('status', 'Pending')->count());
    }
}
