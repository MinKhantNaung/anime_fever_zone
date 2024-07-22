@section('description',
    'Explore exciting content on Anime and more at Anime Fever Zone. Join our community and stay informed about
    the latest trends and discussions across a wide range of topics.')

@section('meta-og')
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Anime Fever Zone" />
    <meta property="og:description"
        content="Explore exciting content on Anime and more at Anime Fever Zone. Join our community and stay informed about
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

    @push('scripts')
        {{-- PopAds --}}
        <script type="text/javascript" data-cfasync="false">
        /*<![CDATA[/* */
        (function(){var b=window,u="fc70225ec0d55b8908374d7aadb3b4ac",n=[["siteId",400*221*776-63480185],["minBid",0],["popundersPerIP","0"],["delayBetween",0],["default",false],["defaultPerDay",0],["topmostLayer","auto"]],g=["d3d3LmJldHRlcmFkc3lzdGVtLmNvbS9xcC9iZm9yY2UubWluLmpz","ZDJrazBvM2ZyN2VkMDEuY2xvdWRmcm9udC5uZXQvQm56bi9hdi9sYWZyYW1lLWFyLm1pbi5jc3M=","d3d3LmRyZ2Nuc25vaG5mLmNvbS9IL2hmb3JjZS5taW4uanM=","d3d3LmJicWFmZHNzLmNvbS9xU21nR3UvWnRtcHYvemFmcmFtZS1hci5taW4uY3Nz"],r=-1,v,p,q=function(){clearTimeout(p);r++;if(g[r]&&!(1747496315000<(new Date).getTime()&&1<r)){v=b.document.createElement("script");v.type="text/javascript";v.async=!0;var d=b.document.getElementsByTagName("script")[0];v.src="https://"+atob(g[r]);v.crossOrigin="anonymous";v.onerror=q;v.onload=function(){clearTimeout(p);b[u.slice(0,16)+u.slice(0,16)]||q()};p=setTimeout(q,5E3);d.parentNode.insertBefore(v,d)}};if(!b[u]){try{Object.freeze(b[u]=n)}catch(e){}q()}})();
        /*]]>/* */
        </script>


    {{-- PopAds end --}}

        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "BlogPosting",
          "headline": "Anime Fever Zone",
          "image": "{{ asset('favicon.ico') }}",
          "description": "Explore the latest news, reviews, and discussions on anime and other popular series at Anime Fever Zone. Stay up-to-date
          with the hottest trends and join our vibrant community of anime enthusiasts.",
          "author": {
            "@type": "Person",
            "name": "Anime Fever Zone"
          }
        }
        </script>
    @endpush

</div>
