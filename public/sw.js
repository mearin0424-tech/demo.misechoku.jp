/**
 * ミセチョク PWA Service Worker
 * 静的アセットをキャッシュし、オフラインでも基本動作をサポート
 */
const CACHE_NAME = 'misechoku-v1';
const STATIC_ASSETS = [
  '/',
  '/shop/home',
  '/assets/css/app.css',
  '/assets/css/layout-header.css',
  '/assets/css/layout-footer.css',
  '/assets/css/layout-sidebar.css',
  '/assets/css/character-guide.css',
  '/assets/js/app.js',
  '/assets/js/character-guide.js',
  '/manifest.json',
  '/assets/images/pwa/icon-192.svg',
  '/assets/images/pwa/icon-512.svg'
];

// インストール: 静的アセットをキャッシュ
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS).catch(() => {
        // 一部失敗してもインストールは完了とする
      });
    }).then(() => self.skipWaiting())
  );
});

// アクティベート: 古いキャッシュを削除
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((names) => {
      return Promise.all(
        names.filter((name) => name !== CACHE_NAME).map((name) => caches.delete(name))
      );
    }).then(() => self.clients.claim())
  );
});

// フェッチ: ネットワーク優先、失敗時はキャッシュ
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // 同一オリジンのナビゲーション・静的リソースのみキャッシュ対象
  if (url.origin !== self.location.origin) {
    return;
  }

  // API・POST 等はキャッシュしない
  if (request.method !== 'GET') {
    return;
  }

  event.respondWith(
    fetch(request)
      .then((response) => {
        const clone = response.clone();
        if (response.status === 200 && isCacheable(url)) {
          caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
        }
        return response;
      })
      .catch(() => caches.match(request).then((cached) => cached || caches.match('/')))
  );
});

function isCacheable(url) {
  const path = url.pathname;
  return path.startsWith('/assets/') || path === '/manifest.json' || path === '/';
}
