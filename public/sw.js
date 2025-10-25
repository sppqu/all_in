const CACHE_NAME = 'sppqu-v2.0.1-no-cache';
const OFFLINE_URL = '/offline';

// Install event - ONLY cache offline page
self.addEventListener('install', function(event) {
    console.log('[ServiceWorker] Installing...');
    // Skip waiting to activate immediately
    self.skipWaiting();
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function(cache) {
                console.log('[ServiceWorker] Caching offline page only');
                return cache.add(OFFLINE_URL);
            })
            .catch(function(error) {
                console.error('[ServiceWorker] Failed to cache offline page:', error);
            })
    );
});

// Fetch event - NETWORK-ONLY strategy (no caching)
self.addEventListener('fetch', function(event) {
    const url = new URL(event.request.url);
    
    // Skip service worker for API/activation routes to avoid JSON parsing issues
    if (url.pathname.startsWith('/activate') || 
        url.pathname.startsWith('/deactivate') ||
        url.pathname.startsWith('/api/') ||
        url.pathname.includes('/grant-addon') ||
        url.pathname.includes('/revoke-addon')) {
        // Let browser handle these directly without SW intervention
        return;
    }
    
    // Network-only for navigation (HTML pages)
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .catch(function(error) {
                    console.log('[ServiceWorker] Network failed for navigation, showing offline page');
                    return caches.match(OFFLINE_URL);
                })
        );
        return;
    }
    
    // For all other requests (assets, etc), just try network
    // No caching, no fallback
    event.respondWith(fetch(event.request));
});

// Activate event - Clean up old caches
self.addEventListener('activate', function(event) {
    console.log('[ServiceWorker] Activating...');
    event.waitUntil(
        caches.keys()
            .then(function(cacheNames) {
                return Promise.all(
                    cacheNames.map(function(cacheName) {
                        if (cacheName !== CACHE_NAME) {
                            console.log('[ServiceWorker] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(function() {
                console.log('[ServiceWorker] Claiming clients');
                return self.clients.claim();
            })
    );
});

// Push notification event
self.addEventListener('push', function(event) {
    if (event.data) {
        const data = event.data.json();
        const options = {
            body: data.body,
            icon: '/images/pwa/icon-192x192.png',
            badge: '/images/pwa/icon-72x72.png',
            vibrate: [100, 50, 100],
            data: {
                dateOfArrival: Date.now(),
                primaryKey: 1
            },
            actions: [
                {
                    action: 'explore',
                    title: 'Lihat Detail',
                    icon: '/images/pwa/icon-72x72.png'
                },
                {
                    action: 'close',
                    title: 'Tutup',
                    icon: '/images/pwa/icon-72x72.png'
                }
            ]
        };

        event.waitUntil(
            self.registration.showNotification(data.title, options)
        );
    }
});

// Notification click event
self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    if (event.action === 'explore') {
        event.waitUntil(
            clients.openWindow('/student/dashboard')
        );
    }
});
