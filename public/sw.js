const CACHE_NAME = 'yelenscol-pwa-v2';
const PRECACHE_URLS = [
    '/manifest.json',
    '/favicon.svg',
    '/pwa-icon-192.png',
    '/pwa-icon-512.png'
];
const CACHEABLE_PATHS = new Set(PRECACHE_URLS);

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => Promise.all(
                cacheNames
                    .filter((cacheName) => cacheName.startsWith('yelenscol-pwa-') && cacheName !== CACHE_NAME)
                    .map((cacheName) => caches.delete(cacheName))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => new Response(
                '<!doctype html><html lang="fr"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Hors ligne · YELENSCOL</title><body style="font-family:system-ui,sans-serif;background:#f1f5f9;color:#1e1b6b;display:grid;min-height:100vh;place-items:center;margin:0"><main style="text-align:center;padding:2rem"><h1>Vous êtes hors ligne</h1><p>Reconnectez-vous à Internet pour accéder à votre espace scolaire.</p></main></body></html>',
                { headers: { 'Content-Type': 'text/html; charset=utf-8', 'Cache-Control': 'no-store' } }
            ))
        );
        return;
    }

    if (!CACHEABLE_PATHS.has(url.pathname) && !url.pathname.startsWith('/build/')) {
        return;
    }

    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(request).then((response) => {
                if (response.ok && response.type === 'basic') {
                    const responseToCache = response.clone();
                    event.waitUntil(
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, responseToCache))
                    );
                }

                return response;
            });
        })
    );
});
