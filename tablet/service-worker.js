
const CACHE_NAME = "V210615-3";

const STATIC_CACHE_URLS = [
  "./",
  './manifest.json?v=210615-3',
  './js/angular.min.js?v=210615-3',
  './js/angular-route.min.js?v=210615-3',
  './js/angular-cookies.min.js?v=210615-3',
  './js/roamingEditor.js?v=210615-3',
  './templates/roamingsList.html?v=210615-3',
  './templates/roamingEditor.html?v=210615-3',
  './templates/interventionEditor.html?v=210615-3',
  './templates/donations.html?v=210615-3',
  './templates/logistic.html?v=210615-3',
  './templates/debug.html?v=210615-3',
  './css/main.css?v=210615-3',
];

function cache(request, response) {
  if (request.url.includes("/api/") || response.type === "error" || response.type === "opaque") {
    return Promise.resolve(); // do not put in cache network errors or API calls
  }
  return caches
    .open(CACHE_NAME)
    .then(cache => cache.put(request, response.clone()));
}

self.addEventListener("install", event => {
  console.log("Service Worker installing...");
  // Activate new service worker even if previous one is still linked to clients
  self.skipWaiting();
  // Preload all the static cache URLs
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_CACHE_URLS))
  );
  console.log("Service Worker installed");
});

self.addEventListener("fetch", event => {
  // Stratégie Cache-First
  event.respondWith(
    caches
      .match(event.request) // On vérifie si la requête a déjà été mise en cache
      .then(cached => cached || fetch(event.request)) // sinon on requête le réseau
      .then(
        response =>
          cache(event.request, response) // on met à jour le cache
            .then(() => response) // et on résout la promesse avec l'objet Response
      )
  );
});

self.addEventListener("activate", event => {
  console.log("Service Worker activating...");
  // delete any unexpected caches
  event.waitUntil(
    caches
      .keys()
      .then(keys => keys.filter(key => key !== CACHE_NAME))
      .then(keys =>
        Promise.all(
          keys.map(key => {
            console.log(`Deleting cache ${key}`);
            return caches.delete(key);
          })
        )
      )
  );
  console.log("Service Worker activated");
});
