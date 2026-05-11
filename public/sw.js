const CACHE_VERSION = 'bpl-srt-v1';
const APP_SHELL_CACHE = `${CACHE_VERSION}-shell`;
const APP_DYNAMIC_CACHE = `${CACHE_VERSION}-dynamic`;

const APP_SHELL_FILES = [
  './',
  './offline.html',
  './manifest.webmanifest',
  './pwa-icon.svg',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(APP_SHELL_CACHE).then(async (cache) => {
      await Promise.allSettled(
        APP_SHELL_FILES.map(async (url) => {
          const response = await fetch(url, { cache: 'no-cache' });
          if (response.ok) {
            await cache.put(url, response.clone());
          }
        }),
      );
    }).then(() => self.skipWaiting()),
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((keys) =>
        Promise.all(
          keys
            .filter((key) => key !== APP_SHELL_CACHE && key !== APP_DYNAMIC_CACHE)
            .map((key) => caches.delete(key)),
        ),
      )
      .then(() => self.clients.claim()),
  );
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') {
    return;
  }

  const requestUrl = new URL(event.request.url);
  const isSameOrigin = requestUrl.origin === self.location.origin;

  if (!isSameOrigin) {
    return;
  }

  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).catch(async () => {
        const cached = await caches.match(event.request);
        if (cached) {
          return cached;
        }
        return caches.match('./offline.html');
      }),
    );
    return;
  }

  event.respondWith(
    caches.match(event.request).then(
      (cachedResponse) =>
        cachedResponse ||
        fetch(event.request)
          .then(async (networkResponse) => {
            if (networkResponse.ok) {
              const cache = await caches.open(APP_DYNAMIC_CACHE);
              cache.put(event.request, networkResponse.clone());
            }
            return networkResponse;
          })
          .catch(() => caches.match('./offline.html')),
    ),
  );
});
