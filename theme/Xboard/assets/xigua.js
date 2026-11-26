(function () {
  "use strict";

  function syncRoute() {
    var route = window.location.hash.replace(/^#/, "").split("?")[0] || "/";
    document.body.setAttribute("data-xg-route", route);
  }

  window.addEventListener("hashchange", syncRoute, { passive: true });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", syncRoute, { once: true });
  } else {
    syncRoute();
  }
})();
