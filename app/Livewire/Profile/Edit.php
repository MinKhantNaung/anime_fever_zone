<?php

namespace App\Livewire\Profile;

use App\Models\Media;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\AlertService;
use App\Services\MediaService;
use App\Services\SiteSettingService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public $media;

    public $name;

    public $email;

    public bool $checked;

    protected $siteSetting;

    protected $siteSettingService;

    protected $mediaService;

    protected $alertService;

    public function boot(
        SiteSetting $siteSetting,
        SiteSettingService $siteSettingService,
        MediaService $mediaService,
        AlertService $alertService
    ) {
        $this->siteSetting = $siteSetting;
        $this->siteSettingService = $siteSettingService;
        $this->mediaService = $mediaService;
        $this->alertService = $alertService;
    }

    #[On('profile-reload')]
    public function mount()
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
        $this->checked = SiteSetting::first()?->email_verify_status ?? false;
    }

    public function isChecked()
    {
        $siteSetting = $this->siteSetting->first();

        $this->siteSettingService->toggleEmailVerifyStatus($siteSetting, $this->checked);

        $this->alertService->alert($this, config('messages.email.verify_toggle'), 'success');
    }

    public function saveProfile()
    {
        $validated = $this->validateRequests();

        $this->updateProfile($validated);

        $this->updateMedia($validated['media']);

        $this->reset();
        $this->dispatch('profile-reload');

        $this->alertService->alert($this, config('messages.profile.update'), 'success');
    }

    protected function validateRequests()
    {
        return $this->validate([
            'media' => ['nullable', 'file', 'image', 'mimes:webp', 'max:5120'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore(auth()->user()->id)],
        ]);
    }

    protected function updateProfile($validated)
    {
        auth()->user()->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (auth()->user()->isDirty('email')) {
            auth()->user()->email_verified_at = null;
        }

        auth()->user()->save();
    }

    protected function updateMedia($newMedia)
    {
        if ($newMedia) {
            // delete previous media
            $media = auth()->user()->media;

            if ($media) {
                $this->mediaService->destroy($media);
            }

            // add updated media
            $this->mediaService->store(User::class, auth()->user(), $newMedia, 'image');
        }
    }

    public function render()
    {
        return view('livewire.profile.edit')
            ->title('Profile');
    }
}
