var EasyWebpushSW = {
  applicationServerKey:
    "BI5PjOjLjyaQSOsad3tuzM8c5DsxN7GwYn4GeJk-Kig3WVFSfBtOm5E2_l-Y2GaGsvuC0qM7KaalgJse8HmRH78",
  getPWAbtn: function () {
    return $("#pwa_app_install");
  },
  register: function () {
    if ("serviceWorker" in navigator) {
      navigator.serviceWorker.register("/sw.js").then(
        function (registration) {
          // Registration was successful
          console.log(
            `ServiceWorker registration successful with scope: ${registration.scope}`
          );
        },
        function (err) {
          // registration failed :(
          console.log("ServiceWorker registration failed: ", err);
        }
      );
    } else {
      // Hide element ?
    }
  },
  disableButton: function () {
    // $("#easywebpush_subscribe").text("{{ something }}");
    // $("#easywebpush_subscribe").prop("disabled", true);
    // $("#easywebpush_subscribe").button("loading");
  },
  loading: function (isLoading = true) {
    const state = isLoading ? "loading" : "reset";
    // $("#easywebpush_subscribe").button(state);
  },
  checkSupport: function () {
    let supported = true;
    //Check support
    if (!("serviceWorker" in navigator)) {
      console.warn("Sorry: browser doesn't support servieworker");
      supported = false;
    }
    if (!("PushManager" in window)) {
      console.warn("Sorry: browser doesn't support webpush");
      supported = false;
    }
    if (!("showNotification" in ServiceWorkerRegistration.prototype)) {
      console.warn("Sorry: browser doesn't support showing webpush");
      supported = false;
    }
    if (!supported) {
      // this.disableSubscription();
    }
    return supported;
  },
  requestPermission: async function () {
    try {
      const hasSupport = this.checkSupport();
      if (!hasSupport) {
        throw new Error("Browser not supported.");
      }
      if (Notification.permission === "denied") {
        throw new Error("Push messages are blocked.");
      }
      if (Notification.permission === "granted") {
        return true;
      }
      if (Notification.permission === "default") {
        const result = await Notification.requestPermission();
        if (result !== "granted") {
          throw new Error("Bad permission result");
        }
        return true;
      }
    } catch (error) {
      throw error;
    }
  },
  isSubscribed: async function () {
    try {
      if (Notification.permission === "denied") {
        console.warn(
          "Sorry: You've disabled notification permission manually, you may re-enable them in browser settings"
        );
        return false;
      }
      if (Notification.permission === "granted") {
        //make sure if granted but unsubscribed
        const registration = await navigator.serviceWorker.getRegistration();
        if (!registration) {
          return false;
        }
        const subscription = await registration.pushManager.getSubscription();
        if (!subscription) {
          return false;
        }
        const isRemoteSubscribed = await this.remoteIsSubscribed(subscription);
        return isRemoteSubscribed;
      }
      return false;
    } catch (error) {
      throw error;
    }
  },
  subscribe: async function () {
    try {
      await this.requestPermission();
      const serviceWorkerRegistration = await navigator.serviceWorker.ready;
      const subscription =
        await serviceWorkerRegistration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: this.urlBase64ToUint8Array(
            this.applicationServerKey
          ),
        });
      const remoteResult = await this.remoteSubscribe(subscription);
      return remoteResult;
    } catch (error) {
      if (Notification.permission === "denied") {
        // this.canceledByUserNotice();
      } else {
        // this.canceledByUnknownNotice(error.message);
      }
      throw error;
    }
  },
  unsubscribe: async function () {
    try {
      const registration = await navigator.serviceWorker.ready;
      const subscription = await registration.pushManager.getSubscription();

      if (!subscription) {
        //No subscription registered
        console.warn("We did not detect previous registration in browser");
        return;
      }

      const serverUnsubscription = await this.remoteUnsubscribe(subscription);
      if (serverUnsubscription.success) {
        await subscription.unsubscribe();
      }
      return serverUnsubscription;
    } catch (error) {
      if (Notification.permission === "denied") {
        // this.canceledByUserNotice();
      } else {
        // this.canceledByUnknownNotice(error.message);
      }
      throw error;
    }
  },
  remoteSubscribe: async function (subscription) {
    return $.ajax({
      url: "index.php?route=extension/module/easywebpush/subscribe",
      type: "POST",
      cache: false,
      data: { subscription: JSON.stringify(subscription) },
      dataType: "json",
      beforeSend: () => {},
      complete: () => {},
      success: function (json) {
        return json;
      },
      error: function (xhr, ajaxOptions, thrownError) {
        console.log(thrownError, xhr.responseText);
        return thrownError;
      },
    });
  },
  remoteUnsubscribe: async function (subscription) {
    return $.ajax({
      url: "index.php?route=extension/module/easywebpush/unsubscribe",
      type: "POST",
      cache: false,
      data: { subscription: JSON.stringify(subscription) },
      dataType: "json",
      beforeSend: function () {},
      complete: function () {},
      success: function (json) {
        return json;
      },
      error: function (err) {
        console.log(thrownError, xhr.responseText);
        return thrownError;
      },
    });
  },
  remoteIsSubscribed: async function (subscription) {
    return $.ajax({
      url: "index.php?route=extension/module/easywebpush/isSubscribed",
      type: "POST",
      cache: false,
      data: { subscription: JSON.stringify(subscription) },
      dataType: "json",
      beforeSend: () => {},
      complete: () => {},
      success: function (json) {
        return json;
      },
      error: function (err) {
        console.log(err);
      },
    });
  },
  urlBase64ToUint8Array: function (base64String) {
    const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
      .replace(/\-/g, "+")
      .replace(/_/g, "/");
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  },
  initAppInstall: function () {
    if (!this.getPWAbtn()) return;
    this.getPWAbtn().display = "none";
    if (navigator.standalone) {
      console.log("Installed (iOS)");
    } else if (matchMedia("(display-mode: standalone)").matches) {
      console.log("Installed");
    } else {
      //Using browser: show button and listen download click
      this.getPWAbtn().display = "block";
      this.SuggestAppInstall();
    }
  },
  SuggestAppInstall: function () {
    window.addEventListener("beforeinstallprompt", (e) => {
      e.preventDefault();
      deferredPrompt = e;
      this.getPWAbtn().click((e) => {
        deferredPrompt.prompt();
        // Wait for user response
        deferredPrompt.userChoice.then((choiceResult) => {
          if (choiceResult.outcome === "accepted") {
            //console.log("User Accepted");
          } else {
            //console.log("User dismissed");
          }
        });
      });
    });
  },
};

$(document).ready(function () {
  EasyWebpushSW.register();
  EasyWebpushSW.initAppInstall();
});
