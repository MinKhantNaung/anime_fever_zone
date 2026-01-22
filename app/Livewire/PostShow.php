<?php

namespace App\Livewire;

use App\Mail\WebsiteMail;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Subscriber;
use App\Models\Video;
use App\Services\ElevenlabsService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Component;

use function Illuminate\Support\defer;

class PostShow extends Component
{
    public $slug;

    public $post;

    public $featuredPosts;

    public $videos;

    public $email;

    public bool $emailVerifyStatus;

    public ?string $audioUrl = null;

    public bool $isGeneratingAudio = false;

    public function subscribe()
    {
        $this->validate([
            'email' => ['required', 'string', 'email', Rule::unique('subscribers')->where(function ($query) {
                return $query->where('status', 'Active');
            })],
        ]);

        $token = hash('sha256', time());

        Subscriber::create([
            'email' => $this->email,
            'token' => $token,
            'status' => 'Pending',
        ]);

        // Send email
        $subject = 'Please Comfirm Subscription';
        $verification_link = url('subscriber/verify/' . $token . '/' . $this->email);
        $message = 'Please click on the following link in order to verify as subscriber:<br><br>';

        $message .= '<a href="' . $verification_link . '">';
        $message .= $verification_link;
        $message .= '</a><br><br>';
        $message .= 'If you received this email by mistake, simply delete it. You will not be subscribed if you do not click the confirmation link above.';

        defer(fn () => Mail::to($this->email)->send(new WebsiteMail($subject, $message)))->always();

        $this->dispatch('subscribed', [
            'title' => 'Thanks, please check your inbox to confirm subscription!',
            'icon' => 'success',
            'iconColor' => 'green',
        ]);
    }

    public function mount()
    {
        $this->post = Post::with('media', 'topic', 'tags', 'sections')
            ->select('id', 'topic_id', 'heading', 'body', 'created_at', 'updated_at')
            ->where('slug', $this->slug)
            ->first();

        if (! $this->post) {
            abort(404);
        }

        $this->featuredPosts = Post::where('id', '!=', $this->post->id)
            ->where('is_feature', true)
            ->where('is_publish', true)
            ->with('media')
            ->inRandomOrder()
            ->select('id', 'heading', 'slug')
            ->take(5)
            ->get();

        $this->videos = Video::where('is_publish', true)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $this->emailVerifyStatus = SiteSetting::first()->email_verify_status;
    }

    public function generateAudio(ElevenlabsService $elevenlabsService): void
    {
        $this->isGeneratingAudio = true;

        $text = $this->getPostText();

        $this->audioUrl = $elevenlabsService->textToSpeech($text);

        $this->isGeneratingAudio = false;

        if ($this->audioUrl === 'limit_reached') {
            $this->dispatch('swal', [
                'title' => 'Sorry, we have reached our quota limit. Please try again later.',
                'icon' => 'warning',
                'iconColor' => 'yellow',
            ]);

            $this->audioUrl = null;

            return;
        }

        if (! $this->audioUrl) {
            $this->dispatch('swal', [
                'title' => 'Failed to generate audio. Please check your Elevenlabs API configuration.',
                'icon' => 'error',
                'iconColor' => 'red',
            ]);
        }
    }

    protected function getPostText(): string
    {
        // Combine heading and body, strip HTML tags for clean text
        $heading = strip_tags($this->post->heading);
        $body = strip_tags($this->post->body);

        // Remove extra whitespace and newlines
        $text = $heading . '. ' . $body;

        // Include all sections
        //        foreach ($this->post->sections as $section) {
        //            $sectionHeading = $section->heading ? strip_tags($section->heading) . '. ' : '';
        //            $sectionBody = strip_tags($section->body);
        //            $text .= ' ' . $sectionHeading . $sectionBody;
        //        }

        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        return $text;
    }

    public function render()
    {
        return view('livewire.post-show')
            ->title(ucwords(str_replace('-', ' ', $this->slug)) . ' - Anime Fever Zone');
    }
}
