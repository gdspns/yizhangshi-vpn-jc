(function () {
  "use strict";

  function syncRoute() {
    var route = window.location.hash.replace(/^#/, "").split("?")[0] || "/";
    document.body.setAttribute("data-xg-route", route);
    if (["/login", "/register", "/forgetpassword"].indexOf(route) !== -1) {
      document.documentElement.classList.add("dark");
      document.body.classList.add("dark");
    }
  }

  window.addEventListener("hashchange", syncRoute, { passive: true });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", syncRoute, { once: true });
  } else {
    syncRoute();
  }

  new MutationObserver(function () {
    var route = document.body.getAttribute("data-xg-route");
    if (["/login", "/register", "/forgetpassword"].indexOf(route) !== -1 && !document.documentElement.classList.contains("dark")) {
      document.documentElement.classList.add("dark");
      document.body.classList.add("dark");
    }
  }).observe(document.documentElement, { attributes: true, attributeFilter: ["class"] });
})();
