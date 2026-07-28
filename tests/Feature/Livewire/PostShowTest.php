<?php

namespace Tests\Feature\Livewire;

use App\Livewire\PostShow;
use App\Mail\WebsiteMail;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Subscriber;
use Database\Factories\VideoFactory;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class PostShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // PostShow::mount() reads SiteSetting::first()->email_verify_status.
        SiteSetting::factory()->create();
    }

    /** Mount */
    public function test_it_renders_a_published_post(): void
    {
        $post = Post::factory()->withMedia()->published()->create(['heading' => 'Demon Slayer Recap']);

        Livewire::test(PostShow::class, ['slug' => $post->slug])
            ->assertOk()
            ->assertSet('post.id', $post->id)
            ->assertSee('Demon Slayer Recap');
    }

    public function test_it_aborts_when_the_slug_is_unknown(): void
    {
        $this->expectException(ModelNotFoundException::class);

        Livewire::test(PostShow::class, ['slug' => 'no-such-post']);
    }

    public function test_it_loads_sidebar_featured_posts_excluding_the_current_one(): void
    {
        $post = Post::factory()->withMedia()->published()->featured()->create();
        Post::factory()->count(2)->withMedia()->published()->featured()->create();

        $component = Livewire::test(PostShow::class, ['slug' => $post->slug]);

        $component->assertCount('featuredPosts', 2);
        $this->assertNotContains($post->id, $component->get('featuredPosts')->pluck('id')->all());
    }

    public function test_it_loads_published_videos_for_the_sidebar(): void
    {
        $post = Post::factory()->withMedia()->published()->create();
        VideoFactory::new()->count(2)->published()->create();
        VideoFactory::new()->create();

        Livewire::test(PostShow::class, ['slug' => $post->slug])
            ->assertCount('videos', 2);
    }

    public function test_it_exposes_the_email_verification_setting(): void
    {
        SiteSetting::first()->update(['email_verify_status' => true]);
        $post = Post::factory()->withMedia()->published()->create();

        Livewire::test(PostShow::class, ['slug' => $post->slug])
            ->assertSet('emailVerifyStatus', true);
    }

    /** Subscribing */
    public function test_a_visitor_can_subscribe(): void
    {
        $post = Post::factory()->withMedia()->published()->create();

        Livewire::test(PostShow::class, ['slug' => $post->slug])
            ->set('email', 'fan@animefeverzone.test')
            ->call('subscribe')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('subscribers', [
            'email' => 'fan@animefeverzone.test',
            'status' => 'Pending',
        ]);
    }

    public function test_subscribing_stores_a_verification_token(): void
    {
        $post = Post::factory()->withMedia()->published()->create();

        Livewire::test(PostShow::class, ['slug' => $post->slug])
            ->set('email', 'fan@animefeverzone.test')
            ->call('subscribe');

        $this->assertNotEmpty(Subscriber::first()->token);
    }

    public function test_subscribing_announces_success(): void
    {
        $post = Post::factory()->withMedia()->published()->create();

        Livewire::test(PostShow::class, ['slug' => $post->slug])
            ->set('email', 'fan@animefeverzone.test')
            ->call('subscribe')
            ->assertDispatched('subscribed');
    }

    public function test_subscribing_sends_a_confirmation_email(): void
    {
        Mail::fake();
        $this->withoutDefer();

        $post = Post::factory()->withMedia()->published()->create();

        Livewire::test(PostShow::class, ['slug' => $post->slug])
            ->set('email', 'fan@animefeverzone.test')
            ->call('subscribe');

        Mail::assertSent(WebsiteMail::class, fn ($mail) => $mail->hasTo('fan@animefeverzone.test'));
    }

    public function test_the_subscribe_email_is_required(): void
    {
        $post = Post::factory()->withMedia()->published()->create();

        Livewire::test(PostShow::class, ['slug' => $post->slug])
            ->set('email', '')
            ->call('subscribe')
            ->assertHasErrors(['email' => 'required']);

        $this->assertDatabaseCount('subscribers', 0);
    }

    public function test_the_subscribe_email_must_be_valid(): void
    {
        $post = Post::factory()->withMedia()->published()->create();

        Livewire::test(PostShow::class, ['slug' => $post->slug])
            ->set('email', 'not-an-email')
            ->call('subscribe')
            ->assertHasErrors(['email' => 'email']);
    }

    public function test_an_already_active_subscriber_cannot_subscribe_again(): void
    {
        Subscriber::factory()->active()->create(['email' => 'fan@animefeverzone.test']);
        $post = Post::factory()->withMedia()->published()->create();

        Livewire::test(PostShow::class, ['slug' => $post->slug])
            ->set('email', 'fan@animefeverzone.test')
            ->call('subscribe')
            ->assertHasErrors(['email' => 'unique']);

        $this->assertSame(1, Subscriber::count());
    }

    public function test_a_pending_subscriber_may_request_confirmation_again(): void
    {
        Subscriber::factory()->create(['email' => 'fan@animefeverzone.test']);
        $post = Post::factory()->withMedia()->published()->create();

        Livewire::test(PostShow::class, ['slug' => $post->slug])
            ->set('email', 'fan@animefeverzone.test')
            ->call('subscribe')
            ->assertHasNoErrors();

        $this->assertSame(2, Subscriber::count());
    }

    /**
     * Audio narration.
     *
     * ElevenlabsService is final, so it cannot be mocked. These drive the real
     * service with a faked HTTP client instead.
     */
    public function test_it_stores_the_generated_audio_url(): void
    {
        $this->fakeElevenlabs(Http::response('fake-mp3-bytes', 200));

        $post = Post::factory()->withMedia()->published()->create();

        $component = Livewire::test(PostShow::class, ['slug' => $post->slug])
            ->call('generateAudio')
            ->assertSet('isGeneratingAudio', false);

        $this->assertStringContainsString('/storage/audio/', $component->get('audioUrl'));
        $this->assertStringEndsWith('.mp3', $component->get('audioUrl'));
    }

    public function test_it_caches_the_generated_audio_file(): void
    {
        $this->fakeElevenlabs(Http::response('fake-mp3-bytes', 200));

        $post = Post::factory()->withMedia()->published()->create();

        Livewire::test(PostShow::class, ['slug' => $post->slug])->call('generateAudio');

        $this->assertCount(1, Storage::disk('public')->files('audio'));
    }

    public function test_it_warns_when_the_narration_quota_is_reached(): void
    {
        $this->fakeElevenlabs(Http::response(['detail' => ['status' => 'quota_exceeded']], 429));

        $post = Post::factory()->withMedia()->published()->create();

        Livewire::test(PostShow::class, ['slug' => $post->slug])
            ->call('generateAudio')
            ->assertSet('audioUrl', null)
            ->assertSet('isGeneratingAudio', false)
            ->assertDispatched('swal');
    }

    public function test_it_reports_a_narration_failure_from_the_api(): void
    {
        $this->fakeElevenlabs(Http::response('upstream exploded', 500));

        $post = Post::factory()->withMedia()->published()->create();

        Livewire::test(PostShow::class, ['slug' => $post->slug])
            ->call('generateAudio')
            ->assertSet('audioUrl', null)
            ->assertDispatched('swal');
    }

    public function test_it_reports_a_narration_failure_when_no_api_key_is_configured(): void
    {
        $this->fakeElevenlabs(Http::response('fake-mp3-bytes', 200));
        config()->set('prism.providers.elevenlabs.api_key', '');

        $post = Post::factory()->withMedia()->published()->create();

        Livewire::test(PostShow::class, ['slug' => $post->slug])
            ->call('generateAudio')
            ->assertSet('audioUrl', null)
            ->assertDispatched('swal');

        Http::assertNothingSent();
    }

    public function test_the_narration_text_combines_the_heading_and_body_without_markup(): void
    {
        $this->fakeElevenlabs(Http::response('fake-mp3-bytes', 200));

        $post = Post::factory()->withMedia()->published()->create([
            'heading' => 'The Heading',
            'body' => '<p>The   body   text.</p>',
        ]);

        Livewire::test(PostShow::class, ['slug' => $post->slug])->call('generateAudio');

        Http::assertSent(fn ($request) => $request['text'] === 'The Heading. The body text.');
    }

    /**
     * Point ElevenlabsService at a faked API with a configured key.
     */
    private function fakeElevenlabs(PromiseInterface $response): void
    {
        Storage::fake('public');
        config()->set('prism.providers.elevenlabs.api_key', 'test-key');
        Http::fake(['api.elevenlabs.io/*' => $response]);
    }

    /** Route */
    public function test_the_post_page_is_publicly_reachable(): void
    {
        $post = Post::factory()->withMedia()->published()->create();

        $this->get(route('post', $post->slug))->assertOk();
    }
}
