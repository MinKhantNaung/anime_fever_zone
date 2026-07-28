<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Contact;
use App\Models\Contact as ContactModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders(): void
    {
        Livewire::test(Contact::class)->assertOk();
    }

    /** Store */
    public function test_it_stores_a_contact_message(): void
    {
        Livewire::test(Contact::class)
            ->set('name', 'Sakura Haruno')
            ->set('email', 'sakura@animefeverzone.test')
            ->set('category', 'general')
            ->set('message', 'Great site!')
            ->call('sendToContact')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contacts', [
            'name' => 'Sakura Haruno',
            'email' => 'sakura@animefeverzone.test',
            'category' => 'general',
            'message' => 'Great site!',
        ]);
    }

    public function test_it_announces_success(): void
    {
        Livewire::test(Contact::class)
            ->set('name', 'Sakura Haruno')
            ->set('email', 'sakura@animefeverzone.test')
            ->set('category', 'idea')
            ->set('message', 'A suggestion.')
            ->call('sendToContact')
            ->assertDispatched('swal', [
                'title' => config('messages.contact.success'),
                'icon' => 'success',
                'iconColor' => 'green',
            ]);
    }

    public function test_it_clears_the_form_after_sending(): void
    {
        Livewire::test(Contact::class)
            ->set('name', 'Sakura Haruno')
            ->set('email', 'sakura@animefeverzone.test')
            ->set('category', 'general')
            ->set('message', 'Great site!')
            ->call('sendToContact')
            ->assertSet('name', null)
            ->assertSet('email', null)
            ->assertSet('category', null)
            ->assertSet('message', null);
    }

    /** Validation */
    public function test_every_field_is_required(): void
    {
        Livewire::test(Contact::class)
            ->call('sendToContact')
            ->assertHasErrors([
                'name' => 'required',
                'email' => 'required',
                'category' => 'required',
                'message' => 'required',
            ]);

        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_the_email_must_be_a_valid_address(): void
    {
        Livewire::test(Contact::class)
            ->set('name', 'Sakura Haruno')
            ->set('email', 'not-an-email')
            ->set('category', 'general')
            ->set('message', 'Hello')
            ->call('sendToContact')
            ->assertHasErrors(['email' => 'email']);
    }

    public function test_the_email_must_be_lowercase(): void
    {
        Livewire::test(Contact::class)
            ->set('name', 'Sakura Haruno')
            ->set('email', 'Sakura@AnimeFeverZone.test')
            ->set('category', 'general')
            ->set('message', 'Hello')
            ->call('sendToContact')
            ->assertHasErrors(['email' => 'lowercase']);
    }

    public function test_the_category_must_be_one_of_the_supported_values(): void
    {
        Livewire::test(Contact::class)
            ->set('name', 'Sakura Haruno')
            ->set('email', 'sakura@animefeverzone.test')
            ->set('category', 'complaints')
            ->set('message', 'Hello')
            ->call('sendToContact')
            ->assertHasErrors(['category' => 'in']);

        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_the_message_is_capped_at_255_characters(): void
    {
        Livewire::test(Contact::class)
            ->set('name', 'Sakura Haruno')
            ->set('email', 'sakura@animefeverzone.test')
            ->set('category', 'general')
            ->set('message', str_repeat('a', 256))
            ->call('sendToContact')
            ->assertHasErrors(['message' => 'max']);
    }

    public function test_it_accepts_every_supported_category(): void
    {
        foreach (['general', 'idea', 'issue', 'ads', 'copy'] as $category) {
            Livewire::test(Contact::class)
                ->set('name', 'Sakura Haruno')
                ->set('email', 'sakura@animefeverzone.test')
                ->set('category', $category)
                ->set('message', 'Hello')
                ->call('sendToContact')
                ->assertHasNoErrors();
        }

        $this->assertSame(5, ContactModel::count());
    }

    /** Route */
    public function test_the_contact_page_is_publicly_reachable(): void
    {
        $this->get(route('contact'))->assertOk();
    }
}
