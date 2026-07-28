<?php

namespace Tests\Feature\Livewire\Components;

use App\Livewire\Components\AiChatBox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class AiChatBoxTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_for_a_guest(): void
    {
        Livewire::test(AiChatBox::class)->assertOk();
    }

    public function test_it_renders_for_a_signed_in_user(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(AiChatBox::class)->assertOk();
    }
}
