const STATIC_CACHE = "static";
const PAGES_CACHE = "pages";
const static_urls = []; //Add yours statics urls here
const pages_urls = []; //Add the pages you want to cache to load offline here
const staticReg = /\b^css|js|woff2|webp|json|jpg|png*\b/;
const browser_extensions = /^chrome-extension:/;
const p_reg = "^\\" + pages_urls.map((u) => u + "$").join("|\\") + "\\b";
const pagesReg = new RegExp(p_reg);

self.addEventListener("install", function (event) {
  //Open chache storage for statics & pages
  event.waitUntil(
    (async function () {
      const staticCache = await caches.open(STATIC_CACHE);
      const pagesCache = await caches.open(PAGES_CACHE);
      pagesCache.addAll(pages_urls);
      staticCache.addAll(static_urls);
      self.skipWaiting();
    })()
  );
});

self.addEventListener("fetch", (event) => {
  if (
    //GET: No support for other requests: see https://w3c.github.io/ServiceWorker/#cache-put No.4
    event.request.clone().method === "GET" &&
    !browser_extensions.test(event.request.url) && // Omit browser extensions
    !/\/admin\//.test(event.request.url) //Omit admin statics
  ) {
    const reqUrl = event.request.url.split(".");
    const ext = reqUrl[reqUrl.length - 1]; //Get extention type
    const pageurl = new URL(event.request.url).pathname;
    if (staticReg.test(ext)) {
      //cache statics
      event.respondWith(
        (async () => {
          const staticCache = await caches.open(STATIC_CACHE);
          const staticCachedResponse = await staticCache.match(event.request);
          if (staticCachedResponse) {
            //Return statics from cashe
            revalidateResponse(staticCache, event.request);
            return staticCachedResponse;
          } else {
            const networkResponsePromise = await fetch(event.request.clone());
            await staticCache.put(
              event.request,
              networkResponsePromise.clone()
            );
            return networkResponsePromise;
          }
        })()
      );
    } else if (pagesReg.test(pageurl)) {
      //cache pages
      event.respondWith(
        (async () => {
          const pageCache = await caches.open(PAGES_CACHE);
          const pageCachedResponse = await pageCache.match(event.request);
          const networkResponsePromise = fetch(event.request).catch(
            () => pageCachedResponse //fallback to cached pages if no network
          );
          await event.waitUntil(
            (async function () {
              const networkResponse = await networkResponsePromise;
              await pageCache.put(event.request, networkResponse.clone());
            })()
          );
          return networkResponsePromise;
        })()
      );
    }
  }
});
async function revalidateResponse(storage, request) {
  const networkResponsePromise = await fetch(request.clone());
  await storage.put(request, networkResponsePromise.clone());
}
self.addEventListener("push", function (event) {
  if (!(self.Notification && self.Notification.permission === "granted")) {
    return;
  }
  const sendNotification = (body) => {
    // you could refresh a notification badge here with postMessage API
    const payload = JSON.parse(body);
    return self.registration.showNotification(payload.title, {
      body: payload.body,
      icon: payload.icon,
      badge: payload.badge,
      vibrate: payload.vibrate,
      data: payload.data,
      dir: payload.dir,
      image: payload.image,
      actions: payload.actions,
    });
  };
  if (event.data) {
    const message = event.data.text();
    event.waitUntil(sendNotification(message));
  }
});

self.addEventListener("notificationclick", (event) => {
  event.notification.close();
  const action_link = !!event.notification?.data
    ? event.notification.data.link
    : "";

  if (event.action === "close") {
    return;
  } else if (
    (event.action === "action" && !!action_link) || //Clicked on action btn
    (!event.action && !!action_link) //click on msg it self
  ) {
    event.waitUntil(
      clients
        .matchAll({ includeUncontrolled: true, type: "window" })
        .then((clientsArr) => {
          // If a Window tab exists => true
          const hasWindow = clientsArr.some((windowClient) => {
            if (windowClient.url === action_link) {
              windowClient.focus();
              return true;
            } else {
              return false;
            }
          });
          // Otherwise, open a new tab
          if (!hasWindow) {
            return clients.openWindow(action_link);
          }
        })
        .then(() => {
          const isCampaign =
            !!event.notification?.data &&
            !!event.notification?.data?.campaign_id &&
            !!event.notification?.data?.url;

          if (isCampaign) {
            fetch(
              `${event.notification.data.url}?route=extension/module/easywebpush/reportopen&campaign_id=${event.notification.data.campaign_id}`
            );
          }
        })
    );
  }
});
