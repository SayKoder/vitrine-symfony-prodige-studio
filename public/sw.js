const CACHE_STATIQUE = 'vitrineps-statique-v2';
const CACHE_PAGES = 'vitrineps-pages-v1';
const CACHES_CONNUS = [CACHE_STATIQUE, CACHE_PAGES];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_PAGES).then((cache) => cache.add('/')),
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cles) => Promise.all(
            cles.filter((cle) => !CACHES_CONNUS.includes(cle)).map((cle) => caches.delete(cle)),
        )),
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if ('GET' !== request.method) {
        return;
    }

    const url = new URL(request.url);

    if (url.pathname.startsWith('/assets/') || url.pathname.startsWith('/icons/') || url.pathname.startsWith('/uploads/')) {
        event.respondWith(gererRessourceStatique(request));
        return;
    }

    if ('navigate' === request.mode) {
        event.respondWith(gererNavigation(request));
    }
});

async function gererRessourceStatique(request) {
    const cache = await caches.open(CACHE_STATIQUE);
    const reponseEnCache = await cache.match(request);

    if (reponseEnCache) {
        return reponseEnCache;
    }

    const reponseReseau = await fetch(request);
    cache.put(request, reponseReseau.clone());

    return reponseReseau;
}

async function gererNavigation(request) {
    const cache = await caches.open(CACHE_PAGES);

    try {
        const reponseReseau = await fetch(request);
        cache.put(request, reponseReseau.clone());

        return reponseReseau;
    } catch (erreur) {
        const reponseEnCache = await cache.match(request);

        return reponseEnCache ?? Response.error();
    }
}
