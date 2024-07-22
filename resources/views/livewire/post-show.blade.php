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
            <h1 class="text-3xl sm:text-5xl font-black leading-tight text-gray-800 pb-2">{{ $post->heading }}</h1>
            <div class="bg-white flex flex-col justify-start">
                <p class="text-xs py-6">
                    By <span class="font-bold mr-2">Anime Fever Zone</span>
                    Modified {{ $post->updated_at->diffForHumans() }}
                </p>
            </div>
            <!-- Article Image -->
            <img src="{{ $post->media->url }}" alt="{{ $post->heading }}" class="w-full">
            <!-- Post Description -->
            <p class="pb-3 pt-6 text-lg font-medium text-gray-700 leading-9">{!! $post->body !!}</p>

            <div class="bg-gray-100 p-4 my-7">
                <h2 class="text-xl sm:text-2xl font-medium">Table Of Contents</h2>
                <ul class="list-decimal list-inside text-blue-600 font-medium text-lg mt-5">
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

            <div>
                <h6 class="text-xl italic font-extrabold my-2">Related Topics</h6>
                <div>
                    <a wire:navigate href="{{ route('topic', $post->topic->slug) }}"
                        class="font-bold text-xs uppercase bg-gray-300 hover:bg-gray-200 rounded p-2">{{ $post->topic->name }}</a>
                    @foreach ($post->tags as $tag)
                        <a wire:navigate href="{{ route('tag', $tag->slug) }}"
                            class="font-bold text-xs uppercase bg-gray-300 hover:bg-gray-200 rounded p-2">{{ $tag->name }}</a>
                    @endforeach
                </div>
            </div>

            <livewire:comments :model="$post" />

            {{-- Subscriber Form --}}
            @if ($emailVerifyStatus)
                <div class="w-full bg-gray-400 mt-5 rounded-lg py-5 px-4 text-xl font-extrabold">
                    <h1>Subscribe To Our Newletter!</h1>
                    <input wire:model='email' type="email" class="mt-5 focus:ring-0 w-full text-lg"
                        placeholder="Email Address">
                    @error('email')
                        <x-input-error messages="{{ $message }}" />
                    @enderror
                    <button wire:loading.attr='disabled' wire:click.prevent='subscribe'
                        class="btn btn-secondary text-lg mt-5">
                        Subscribe
                    </button>
                </div>
            @endif

        </article>

    </section>

    <!-- other posts Section -->
    <x-other-posts :$featuredPosts />

</div>

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
      "headline": "{{ ucwords(str_replace('-', ' ', $slug)) }}",
      "image": "{{ $post->media->url }}",
      "description": "{{ substr($post->body, 0, 150) }}",
      "author": {
        "@type": "Person",
        "name": "Anime Fever Zone"
      }
    }
    </script>
@endpush
