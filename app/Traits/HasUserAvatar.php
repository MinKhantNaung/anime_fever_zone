<?php

namespace App\Traits;

trait HasUserAvatar
{
    public function avatar(): string
    {
        return asset('avatar.webp');
    }
}
