(function () {
  "use strict";

  // VueUse defaults to the operating-system colour scheme when this key is
  // absent.  VPSCS is dark by default; an explicit user choice is still kept.
  try {
    if (window.localStorage.getItem("vueuse-color-scheme") === null) {
      window.localStorage.setItem("vueuse-color-scheme", "dark");
    }
  } catch (_) {}

  function syncRoute() {
    var route = window.location.hash.replace(/^#/, "").split("?")[0] || "/";
    document.body.setAttribute("data-xg-route", route);
    document.body.classList.toggle("dark", document.documentElement.classList.contains("dark"));
  }

  window.addEventListener("hashchange", syncRoute, { passive: true });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", syncRoute, { once: true });
  } else {
    syncRoute();
  }

  new MutationObserver(function () {
    document.body.classList.toggle("dark", document.documentElement.classList.contains("dark"));
  }).observe(document.documentElement, { attributes: true, attributeFilter: ["class"] });
})();
