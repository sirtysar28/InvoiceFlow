/* InvoiceFlow Service Worker (PWA) */
const CACHE_NAME = 'invoiceflow-v2';
const CORE_ASSETS = [
    '/offline.html',
    '/img/icon-192.png',
    '/img/icon-512.png',
    '/img/favicon.svg',
];

/*
 * Hanya aset statis (ekstensi file jelas) yang boleh di-cache.
 * ROUTE DINAMIS WAJIB BYPASS — terutama /captcha/* (gambar captcha
 * pernah ter-cache & bikin kode captcha "selalu salah" karena user
 * melihat kode lama dari cache sementara session menyimpan kode baru).
 */
const STATIC_ASSET_RE = /\.(css|js|png|jpe?g|gif|webp|avif|ico|svg|woff2?|ttf|otf)$/i;
const NEVER_CACHE_RE = /^\/(captcha|manifest\.webmanifest|payments\/midtrans)/i;

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(CORE_ASSETS))
            .then(() => self.skipWaiting())
    );
});

/* Activate: bersihkan SEMUA cache versi lama */
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;

    if (req.method !== 'GET') return; // POST login/pembayaran/webhook: jangan sentuh

    const url = new URL(req.url);
    if (url.origin !== self.location.origin) return; // lewati CDN pihak ke-3

    // Route dinamis (captcha, manifest, webhook, dll) → langsung network, TANPA cache
    if (NEVER_CACHE_RE.test(url.pathname)) return;
    if (url.pathname.includes('/captcha')) return; // pengaman ekstra

    // Navigasi halaman → network-first, fallback cache → offline.html
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req)
                .then((res) => {
                    const copy = res.clone();
                    caches.open(CACHE_NAME).then((c) => c.put(req, copy)).catch(() => {});
                    return res;
                })
                .catch(() =>
                    caches.match(req).then((cached) => cached || caches.match('/offline.html'))
                )
        );
        return;
    }

    // Aset statis saja → stale-while-revalidate
    if (!STATIC_ASSET_RE.test(url.pathname)) return; // bukan aset statis → bypass SW

    event.respondWith(
        caches.match(req).then((cached) => {
            const fetchPromise = fetch(req)
                .then((res) => {
                    if (res.ok) {
                        const copy = res.clone();
                        caches.open(CACHE_NAME).then((c) => c.put(req, copy)).catch(() => {});
                    }
                    return res;
                })
                .catch(() => cached);

            return cached || fetchPromise;
        })
    );
});
