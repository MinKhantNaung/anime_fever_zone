<?php

namespace App\Services;

final class SiteSettingService
{
    public function toggleEmailVerifyStatus($siteSetting, bool $isChecked)
    {
        $siteSetting->update([
            'email_verify_status' => $isChecked
        ]);
    }
}
