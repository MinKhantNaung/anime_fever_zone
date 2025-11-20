@props([
    'videos' => [],
    'title' => 'Videos',
])

@if ($videos->count() > 0)
    <div class="w-full my-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-3xl sm:text-4xl font-bold text-black">{{ $title }}</h2>
            <a wire:navigate.hover href="{{ route('videos.index') }}"
                class="text-[#9926f0] hover:text-[#d122e3] font-bold text-base sm:text-lg">
                <div class="flex items-center gap-1">
                    <span>See more</span>
                    <span class="text-red-600 text-3xl">+</span>
                </div>
            </a>
        </div>

        <div x-data="{
            swiper: null
        }" x-init="swiper = new Swiper($refs.swiperContainer, {
            modules: [Navigation, Pagination],
            slidesPerView: 1,
            spaceBetween: 0,
            loop: {{ $videos->count() > 1 ? 'true' : 'false' }},
            navigation: {
                nextEl: $refs.nextButton,
                prevEl: $refs.prevButton,
            },
            pagination: {
                el: $refs.pagination,
                clickable: true,
            },
        });" class="relative max-w-4xl mx-auto rounded-lg p-4">
            <div x-ref="swiperContainer" class="swiper">
                <div class="swiper-wrapper">
                    @foreach ($videos as $index => $video)
                        <div class="swiper-slide">
                            <a href="{{ route('video.show', $video->slug) }}"
                                class="group block bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 max-w-3xl mx-auto">
                                <div class="relative aspect-video overflow-hidden bg-gray-200">
                                    <img src="{{ $video->getThumbnailUrl('maxresdefault') }}" alt="{{ $video->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        onerror="this.onerror=null; this.src='{{ $video->getThumbnailUrl('hqdefault') }}';"
                                        @if ($index === 0 && request()->routeIs('home')) fetchpriority="high" @else loading="lazy" @endif
                                    >
                                    <!-- Play button overlay -->
                                    <div
                                        class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300">
                                        <div
                                            class="w-12 h-12 rounded-full bg-red-600 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                            <svg class="w-6 h-6 text-white ml-1" fill="currentColor"
                                                viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3">
                                    <h3
                                        class="font-semibold text-lg text-black line-clamp-2 group-hover:text-red-600 transition-colors min-h-[2.5rem]">
                                        {{ $video->title }}
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $video->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <!-- Navigation buttons -->
                <div x-ref="prevButton" class="swiper-button-prev absolute left-2 top-1/2 -translate-y-1/2 z-10">
                    <div
                        class="bg-white/95 border-2 border-white p-2 rounded-full text-gray-900 shadow-lg hover:bg-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.8"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </div>
                </div>
                <div x-ref="nextButton" class="swiper-button-next absolute right-2 top-1/2 -translate-y-1/2 z-10">
                    <div
                        class="bg-white/95 border-2 border-white p-2 rounded-full text-gray-900 shadow-lg hover:bg-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.8"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </div>
                </div>

                <!-- Pagination -->
                <div x-ref="pagination" class="swiper-pagination mt-4"></div>
            </div>
        </div>
    </div>
@endif
