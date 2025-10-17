<?php

namespace App\Services;

use App\Models\Contact;

final class ContactService
{
    public function __construct(protected Contact $contact) {}

    public function store(array $validated)
    {
        $contact = $this->contact->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'category' => $validated['category'],
            'message' => $validated['message'],
        ]);

        return $contact;
    }
}
