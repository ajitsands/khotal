// K Hotel Loyalty Wallet - Service Worker
const CACHE_NAME = 'k-wallet-v1';

// Cache the page shell on install
self.addEventListener('install', event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll([
                '/backend/admin/pwa-icon-192.png',
                '/backend/admin/pwa-icon-512.png',
            ]);
        })
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// Network-first strategy: always try network, fall back to cache
self.addEventListener('fetch', event => {
    // Skip non-GET requests and cross-origin requests
    if (event.request.method !== 'GET') return;
    if (!event.request.url.startsWith(self.location.origin)) return;

    event.respondWith(
        fetch(event.request)
            .then(response => {
                // Cache successful responses for static assets only
                if (response.ok && (event.request.url.includes('.png') || event.request.url.includes('.css') || event.request.url.includes('.js'))) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});
