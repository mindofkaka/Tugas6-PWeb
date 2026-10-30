const CACHE = "todolist-app-v1"
const SHELL = ["offline.html", "style.css", "script.js"]
const CDN = "https://storage.googleapis.com/workbox-cdn/releases/7.3.0"

importScripts(`${CDN}/workbox-sw.js`)

self.addEventListener("install", e => 
    e.waitUntil(caches.open(CACHE)
    .then(c => c.addAll(SHELL)))
)

self.addEventListener("activate", e => 
    e.waitUntil(caches.keys().then(ks => 
        Promise.all(ks.filter(k => 
            k.startsWith("todolist-app-" && k !== CACHE)
            .map(k => caches.delete(k))
        ))
    ))
)

self.addEventListener("fetch", e => {
    if(e.request.method !== "GET") return 
    e.respondWith(
        caches.match(e.request)
        .then(r => r || fetch(e.request))
    )
})

workbox.precaching.precacheAndRoute([
    { "url": "offline.html", "revision": "1" },
    { "url": "style-offline.css", "revision": "1" },
    { "url": "script.js", "revision": "1" },
])
workbox.precaching.cleanupOutdatedCaches()

workbox.routing.registerRoute(
    ({ request }) => request.mode === "navigate",
    new strategies.NetworkFirst({
        cacheName: "pages",
        networkTimeoutSeconds: 3,
        plugins: [new expiration.ExpirationPlugin({ maxEntries: 10 })],
    })
)

workbox.routing.setCatchHandler( async ({request}) => {
    if(request.destination === "document") {
        return precaching.matchPrecache("offline.html")
    }
    return Response.error
})

if("serviceWorker" in navigator) {
    window.addEventListener("load", async() => {
        try {
            await navigator.serviceWorker.register("sw.js")
            console.log("Service Worker Registered")
        }catch(err) {
            console.error("Failed:", err)
        }
    })
}

