var staticCacheName = "pwa-v" + new Date().getTime();
var filesToCache = [
    '/',
    '/offline',
    '/css/app.css',
    '/js/app.js',
    '/images/icons/icon-16x16.png',
    '/images/icons/icon-32x32.png',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-512x512.png',
];

// Cache on install
self.addEventListener("install", event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                return cache.addAll(filesToCache);
            })
    );
});

// Clear cache on activate
self.addEventListener("activate", event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => cacheName.startsWith("pwa-"))
                    .filter(cacheName => cacheName !== staticCacheName)
                    .map(cacheName => caches.delete(cacheName))
            );
        })
    );
});

// Serve from Cache
self.addEventListener("fetch", event => {
    let requestUrl = new URL(event.request.url);

    // Ignore Livewire requests (Prevent caching AJAX responses)
    if (requestUrl.pathname.startsWith('/livewire/')) {
        return;
    }

    // Serve from cache first, then fetch
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request).catch(() => {
                    // If offline and request fails, serve offline page (only for HTML pages)
                    if (event.request.headers.get("accept").includes("text/html")) {
                        return caches.match('offline');
                    }
                });
            })
    );
});
