@section('description', substr($post->body, 0, 150) . '...')

@section('meta-og')
    <meta property="og:type" content="article" />
    <meta property="og:title" content="{{ $post->heading }}" />
    <meta property="og:description" content="{{ substr($post->body, 0, 150) }}" />
    <meta property="og:image" content="{{ $post->media->url }}" />
    <meta property="og:image:secure_url" content="{{ $post->media->url }}" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    @foreach ($post->tags as $tag)
        <meta property="article:tag" content="{{ $tag->name }}" />
    @endforeach
    <meta property="article:published_time" content="{{ $post->created_at->toIso8601String() }}" />
    <meta property="article:modified_time" content="{{ $post->updated_at->toIso8601String() }}" />
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $post->heading }}">
    <meta name="twitter:description" content="{{ substr($post->body, 0, 150) }}">
    <meta name="twitter:image" content="{{ $post->media->url }}">
@endsection

<div class="container mx-auto flex flex-wrap py-6">

    <section class="w-full md:w-2/3 flex flex-col items-center px-3">

        <article class="flex flex-col my-4 w-full">
            <div class="flex items-center justify-between mb-2">
                <h1 class="text-3xl sm:text-5xl font-black leading-tight text-black pb-2 flex-1">{{ $post->heading }}
                </h1>
                <div class="ml-4 flex items-center gap-2">
                    <button wire:click="generateAudio" wire:loading.attr="disabled" wire:target="generateAudio"
                        class="flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        title="Listen to this article">
                        <svg wire:loading.remove wire:target="generateAudio" class="w-5 h-5" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z">
                            </path>
                        </svg>
                        <svg wire:loading wire:target="generateAudio" class="animate-spin w-5 h-5" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span wire:loading.remove wire:target="generateAudio">Listen</span>
                        <span wire:loading wire:target="generateAudio">Generating...</span>
                    </button>
                </div>
            </div>
            <div class="bg-white flex flex-col justify-start">
                <p class="text-xs py-6 text-gray-500">
                    By <span class="font-bold mr-2">Anime Fever Zone</span>
                    Modified {{ $post->updated_at->diffForHumans() }}
                </p>
            </div>
            @if ($audioUrl)
                <div class="mb-4 p-4 bg-gray-100 rounded-lg"
                    wire:key="audio-player-{{ $post->id }}-{{ md5($audioUrl) }}">
                    <audio x-data x-ref="player" x-init="$refs.player.load()" controls class="w-full">
                        <source :src="'{{ $audioUrl }}'" type="audio/mpeg">
                    </audio>
                </div>
            @endif
            <!-- Article Image -->
            <img src="{{ $post->media->url }}" alt="Image representing {{ $post->heading }}" class="w-full"
                fetchpriority="high">
            <!-- Post Description -->
            <p class="pb-3 pt-6 text-lg font-medium text-gray-700 leading-9 anime-content">{!! $post->body !!}</p>

            <div class="bg-gray-100 p-4 my-7">
                <h2 class="text-xl sm:text-2xl font-medium text-gray-800">Table Of Contents</h2>
                <ul class="list-decimal list-inside text-[#9926f0] hover:text-[#d122e3] font-medium text-lg mt-5">
                    @foreach ($post->sections as $section)
                        @if ($section->heading)
                            <li class="py-2">
                                <a href="#{{ str_replace(' ', '-', $section->heading) }}">{{ $section->heading }}</a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>

            @foreach ($post->sections as $section)
                <livewire:section.item wire:key="{{ $section->id }}" :section="$section" />
            @endforeach

            <div class="my-5">
                <h6 class="text-xl italic font-extrabold my-2 text-gray-800">Related Topics</h6>
                <div class="text-gray-700">
                    <a wire:navigate.hover href="{{ route('topic', $post->topic->slug) }}"
                        class="font-bold text-xs uppercase bg-gray-300 hover:bg-gray-200 rounded p-2">{{ $post->topic->name }}</a>
                    @foreach ($post->tags as $tag)
                        <a wire:navigate.hover href="{{ route('tag', $tag->slug) }}"
                            class="font-bold text-xs uppercase bg-gray-300 hover:bg-gray-200 rounded p-2">{{ $tag->name }}</a>
                    @endforeach
                </div>
            </div>

            <livewire:comments :model="$post" />

            {{-- Subscriber Form --}}
            @if ($emailVerifyStatus)
                <div class="w-full bg-gray-400 mt-5 rounded-lg py-5 px-4 text-xl font-extrabold">
                    <h1 class="text-black">Subscribe To Our Newletter!</h1>

                    <form wire:submit.prevent='subscribe'>

                        @csrf
                        <input wire:model='email' type="email" class="mt-5 focus:ring-0 w-full text-lg"
                            placeholder="Email Address">
                        @error('email')
                            <x-input-error messages="{{ $message }}" />
                        @enderror
                        <button wire:loading.attr='disabled' wire:click.prevent='subscribe'
                            class="btn btn-secondary text-lg mt-5">
                            Subscribe
                        </button>
                    </form>
                </div>
            @endif

        </article>

        <!-- Videos Swiper Section -->
        <x-video-swiper :videos="$videos" title="Videos" />

    </section>

    <!-- other posts Section -->
    <x-other-posts :$featuredPosts />

</div>

@section('meta-jsonld')

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BlogPosting",
      "headline": "{{ ucwords(str_replace('-', ' ', $slug)) }}",
      "image": "{{ $post->media->url }}",
      "description": "{{ substr($post->body, 0, 150) }}",
      "author": {
        "@type": "Person",
        "name": "Anime Fever Zone"
      }
    }
    </script>
@endsection
