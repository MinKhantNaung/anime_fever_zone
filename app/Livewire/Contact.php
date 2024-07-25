<?php

namespace App\Livewire;

use App\Models\Contact as ContactModel;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Contact extends Component
{
    public $name;
    public $email;
    public $category;
    public $message;

    public function sendToContact()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'category' => 'required|in:general,idea,issue,ads,copy',
            'message' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            ContactModel::create([
                'name' => $this->name,
                'email' => $this->email,
                'category' => $this->category,
                'message' => $this->message
            ]);

            DB::commit();
            $this->reset();
            $this->dispatch('swal', [
                'title' => 'Your message sent successfully !',
                'icon' => 'success',
                'iconColor' => 'green'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('swal', [
                'title' => 'An unexpected error occurred. Please try again later.',
                'icon' => 'error',
                'iconColor' => 'red'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.contact')
            ->title('Contact | Anime Fever Zone');
    }
}
