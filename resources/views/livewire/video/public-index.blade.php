@section('description', 'Watch the latest anime videos, reviews, and discussions on Anime Fever Zone. Explore our collection of YouTube videos.')

@section('meta-og')
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Videos - Anime Fever Zone" />
    <meta property="og:description" content="Watch the latest anime videos, reviews, and discussions on Anime Fever Zone." />
    <meta property="og:image" content="{{ asset('favicon.ico') }}" />
@endsection

<div class="container mx-auto px-3 py-6">
    <div class="w-full mb-6">
        <h1 class="text-3xl md:text-4xl font-bold text-black mb-2">Videos</h1>
        <p class="text-gray-600">Watch the latest anime videos and discussions</p>
    </div>

    @if ($videos->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($videos as $video)
                <a wire:navigate.hover href="{{ route('video.show', $video->id) }}"
                   class="group bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="relative aspect-video overflow-hidden bg-gray-200">
                        <img
                            src="{{ $video->getThumbnailUrl('maxresdefault') }}"
                            alt="{{ $video->title }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                            onerror="this.onerror=null; this.src='{{ $video->getThumbnailUrl('hqdefault') }}';"
                        >
                        <!-- Play button overlay -->
                        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300">
                            <div class="w-16 h-16 rounded-full bg-red-600 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <svg class="w-8 h-8 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-lg text-black line-clamp-2 group-hover:text-red-600 transition-colors">
                            {{ $video->title }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-2">
                            {{ $video->created_at->diffForHumans() }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="w-full my-8">
            {{ $videos->links() }}
        </div>
    @else
        <div class="text-center py-20">
            <p class="text-2xl text-gray-500">No videos available yet.</p>
        </div>
    @endif
</div>

