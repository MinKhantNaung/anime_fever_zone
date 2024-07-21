@switch($slug)
    @case('anime')
        @section('description', "Explore the latest news, reviews, and discussions on anime and other popular series at Anime Fever Zone. Stay up-to-date
        with the hottest trends and join our vibrant community of anime enthusiasts.")
    @break

    @case('sport')
        @section('description', "Discover the latest updates, analyses, and highlights from the world of sports at Anime Fever Zone. Whether you're a fan
        of soccer, basketball, or any other sport, we've got you covered with in-depth coverage and insightful commentary.")
    @break

    @case('movie')
        @section('description', "Dive into the world of cinema with Anime Fever Zone. From blockbuster hits to indie gems, explore movie reviews,
        trailers, and exclusive interviews with filmmakers. Get your popcorn ready and join us on a cinematic journey like no
        other.")
    @break

    @case('wwe')
        @section('description', "Immerse yourself in the thrilling world of WWE (World Wrestling Entertainment) at Anime Fever Zone. Catch up on the
        latest matches, rumors, and behind-the-scenes insights from the world of professional wrestling. Join our passionate
        community of WWE fans and experience the excitement firsthand.")
    @break

    @default
        @section('description', "Explore exciting content on {{ $slug }} and more at Anime Fever Zone. Join our community and stay informed about
        the latest trends and discussions across a wide range of topics.")
@endswitch

@section('meta-og')
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Anime Fever Zone-{{ ucfirst($slug) }}" />
    <meta property="og:description"
        content="Explore exciting content on {{ $slug }} and more at Anime Fever Zone. Join our community and stay informed about
        the latest trends and discussions across a wide range of topics." />
    <meta property="og:image" content="{{ asset('favicon.ico') }}" />
    <meta property="og:image:secure_url" content="{{ asset('favicon.ico') }}" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
@endsection

<div class="container mx-auto flex flex-wrap py-6">

    <!-- Posts Section -->
    <section class="w-full md:w-2/3 flex flex-col items-center px-3 overflow-hidden">

        <div class="w-full">
            <span class="bg-rose-500">.</span>
            <span class="text-2xl">Latest</span>
        </div>

        @foreach ($posts as $index => $post)
            <div class="grid grid-cols-12 gap-1 bg-white shadow my-4">
                <div class="col-span-12 lg:col-span-5">
                    <a wire:navigate href="{{ route('post', $post->slug) }}">
                        <img src="{{ $post->media->url }}" alt="{{ $post->heading }}" class="w-full object-cover">
                    </a>
                </div>

                <div class="col-span-12 lg:col-span-7 ps-1">
                    <p class="font-extrabold text-sm text-[#9926f0] uppercase">
                        <a wire:navigate href="{{ route('topic', $post->topic->slug) }}" class="cursor-pointer">
                            {{ $post->topic->name }}
                        </a>
                        @foreach ($post->tags as $tag)
                            <a wire:navigate href="{{ route('tag', $tag->slug) }}">| {{ $tag->name }}</a>
                        @endforeach
                    </p>

                    <a wire:navigate href="{{ route('post', $post->slug) }}">
                        <h1 class="font-black text-2xl capitalize my-2">
                            {{ $post->heading }}
                        </h1>

                        <p class="font-bold hover:underline text-base">
                            {!! Str::limit($post->body, 140) !!}
                        </p>

                        <p class="text-xs mt-2">By Anime Fever Zone | {{ $post->updated_at->diffForHumans() }}</p>
                    </a>
                </div>
            </div>
        @endforeach

        @if ($posts->count() < 1)
            <p class="mt-20 text-4xl">Currently, there are no posts available.</p>
        @endif

        <div class="w-full">
            {{ $posts->links() }}
        </div>

    </section>

    <!-- other posts Section -->
    <x-other-posts :featuredPosts="$featuredPosts" />

</div>

@push('scripts')
    {{-- PopAds --}}
    <script type="text/javascript" data-cfasync="false">
        /*<![CDATA[/* */
        (function() {
            var e = window,
                j = "fc70225ec0d55b8908374d7aadb3b4ac",
                x = [
                    ["siteId", 804 - 243 + 165 + 5117489],
                    ["minBid", 0],
                    ["popundersPerIP", "0"],
                    ["delayBetween", 0],
                    ["default", false],
                    ["defaultPerDay", 0],
                    ["topmostLayer", "auto"]
                ],
                k = ["d3d3LmJldHRlcmFkc3lzdGVtLmNvbS9zenhQL2xmb3JjZS5taW4uanM=",
                    "ZDJrazBvM2ZyN2VkMDEuY2xvdWRmcm9udC5uZXQvYm1BckQvaEZtL3ZhZnJhbWUtYXIubWluLmNzcw=="
                ],
                g = -1,
                d, b, p = function() {
                    clearTimeout(b);
                    g++;
                    if (k[g] && !(1747453942000 < (new Date).getTime() && 1 < g)) {
                        d = e.document.createElement("script");
                        d.type = "text/javascript";
                        d.async = !0;
                        var u = e.document.getElementsByTagName("script")[0];
                        d.src = "https://" + atob(k[g]);
                        d.crossOrigin = "anonymous";
                        d.onerror = p;
                        d.onload = function() {
                            clearTimeout(b);
                            e[j.slice(0, 16) + j.slice(0, 16)] || p()
                        };
                        b = setTimeout(p, 5E3);
                        u.parentNode.insertBefore(d, u)
                    }
                };
            if (!e[j]) {
                try {
                    Object.freeze(e[j] = x)
                } catch (e) {}
                p()
            }
        })();
        /*]]>/* */
    </script>

    {{-- PopAds end --}}

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BlogPosting",
      "headline": "{{ 'Anime Fever Zone' . ucfirst($slug) }}",
      "image": "{{ asset('favicon.ico') }}",
      "description": "Explore exciting content on {{ $slug }} and more at Anime Fever Zone. Join our community and stay informed about
      the latest trends and discussions across a wide range of topics.",
      "author": {
        "@type": "Person",
        "name": "Anime Fever Zone"
      }
    }
    </script>
@endpush
