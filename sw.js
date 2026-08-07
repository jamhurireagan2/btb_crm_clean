// Service Worker for Client Management System PWA

const CACHE_NAME = 'cms-v2';
const urlsToCache = [
  '/',
  '/index_pwa.php',
  '/pwa_login.php',
  '/pwa_dashboard.php',
  '/pwa_logout.php',
  '/assets/style.css',
  '/assets/images/icon-192x192.png',
  '/assets/images/icon-512x512.png'
];

// Install event - cache assets
self.addEventListener('install', function(event) {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
  );
});

// Fetch event - serve from cache if available
self.addEventListener('fetch', function(event) {
  event.respondWith(
    caches.match(event.request)
      .then(function(response) {
        if (response) {
          return response;
        }
        return fetch(event.request);
      })
  );
});

// Activate event - clean old caches
self.addEventListener('activate', function(event) {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames.map(function(cacheName) {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});