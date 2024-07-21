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

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white">

    <!-- Top Bar Nav -->
    <livewire:components.nav-bar />

    <!-- Text Header -->
    <header class="w-full container mx-auto">
        <div class="flex flex-col items-center py-12">
            {{-- Eleavers Ad --}}
            <div class="w-full overflow-hidden p-4">
                <script src="//servedby.eleavers.com/ads/ads.php?t=MzA0OTI7MjA2Mjk7aG9yaXpvbnRhbC5sZWFkZXJib2FyZA==&index=1"></script>
            </div>
            {{-- Eleavers Ad --}}
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
