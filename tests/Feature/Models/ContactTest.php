<?php

namespace Tests\Feature\Models;

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ContactTest extends TestCase
{
    use RefreshDatabase;

    /** Store */
    public function test_it_can_be_created(): void
    {
        $contact = Contact::create([
            'name' => 'Sakura Haruno',
            'email' => 'sakura@animefeverzone.test',
            'category' => 'general',
            'message' => 'Love the site!',
        ]);

        $this->assertDatabaseCount('contacts', 1);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'name' => 'Sakura Haruno',
            'email' => 'sakura@animefeverzone.test',
            'category' => 'general',
            'message' => 'Love the site!',
        ]);
    }

    public function test_it_mass_assigns_the_name_email_category_and_message(): void
    {
        $this->assertSame(
            ['name', 'email', 'category', 'message'],
            (new Contact)->getFillable()
        );
    }

    #[DataProvider('categories')]
    public function test_it_accepts_every_supported_category(string $category): void
    {
        $contact = Contact::factory()->create(['category' => $category]);

        $this->assertSame($category, $contact->fresh()->category);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function categories(): array
    {
        return [
            'general' => ['general'],
            'idea' => ['idea'],
            'issue' => ['issue'],
            'ads' => ['ads'],
            'copy' => ['copy'],
        ];
    }

    public function test_it_records_timestamps(): void
    {
        $contact = Contact::factory()->create();

        $this->assertNotNull($contact->created_at);
        $this->assertNotNull($contact->updated_at);
    }

    public function test_many_messages_can_come_from_the_same_email(): void
    {
        Contact::factory()->count(2)->create(['email' => 'repeat@animefeverzone.test']);

        $this->assertSame(2, Contact::where('email', 'repeat@animefeverzone.test')->count());
    }
}
