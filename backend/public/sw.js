/*
 * EcoTech OS service worker.
 * It only makes the app installable and shows a friendly page when the
 * network is down. Pages are never cached, so signed-in data is not stored
 * on the phone.
 */
const OFFLINE_HTML = '<!doctype html><html><head><meta charset="utf-8">' +
    '<meta name="viewport" content="width=device-width,initial-scale=1"><title>EcoTech OS</title>' +
    '<style>body{font-family:system-ui,sans-serif;background:#0b1020;color:#fff;display:flex;min-height:100vh;' +
    'align-items:center;justify-content:center;text-align:center;margin:0;padding:24px}' +
    'a{color:#7ab8ff}</style></head><body><div><h1>You are offline</h1>' +
    '<p>Connect to the office Wi-Fi or check your internet, then try again.</p>' +
    '<p><a href="/">Try again</a></p></div></body></html>';

self.addEventListener('install', function () { self.skipWaiting(); });
self.addEventListener('activate', function (event) { event.waitUntil(self.clients.claim()); });

self.addEventListener('fetch', function (event) {
    if (event.request.mode !== 'navigate') {
        return;
    }
    event.respondWith(
        fetch(event.request).catch(function () {
            return new Response(OFFLINE_HTML, { headers: { 'Content-Type': 'text/html; charset=UTF-8' } });
        })
    );
});
