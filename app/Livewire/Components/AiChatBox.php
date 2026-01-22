<?php

namespace App\Livewire\Components;

use Livewire\Component;

final class AiChatBox extends Component
{
    public bool $isOpen = false;

    public function toggle(): void
    {
        $this->isOpen = ! $this->isOpen;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function render()
    {
        return view('livewire.components.ai-chat-box');
    }
}
