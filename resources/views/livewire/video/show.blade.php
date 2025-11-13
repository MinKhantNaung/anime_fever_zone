@section('description', $video->title)

@section('meta-og')
    <meta property="og:type" content="video.other" />
    <meta property="og:title" content="{{ $video->title }} - Anime Fever Zone" />
    <meta property="og:description" content="{{ $video->title }}" />
    <meta property="og:image" content="{{ $video->getThumbnailUrl('maxresdefault') }}" />
    <meta property="og:video" content="{{ $video->youtube_url }}" />
@endsection

<div class="container mx-auto px-3 py-6" id="video-container">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" id="video-layout">
        <!-- Main Video Player -->
        <div class="lg:col-span-2" id="video-main">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-black relative w-full" id="video-wrapper" style="padding-bottom: 56.25%;">
                    <video id="video-player-{{ $video->id }}" class="video-js vjs-default-skin vjs-big-play-centered"
                           controls preload="auto" data-setup='{}' style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                    </video>
                    <!-- Theater Mode Toggle Button -->
                    <button id="theater-mode-toggle" class="absolute top-4 right-4 z-10 bg-black bg-opacity-70 hover:bg-opacity-90 text-white p-2 rounded transition-all" title="Theater mode">
                        <!-- Theater mode icon (shows when NOT in theater mode - rectangle with vertical lines on sides) -->
                        <svg id="theater-icon" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 7H5c-1.1 0-2 .9-2 2v6c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zm0 8H5V9h14v6z"/>
                            <path d="M1 7h2v10H1zm20 0h2v10h-2z"/>
                        </svg>
                        <!-- Normal mode icon (shows when IN theater mode - rectangle with horizontal lines on top/bottom) -->
                        <svg id="normal-icon" class="w-5 h-5 hidden" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 19V5c0-1.1.9-2 2-2h6c1.1 0 2 .9 2 2v14c0 1.1-.9 2-2 2H9c-1.1 0-2-.9-2-2zm8 0H9V5h6v14z"/>
                            <path d="M7 1v2h10V1zm0 20v2h10v-2z"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <h1 class="text-2xl md:text-3xl font-bold text-black mb-3">{{ $video->title }}</h1>
                    <div class="flex items-center text-sm text-gray-500 mb-4">
                        <span>Published {{ $video->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="prose max-w-none">
                        <p class="text-gray-700">{{ $video->description }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Videos Sidebar -->
        <div class="lg:col-span-1" id="video-sidebar">
            <h2 class="text-xl font-bold text-black mb-4">Related Videos</h2>
            @if ($relatedVideos->count() > 0)
                <div class="space-y-4">
                    @foreach ($relatedVideos as $relatedVideo)
                        <a href="{{ route('video.show', $relatedVideo->slug) }}"
                           class="flex gap-3 bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow group">
                            <div class="relative w-40 h-24 flex-shrink-0 bg-gray-200 overflow-hidden">
                                <img
                                    src="{{ $relatedVideo->getThumbnailUrl('hqdefault') }}"
                                    alt="{{ $relatedVideo->title }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    loading="lazy"
                                >
                                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all">
                                    <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 p-2">
                                <h3 class="font-semibold text-sm text-black line-clamp-2 group-hover:text-red-600 transition-colors">
                                    {{ $relatedVideo->title }}
                                </h3>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $relatedVideo->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">No related videos available.</p>
            @endif
        </div>
    </div>
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
            grid-template-columns: 1fr !important;
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

        #video-container.theater-mode #video-main .bg-white > div:last-child {
            padding: 1rem 1.5rem !important;
        }

        #video-container.theater-mode #video-sidebar {
            display: none !important;
        }

        #video-container.theater-mode .bg-white > div:last-child {
            flex-shrink: 0 !important;
            max-height: 200px !important;
            overflow-y: auto !important;
        }

        /* Theater mode button styling */
        #theater-mode-toggle {
            opacity: 0;
            transition: opacity 0.3s;
        }

        #video-wrapper:hover #theater-mode-toggle {
            opacity: 1;
        }

        /* Hide theater mode on mobile devices */
        @media (max-width: 1024px) {
            #theater-mode-toggle {
                display: none !important;
            }

            #video-container.theater-mode {
                max-width: 100% !important;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
                padding-top: 1.5rem !important;
                padding-bottom: 1.5rem !important;
                height: auto !important;
                display: block !important;
                flex-direction: unset !important;
                overflow: visible !important;
            }

            #video-container.theater-mode #video-layout {
                grid-template-columns: 1fr !important;
                height: auto !important;
                display: grid !important;
                flex-direction: unset !important;
                gap: 1.5rem !important;
            }

            #video-container.theater-mode #video-main {
                width: 100% !important;
                max-width: 100% !important;
                flex: unset !important;
                display: block !important;
                flex-direction: unset !important;
                min-height: unset !important;
            }

            #video-container.theater-mode #video-main .bg-white {
                height: auto !important;
                display: block !important;
                flex-direction: unset !important;
                border-radius: 0.5rem !important;
            }

            #video-container.theater-mode #video-wrapper {
                border-radius: 0.5rem 0.5rem 0 0 !important;
                flex: unset !important;
                min-height: unset !important;
                padding-bottom: 56.25% !important;
                height: auto !important;
                aspect-ratio: 16 / 9 !important;
                max-height: none !important;
                width: 100% !important;
                position: relative !important;
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

            // Disable theater mode on mobile devices
            if (isMobileDevice()) {
                // Remove theater mode if it was previously enabled
                container.classList.remove('theater-mode');
                localStorage.setItem('theaterMode', 'false');
                return;
            }

            // Load theater mode state from localStorage
            const isTheaterMode = localStorage.getItem('theaterMode') === 'true';
            if (isTheaterMode) {
                container.classList.add('theater-mode');
                theaterIcon.classList.add('hidden');
                normalIcon.classList.remove('hidden');
            }

            toggleBtn.addEventListener('click', function() {
                // Prevent theater mode on mobile
                if (isMobileDevice()) {
                    return;
                }

                const isActive = container.classList.contains('theater-mode');

                if (isActive) {
                    // Exit theater mode
                    container.classList.remove('theater-mode');
                    theaterIcon.classList.remove('hidden');
                    normalIcon.classList.add('hidden');
                    localStorage.setItem('theaterMode', 'false');
                } else {
                    // Enter theater mode
                    container.classList.add('theater-mode');
                    theaterIcon.classList.add('hidden');
                    normalIcon.classList.remove('hidden');
                    localStorage.setItem('theaterMode', 'true');
                }
            });

            // Handle window resize to disable theater mode if resized to mobile
            window.addEventListener('resize', function() {
                if (isMobileDevice() && container.classList.contains('theater-mode')) {
                    container.classList.remove('theater-mode');
                    theaterIcon.classList.remove('hidden');
                    normalIcon.classList.add('hidden');
                    localStorage.setItem('theaterMode', 'false');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize theater mode
            initTheaterMode();

            const videoId = 'video-player-{{ $video->id }}';
            const videoElement = document.getElementById(videoId);

            if (window.videojs && videoElement) {
                const player = window.videojs(videoId, {
                    techOrder: ['youtube'],
                    width: '100%',
                    height: '100%',
                    sources: [{
                        type: 'video/youtube',
                        src: '{{ $video->youtube_url }}'
                    }],
                    youtube: {
                        ytControls: 2,
                        modestbranding: 1,
                        rel: 0
                    }
                });
            }
        });

        // Re-initialize when Livewire updates
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', () => {
                // Re-initialize theater mode
                initTheaterMode();

                const videoId = 'video-player-{{ $video->id }}';
                const videoElement = document.getElementById(videoId);

                if (window.videojs && videoElement) {
                    const existingPlayer = window.videojs.getPlayer(videoId);
                    if (existingPlayer) {
                        existingPlayer.dispose();
                    }

                    const player = window.videojs(videoId, {
                        techOrder: ['youtube'],
                        width: '100%',
                        height: '100%',
                        sources: [{
                            type: 'video/youtube',
                            src: '{{ $video->youtube_url }}'
                        }],
                        youtube: {
                            ytControls: 2,
                            modestbranding: 1,
                            rel: 0
                        }
                    });
                }
            });
        });
    </script>
@endpush
