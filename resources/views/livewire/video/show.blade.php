@section('description', $video->title)

@section('meta-og')
    <meta property="og:type" content="video.other" />
    <meta property="og:title" content="{{ $video->title }} - Anime Fever Zone" />
    <meta property="og:description" content="{{ $video->title }}" />
    <meta property="og:image" content="{{ $video->getThumbnailUrl('maxresdefault') }}" />
    <meta property="og:video" content="{{ $video->youtube_url }}" />
@endsection

<div class="container mx-auto px-3 py-6" id="video-container">
    <div id="video-layout">
        <!-- Main Video Player - Full Width -->
        <div id="video-main" class="mb-6">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-black relative w-full" id="video-wrapper" style="padding-bottom: 56.25%;">
                    <video id="video-player-{{ $video->id }}" class="video-js vjs-default-skin vjs-big-play-centered"
                        controls preload="auto" data-setup='{}'
                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                    </video>
                    <!-- Theater Mode Toggle Button -->
                    <button id="theater-mode-toggle"
                        class="absolute top-4 right-4 z-10 bg-black bg-opacity-70 hover:bg-opacity-90 text-white p-2 rounded transition-all"
                        title="Theater mode">
                        <!-- Theater mode icon (shows when NOT in theater mode - rectangle with vertical lines on sides) -->
                        <svg id="theater-icon" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M19 7H5c-1.1 0-2 .9-2 2v6c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zm0 8H5V9h14v6z" />
                            <path d="M1 7h2v10H1zm20 0h2v10h-2z" />
                        </svg>
                        <!-- Normal mode icon (shows when IN theater mode - rectangle with horizontal lines on top/bottom) -->
                        <svg id="normal-icon" class="w-5 h-5 hidden" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M7 19V5c0-1.1.9-2 2-2h6c1.1 0 2 .9 2 2v14c0 1.1-.9 2-2 2H9c-1.1 0-2-.9-2-2zm8 0H9V5h6v14z" />
                            <path d="M7 1v2h10V1zm0 20v2h10v-2z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Title, Description and Related Videos Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Title and Description -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h1 class="text-2xl md:text-3xl font-bold text-black mb-3">{{ $video->title }}</h1>
                    <div class="flex items-center text-sm text-gray-500 mb-4">
                        <span>Published {{ $video->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="prose max-w-none">
                        <p class="text-gray-700">{!! $video->description !!}</p>
                    </div>
                </div>
            </div>

            <!-- Trending Videos Sidebar -->
            <div class="lg:col-span-1" id="video-sidebar">
                <h2 class="text-xl font-bold text-black mb-4">TRENDING NOW</h2>
                @if ($trendingVideos->count() > 0)
                    <div class="space-y-4">
                        @foreach ($trendingVideos as $trendingVideo)
                            <a href="{{ route('video.show', $trendingVideo->slug) }}"
                                class="flex gap-3 bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow group">
                                <div class="relative w-40 h-24 flex-shrink-0 bg-gray-200 overflow-hidden">
                                    <img src="{{ $trendingVideo->getThumbnailUrl('hqdefault') }}"
                                        alt="{{ $trendingVideo->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        loading="lazy">
                                    <div
                                        class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all">
                                        <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition-opacity"
                                            fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 p-2">
                                    <h3
                                        class="font-semibold text-sm text-black line-clamp-2 group-hover:text-red-600 transition-colors">
                                        {{ $trendingVideo->title }}
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $trendingVideo->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No trending videos available.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Recommended Videos Section - CBR Style -->
    @if ($recommendedVideos->count() > 0)
        <div class="mt-8" id="recommended-videos-section">
            <h2 class="text-2xl font-bold text-black mb-4">RECOMMENDED</h2>
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($recommendedVideos as $recommendedVideo)
                    <a href="{{ route('video.show', $recommendedVideo->slug) }}"
                        class="group relative block w-full rounded-lg overflow-hidden" style="aspect-ratio: 3/4;">

                        <img src="{{ $recommendedVideo->getThumbnailUrl('maxresdefault') }}"
                            alt="{{ $recommendedVideo->title }}"
                            class="absolute inset-0 w-full h-full object-cover object-center" loading="lazy"
                            decoding="async">

                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>

                        <div class="absolute bottom-0 left-0 right-0 p-3">
                            <p class="text-white text-sm mb-1.5 opacity-90 font-medium">
                                {{ $recommendedVideo->created_at->format('M d, Y') }}
                            </p>
                            <h3 class="text-white text-lg font-bold line-clamp-2 drop-shadow-lg group-hover:underline">
                                {{ $recommendedVideo->title }}
                            </h3>
                        </div>
                    </a>
                @endforeach
            </div>

        </div>
    @endif
</div>

@push('scripts')
    <style>
        #video-player-{{ $video->id }}.video-js {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            padding-top: 0 !important;
        }

        /* Theater Mode Styles */
        #video-container.theater-mode {
            max-width: 100% !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }

        #video-container.theater-mode #video-layout {
            height: 100% !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 0 !important;
        }

        #video-container.theater-mode #video-main {
            width: 100% !important;
            max-width: 100% !important;
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 0 !important;
            margin-bottom: 0 !important;
        }

        #video-container.theater-mode #video-main .bg-white {
            height: 100% !important;
            display: flex !important;
            flex-direction: column !important;
            border-radius: 0 !important;
        }

        #video-container.theater-mode #video-wrapper {
            border-radius: 0 !important;
            flex: 1 1 auto !important;
            min-height: 0 !important;
            padding-bottom: 0 !important;
            height: auto !important;
            /* Maintain 16:9 aspect ratio, similar to YouTube's 853x480 */
            aspect-ratio: 16 / 9 !important;
            max-height: calc(100vh - 150px) !important;
            width: 100% !important;
            position: relative !important;
        }

        #video-container.theater-mode #video-wrapper video {
            height: 100% !important;
            width: 100% !important;
        }

        #video-container.theater-mode #video-wrapper .video-js {
            height: 100% !important;
            width: 100% !important;
        }

        /* Hide title/description and related videos section in theater mode */
        #video-container.theater-mode #video-layout>.grid {
            display: none !important;
        }

        #video-container.theater-mode #video-sidebar {
            display: none !important;
        }

        #video-container.theater-mode #recommended-videos-section {
            display: none !important;
        }

        /* Theater mode button styling */
        #theater-mode-toggle {
            opacity: 0;
            transition: opacity 0.3s;
        }

        #video-wrapper:hover #theater-mode-toggle {
            opacity: 1;
        }

        /* Remove padding on mobile for video container */
        @media (max-width: 1024px) {
            #video-container {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            #video-container #video-main .bg-white {
                border-radius: 0 !important;
            }

            #video-container #video-wrapper {
                border-radius: 0 !important;
            }

            /* Hide theater mode button on mobile */
            #theater-mode-toggle {
                display: none !important;
            }
        }

        /* Mobile theater mode - horizontal/fullscreen */
        @media (max-width: 1024px) {
            #video-container.theater-mode {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                width: 100vw !important;
                height: 100vh !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                z-index: 9999 !important;
                background: #000 !important;
                overflow: hidden !important;
            }

            #video-container.theater-mode #video-layout {
                height: 100vh !important;
                width: 100vw !important;
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 0 !important;
            }

            #video-container.theater-mode #video-layout>.grid {
                display: none !important;
            }

            #video-container.theater-mode #video-main {
                width: 100% !important;
                max-width: 100% !important;
                height: 100% !important;
                flex: 1 !important;
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: center !important;
                min-height: 0 !important;
            }

            #video-container.theater-mode #video-main .bg-white {
                height: 100% !important;
                width: 100% !important;
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: center !important;
                border-radius: 0 !important;
                background: #000 !important;
            }

            #video-container.theater-mode #video-wrapper {
                border-radius: 0 !important;
                width: 100% !important;
                height: 100% !important;
                padding-bottom: 0 !important;
                aspect-ratio: 16 / 9 !important;
                max-width: 100vw !important;
                max-height: 100vh !important;
                position: relative !important;
                margin: 0 auto !important;
            }

            /* Optimize for landscape orientation on mobile */
            @media (orientation: landscape) and (max-width: 1024px) {
                #video-container.theater-mode #video-wrapper {
                    width: 100vw !important;
                    height: 56.25vw !important;
                    /* 16:9 aspect ratio */
                    max-height: 100vh !important;
                }
            }

            #video-container.theater-mode #video-sidebar {
                display: none !important;
            }

            #video-container.theater-mode #video-main .bg-white>div:last-child {
                display: none !important;
            }

            /* Prevent body scroll when in theater mode on mobile */
            body.theater-mode-active {
                overflow: hidden !important;
                position: fixed !important;
                width: 100% !important;
            }
        }
    </style>
    <script>
        // Check if device is mobile
        function isMobileDevice() {
            return window.innerWidth <= 1024;
        }

        // Theater Mode Toggle
        function initTheaterMode() {
            const container = document.getElementById('video-container');
            const toggleBtn = document.getElementById('theater-mode-toggle');
            const theaterIcon = document.getElementById('theater-icon');
            const normalIcon = document.getElementById('normal-icon');

            // Check if we're on a video page - if elements don't exist, return early
            if (!container || !toggleBtn || !theaterIcon || !normalIcon) {
                return;
            }

            const isMobile = isMobileDevice();

            // Load theater mode state from localStorage (only for desktop)
            if (!isMobile) {
                const isTheaterMode = localStorage.getItem('theaterMode') === 'true';
                if (isTheaterMode) {
                    container.classList.add('theater-mode');
                    theaterIcon.classList.add('hidden');
                    normalIcon.classList.remove('hidden');
                }
            } else {
                // On mobile, don't restore theater mode from localStorage
                container.classList.remove('theater-mode');
            }

            // Remove existing event listener if any (to prevent duplicates)
            const newToggleBtn = toggleBtn.cloneNode(true);
            toggleBtn.parentNode.replaceChild(newToggleBtn, toggleBtn);

            newToggleBtn.addEventListener('click', function() {
                // Re-check elements exist (in case DOM changed)
                const currentContainer = document.getElementById('video-container');
                const currentTheaterIcon = document.getElementById('theater-icon');
                const currentNormalIcon = document.getElementById('normal-icon');

                if (!currentContainer || !currentTheaterIcon || !currentNormalIcon) {
                    return;
                }

                const isActive = currentContainer.classList.contains('theater-mode');

                if (isActive) {
                    // Exit theater mode
                    currentContainer.classList.remove('theater-mode');
                    currentTheaterIcon.classList.remove('hidden');
                    currentNormalIcon.classList.add('hidden');
                    document.body.classList.remove('theater-mode-active');

                    // Unlock orientation on mobile
                    if (isMobile && screen.orientation && screen.orientation.unlock) {
                        screen.orientation.unlock().catch(() => {});
                    }

                    if (!isMobile) {
                        localStorage.setItem('theaterMode', 'false');
                    }
                } else {
                    // Enter theater mode
                    currentContainer.classList.add('theater-mode');
                    currentTheaterIcon.classList.add('hidden');
                    currentNormalIcon.classList.remove('hidden');

                    if (isMobile) {
                        // On mobile, lock body scroll and request landscape orientation
                        document.body.classList.add('theater-mode-active');

                        // Request landscape orientation if supported
                        if (screen.orientation && screen.orientation.lock) {
                            screen.orientation.lock('landscape').catch(() => {
                                // If lock fails, try alternative method
                                if (screen.orientation && screen.orientation.lock) {
                                    screen.orientation.lock('landscape-primary').catch(() => {});
                                }
                            });
                        }
                    } else {
                        localStorage.setItem('theaterMode', 'true');
                    }
                }
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                // Re-check container exists
                const currentContainer = document.getElementById('video-container');
                if (!currentContainer) {
                    return;
                }

                const nowMobile = isMobileDevice();

                if (nowMobile && currentContainer.classList.contains('theater-mode')) {
                    // Keep theater mode on mobile, just update body class
                    document.body.classList.add('theater-mode-active');
                } else if (!nowMobile && currentContainer.classList.contains('theater-mode')) {
                    // On desktop, remove body class
                    document.body.classList.remove('theater-mode-active');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize theater mode
            initTheaterMode();

            const videoId = 'video-player-{{ $video->id }}';
            const videoElement = document.getElementById(videoId);

            if (window.videojs && videoElement) {
                const player = window.videojs(videoElement, {
                    techOrder: ['youtube'],
                    width: '100%',
                    height: '100%',
                    controls: true,
                    sources: [{
                        type: 'video/youtube',
                        src: '{{ $video->youtube_url }}'
                    }],
                    youtube: {
                        modestbranding: 1,
                        rel: 0,
                        controls: 0, // hide YouTube controls
                        showinfo: 0,
                        iv_load_policy: 3, // hide annotations
                        fs: 1,
                        cc_load_policy: 1,
                        cc_lang_pref: 'en'
                    }
                });
            }
        });

        // Re-initialize only when Livewire morphs in a *new* video element (e.g. navigation to this page).
        // Skip when the element is not in the DOM or already has a valid player (avoids "element not in DOM" and black screen).
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', () => {
                const container = document.getElementById('video-container');
                if (!container) return;

                const videoId = 'video-player-{{ $video->id }}';

                const runAfterMorph = () => {
                    initTheaterMode();

                    const videoElement = document.getElementById(videoId);
                    if (!window.videojs || !videoElement) return;
                    if (!document.body.contains(videoElement)) return;

                    const existingPlayer = window.videojs.getPlayer(videoElement);
                    if (existingPlayer && typeof existingPlayer.isDisposed === 'function' && !existingPlayer.isDisposed()) {
                        return;
                    }
                    if (existingPlayer && typeof existingPlayer.dispose === 'function') {
                        existingPlayer.dispose();
                    }

                    window.videojs(videoElement, {
                        techOrder: ['youtube'],
                        width: '100%',
                        height: '100%',
                        controls: true,
                        sources: [{
                            type: 'video/youtube',
                            src: '{{ $video->youtube_url }}'
                        }],
                        youtube: {
                            modestbranding: 1,
                            rel: 0,
                            controls: 0,
                            showinfo: 0,
                            iv_load_policy: 3,
                            fs: 1,
                            cc_load_policy: 1,
                            cc_lang_pref: 'en'
                        }
                    });
                };

                requestAnimationFrame(() => {
                    requestAnimationFrame(runAfterMorph);
                });
            });
        });
    </script>
@endpush

@section('meta-jsonld')
    <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "VideoObject",
  "name": "{{ $video->title }}",
  "description": "{{ Str::limit(strip_tags($video->description), 150) }}",
  "thumbnailUrl": [
    "{{ $video->getThumbnailUrl('maxresdefault') }}"
  ],
  "uploadDate": "{{ $video->created_at->toIso8601String() }}",
  "embedUrl": "{{ $video->youtube_url }}",
  "url": "{{ route('video.show', $video->slug) }}",
  "publisher": {
    "@type": "Organization",
    "name": "Anime Fever Zone",
    "logo": {
      "@type": "ImageObject",
      "url": "https://animefeverzone.com/logo.png"
    }
  }
}
</script>
@endsection
