(function () {
  "use strict";
  document.documentElement.setAttribute("data-mgrid-theme", "regal-rose");
  try {
    localStorage.removeItem("mgrid.theme");
  } catch (e) {}

  document.querySelectorAll("[data-score-ring]").forEach(function (el) {
    var pct = Math.min(100, Math.max(0, parseFloat(el.dataset.scoreRing || "0")));
    var fill = el.querySelector(".mgrid-score-ring-fill");
    if (fill) {
      fill.style.strokeDashoffset = String(283 - (283 * pct) / 100);
    }
  });

  var sidebar = document.getElementById("mgridSidebar");
  var openBtn = document.getElementById("mgridSidebarToggle");
  var closeBtn = document.getElementById("mgridSidebarClose");
  if (sidebar && openBtn) {
    openBtn.addEventListener("click", function () {
      sidebar.classList.add("is-open");
    });
  }
  if (sidebar && closeBtn) {
    closeBtn.addEventListener("click", function () {
      sidebar.classList.remove("is-open");
    });
  }

  document.querySelectorAll("[data-mgrid-flash-dismiss]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var wrap = btn.closest(".mgrid-alert");
      if (wrap) wrap.remove();
    });
  });

  function initReducedMotion() {
    try {
      if (window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
        document.documentElement.classList.add("mgrid-motion-reduce");
      }
    } catch (e) {}
  }

  function initTopbarScroll() {
    var topbar = document.querySelector(".mgrid-topbar");
    if (!topbar) return;
    var onScroll = function () {
      if (window.scrollY > 6) {
        topbar.classList.add("is-scrolled");
      } else {
        topbar.classList.remove("is-scrolled");
      }
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  function getMeta(name) {
    var m = document.querySelector('meta[name="' + name + '"]');
    return m ? (m.getAttribute("content") || "") : "";
  }

  function initIdleLogout() {
    var timeoutRaw = getMeta("mgrid-idle-timeout-ms");
    var logoutUrl = getMeta("mgrid-logout-url");
    var timeoutMs = parseInt(timeoutRaw || "0", 10);
    if (!logoutUrl || !timeoutMs || timeoutMs < 10000) return;

    var timer = null;
    var resetTimer = function () {
      if (timer) window.clearTimeout(timer);
      timer = window.setTimeout(function () {
        window.location.href = logoutUrl;
      }, timeoutMs);
    };

    ["click", "keydown", "mousemove", "scroll", "touchstart"].forEach(function (evt) {
      window.addEventListener(evt, resetTimer, { passive: true });
    });
    document.addEventListener("visibilitychange", function () {
      if (!document.hidden) resetTimer();
    });
    resetTimer();
  }

  initReducedMotion();
  initTopbarScroll();
  initIdleLogout();
})();
