<?php

namespace App\Livewire\Components;

use App\Livewire\Actions\Logout;
use Livewire\Attributes\On;
use Livewire\Component;

class NavBar extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    #[On('profile-reload')]
    public function render()
    {
        return view('livewire.components.nav-bar');
    }
}
