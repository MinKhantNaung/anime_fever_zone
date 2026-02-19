<?php

use App\Http\Controllers\SubscriberController;
use App\Livewire\Contact;
use App\Livewire\Home;
use App\Livewire\Info\About;
use App\Livewire\Post\Index as PostIndex;
use App\Livewire\PostShow;
use App\Livewire\Privacy;
use App\Livewire\Profile\Edit;
use App\Livewire\Section\Create as SectionCreate;
use App\Livewire\Section\Edit as SectionEdit;
use App\Livewire\Section\Index as SectionIndex;
use App\Livewire\Tag\Create as TagCreate;
use App\Livewire\Tag\Edit as TagEdit;
use App\Livewire\Tag\Index;
use App\Livewire\TagShow;
use App\Livewire\Term;
use App\Livewire\Topic;
use App\Livewire\Topic\Create;
use App\Livewire\Video\Create as VideoCreate;
use App\Livewire\Video\Edit as VideoEdit;
use App\Livewire\Video\Index as VideoIndex;
use App\Livewire\Video\PublicIndex as VideoPublicIndex;
use App\Livewire\Video\Show as VideoShow;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Home::class)->name('home');
Route::livewire('/topic/{slug}', Topic::class)->name('topic');
Route::livewire('/tag/{slug}', TagShow::class)->name('tag');
Route::livewire('/blog/{slug}', PostShow::class)->name('post');
Route::livewire('/videos', VideoPublicIndex::class)->name('videos.index');
Route::livewire('/videos/{slug}', VideoShow::class)->name('video.show');

// Email Subscribe
Route::get('/subscriber/verify/{token}/{email}', SubscriberController::class)->name('subscriber_verify');

// info
Route::middleware('cache.headers:public;max_age=2628000;etag')->group(function (): void {
    Route::livewire('/about', About::class)->name('about');
    Route::livewire('/privacy-policy', Privacy::class)->name('privacy');
    Route::livewire('/terms', Term::class)->name('term');
    Route::livewire('/contact/us', Contact::class)->name('contact');
});

Route::middleware('auth')->group(function (): void {
    Route::livewire('/profile', Edit::class)->name('profile.edit');

    Route::middleware('isBlogger')->group(function (): void {
        Route::livewire('/topics', Create::class)->name('topics.create');

        Route::prefix('/blogger')->group(function (): void {
            Route::livewire('/tags', Index::class)->name('tags.index');
            Route::livewire('/tags/create', TagCreate::class)->name('tags.create');
            Route::livewire('/tags/{tag}/edit', TagEdit::class)->name('tags.edit');

            Route::livewire('/posts', PostIndex::class)->name('posts.index');
            Route::livewire('/posts/{post}/sections', SectionIndex::class)->name('sections.index');
            Route::livewire('/posts/{post}/sections/create', SectionCreate::class)->name('sections.create');
            Route::livewire('/posts/sections/{section}/edit', SectionEdit::class)->name('sections.edit');

            Route::livewire('/videos', VideoIndex::class)->name('blogger.videos.index');
            Route::livewire('/videos/create', VideoCreate::class)->name('blogger.videos.create');
            Route::livewire('/videos/{video}/edit', VideoEdit::class)->name('blogger.videos.edit');
        });
    });
});

require __DIR__ . '/auth.php';
