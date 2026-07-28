const CACHE_NAME = 'kotan-escolar-v1';

// Evento de instalación
self.addEventListener('install', event => {
    console.log('Service Worker instalado');
    self.skipWaiting();
});

// Evento de activación
self.addEventListener('activate', event => {
    console.log('Service Worker activado');
    return self.clients.claim();
});

// Interceptar peticiones de red (necesario para PWA)
self.addEventListener('fetch', event => {
    // Aquí puedes agregar lógica de caché offline en el futuro.
    // Por ahora, dejamos que pasen directo a la red.
    event.respondWith(fetch(event.request));
});