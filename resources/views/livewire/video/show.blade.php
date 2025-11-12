@section('description', $video->title)

@section('meta-og')
    <meta property="og:type" content="video.other" />
    <meta property="og:title" content="{{ $video->title }} - Anime Fever Zone" />
    <meta property="og:description" content="{{ $video->title }}" />
    <meta property="og:image" content="{{ $video->getThumbnailUrl('maxresdefault') }}" />
    <meta property="og:video" content="{{ $video->youtube_url }}" />
@endsection

<div class="container mx-auto px-3 py-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Video Player -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-black relative w-full" style="padding-bottom: 56.25%;">
                    <video id="video-player-{{ $video->id }}" class="video-js vjs-default-skin vjs-big-play-centered"
                           controls preload="auto" data-setup='{}' style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                    </video>
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
        <div class="lg:col-span-1">
            <h2 class="text-xl font-bold text-black mb-4">Related Videos</h2>
            @if ($relatedVideos->count() > 0)
                <div class="space-y-4">
                    @foreach ($relatedVideos as $relatedVideo)
                        <a wire:navigate.hover href="{{ route('video.show', $relatedVideo->id) }}"
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
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

