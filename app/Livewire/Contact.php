<?php

namespace App\Livewire;

use App\Services\AlertService;
use App\Services\ContactService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Contact extends Component
{
    public $name;
    public $email;
    public $category;
    public $message;

    protected $contactService;
    protected $alertService;

    public function boot(ContactService $contactService, AlertService $alertService)
    {
        $this->contactService = $contactService;
        $this->alertService = $alertService;
    }

    public function sendToContact()
    {
        $validated = $this->validateRequests();

        DB::beginTransaction();
        try {
            $this->contactService->store($validated);

            DB::commit();

            $this->reset();

            $this->alertService->alert($this, config('messages.contact.success'), 'success');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->alertService->alert($this, config('messages.common.error'), 'error');
        }
    }

    protected function validateRequests()
    {
        return $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'category' => ['required', 'in:general,idea,issue,ads,copy'],
            'message' => ['required', 'string', 'max:255'],
        ]);
    }

    public function render()
    {
        return view('livewire.contact')
            ->title('Contact | Anime Fever Zone');
    }
}
