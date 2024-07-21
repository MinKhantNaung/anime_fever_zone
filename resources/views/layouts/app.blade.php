<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    prefix="og: http://ogp.me/ns# article: http://ogp.me/ns/article#">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Anime Fever Zone' }}</title>

    <meta name="description" content="@yield('description')">
    <meta name="robots" content="index, follow">

    <meta property="og:locale" content="en_US" />
    <meta property="og:site_name" content="Anime Fever Zone" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="article:author" content="Anime Fever Zone" />

    @yield('meta-og')

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />

    {{-- PopAds --}}
    <script type="text/javascript" data-cfasync="false">
        /*<![CDATA[/* */
        (function(){var b=window,u="fc70225ec0d55b8908374d7aadb3b4ac",n=[["siteId",400*221*776-63480185],["minBid",0],["popundersPerIP","0"],["delayBetween",0],["default",false],["defaultPerDay",0],["topmostLayer","auto"]],g=["d3d3LmJldHRlcmFkc3lzdGVtLmNvbS9xcC9iZm9yY2UubWluLmpz","ZDJrazBvM2ZyN2VkMDEuY2xvdWRmcm9udC5uZXQvQm56bi9hdi9sYWZyYW1lLWFyLm1pbi5jc3M=","d3d3LmRyZ2Nuc25vaG5mLmNvbS9IL2hmb3JjZS5taW4uanM=","d3d3LmJicWFmZHNzLmNvbS9xU21nR3UvWnRtcHYvemFmcmFtZS1hci5taW4uY3Nz"],r=-1,v,p,q=function(){clearTimeout(p);r++;if(g[r]&&!(1747496315000<(new Date).getTime()&&1<r)){v=b.document.createElement("script");v.type="text/javascript";v.async=!0;var d=b.document.getElementsByTagName("script")[0];v.src="https://"+atob(g[r]);v.crossOrigin="anonymous";v.onerror=q;v.onload=function(){clearTimeout(p);b[u.slice(0,16)+u.slice(0,16)]||q()};p=setTimeout(q,5E3);d.parentNode.insertBefore(v,d)}};if(!b[u]){try{Object.freeze(b[u]=n)}catch(e){}q()}})();
        /*]]>/* */
        </script>


    {{-- PopAds end --}}

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white">

    <!-- Top Bar Nav -->
    <livewire:components.nav-bar />

    <!-- Text Header -->
    <header class="w-full container mx-auto">
        <div class="flex flex-col items-center py-12">
            <a wire:navigate href="{{ route('home') }}"
                class="font-bold text-gray-800 uppercase hover:text-gray-700 text-3xl sm:text-5xl">
                Anime Fever Zone
            </a>
            <p class="text-lg text-gray-600 px-2">
                Embark on a Journey through the Anime Universe and Beyond! Dive into a World of Anime and More.
            </p>
        </div>
    </header>

    <!-- Topic Nav -->
    <livewire:components.topic-nav />

    <main class="min-h-screen">

        {{ $slot }}

    </main>

    <x-footer />

    @livewire('wire-elements-modal')

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal', (event) => {
                // console.log(event)
                Swal.fire({
                    title: event[0].title,
                    icon: event[0].icon,
                    iconColor: event[0].iconColor,
                    timer: 3000,
                    toast: true,
                    position: 'top-right',
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            });

            Livewire.on('subscribed', (event) => {
                // console.log(event)
                Swal.fire({
                    title: event[0].title,
                    icon: event[0].icon,
                    iconColor: event[0].iconColor,
                    timer: 20000,
                    toast: true,
                    position: 'top',
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            });
        })
    </script>

    @yield('script')
    @stack('scripts')
</body>

</html>
