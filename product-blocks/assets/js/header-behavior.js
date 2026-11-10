/* Header show/hide behaviour on scroll — jQuery style
 * - Uses rAF throttling
 * - Respects prefers-reduced-motion
 * - Adds/removes `wopb-scroll-visible` / `wopb-scroll-hidden` classes for scroll headers
 * - Positions headers fixed to viewport
 * CSS for transitions lives in assets/css/wopb.css
 */
(function ($) {
  "use strict";

  $(function () {
    let $win = $(window);
    let $header = $("#wopb-header-template");
    if (!$header.length) {
      return;
    }

    // detect WP admin bar and compute its height so fixed headers sit below it
    let $wpadminbar = $("#wpadminbar");
    function getAdminBarHeight() {
      return $wpadminbar.length ? $wpadminbar.outerHeight() : 0;
    }
    let adminBarHeight = getAdminBarHeight();

    let $stickyHeaders = $header.find(".wopb-sticky-header");
    let $scrollHeaders = $header.find(".wopb-sticky-scroll-header");

    // Respect users who prefer reduced motion: keep scroll headers visible and exit
    if (
      window.matchMedia &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches
    ) {
      $scrollHeaders.addClass("wopb-scroll-visible");
      return;
    }

    // Build arrays with original document offsets and heights
    function collectItems($elements) {
      let arr = [];
      $elements.each(function () {
        let $el = $(this);
        arr.push({
          $el: $el,
          el: this,
          originalTop: $el.offset().top,
          height: $el.outerHeight(),
          fixed: false,
          originalBackground: $el.css("background-color"),
          originalOpacity: $el.css("opacity"),
        });
      });
      return arr;
    }

    let stickyArr = collectItems($stickyHeaders);
    let scrollArr = collectItems($scrollHeaders);

    function updateOffsets() {
      // recalc admin bar height on resize
      adminBarHeight = getAdminBarHeight();
      stickyArr.forEach(function (it) {
        it.originalTop = it.$el.offset().top;
        it.height = it.$el.outerHeight();
      });
      scrollArr.forEach(function (it) {
        it.originalTop = it.$el.offset().top;
        it.height = it.$el.outerHeight();
      });
    }

    // helper: apply fixed styles
    function applyFixed(it, top) {
      // ensure fixed element sits below the WP admin bar (if present)
      let topOffset = (adminBarHeight || 0) + top;
      it.$el.css({
        position: "fixed",
        top: topOffset + "px",
        left: "0",
        right: "0",
        "z-index": "9999",
        "background-color": it.originalBackground || "",
        opacity: it.originalOpacity || "1",
      });
      it.fixed = true;
    }

    function removeFixed(it) {
      it.$el.css({
        position: "",
        top: "",
        left: "",
        right: "",
        "z-index": "",
        "background-color": "",
        opacity: "",
        transform: "",
      });
      it.fixed = false;
    }

    // keep track of whether the user has started scrolling (load should stay normal)
    let hasStartedScrolling = false;
    let lastScroll = window.pageYOffset || document.documentElement.scrollTop;
    let DELTA = 10; // px threshold
    let ticking = false;

    // initialise visible class for scroll headers (they remain visible in-place until interaction)
    $scrollHeaders.addClass("wopb-scroll-visible");

    function handleScroll() {
      // refresh admin bar height in case it changed (responsive/admin toggles)
      adminBarHeight = getAdminBarHeight();
      let current = window.pageYOffset || document.documentElement.scrollTop;
      if (!hasStartedScrolling) {
        hasStartedScrolling = true; // first actual scroll - start applying sticky logic
      }

      // If we're back at the absolute top, fully reset headers to their default state
      if (current <= 0) {
        // remove fixed styles and restore visibility for all items
        stickyArr.forEach(function (it) {
          if (it.fixed) removeFixed(it);
        });
        scrollArr.forEach(function (it) {
          if (it.fixed) removeFixed(it);
          it.$el.removeClass("wopb-scroll-hidden").addClass("wopb-scroll-visible");
        });

        // remove any padding applied to the header
        $header.css("padding-top", "");

        lastScroll = current;
        return;
      }

      // compute stacking: fixed items accumulate from top
      let currentTop = 0;

      // Sticky headers: become fixed when scrolled past their original position
      stickyArr.forEach(function (it) {
        if (current >= it.originalTop - currentTop) {
          if (!it.fixed) applyFixed(it, currentTop);
          currentTop += it.height;
        } else {
          if (it.fixed) removeFixed(it);
        }
      });

      // Scroll headers: fixed similar to sticky headers, but also hide on scroll-down and show on scroll-up
      scrollArr.forEach(function (it) {
        if (current >= it.originalTop - currentTop) {

          if (!it.fixed) applyFixed(it, currentTop);

          // Visibility behavior for scroll headers
          // When hiding a stacked header we must translate it fully out of the
          // viewport. Using `translateY(-100%)` isn't sufficient when multiple
          // fixed headers stack, because the element's top offset (from
          // previous headers + admin bar) keeps part of it visible. Compute a
          // pixel-based translate so the element moves above the viewport.
          if (current <= 50) {
            it.$el
              .removeClass("wopb-scroll-hidden")
              .addClass("wopb-scroll-visible");
            it.$el.css("transform", "translateY(0)");
          } else if (Math.abs(current - lastScroll) > DELTA) {
            if (current > lastScroll) {
              // hide: translate the element by -(adminBarHeight + currentTop + its height)
              const topOffset = (adminBarHeight || 0) + currentTop;
              const hideBy = -(topOffset + it.height);
              it.$el
                .removeClass("wopb-scroll-visible")
                .addClass("wopb-scroll-hidden");
              it.$el.css("transform", "translateY(" + hideBy + "px)");
            } else {
              // show: reset transform so it sits at its fixed position
              it.$el
                .removeClass("wopb-scroll-hidden")
                .addClass("wopb-scroll-visible");
              it.$el.css("transform", "translateY(0)");
            }
          }

          currentTop += it.height;
        } else {
          if (it.fixed) removeFixed(it);
          // ensure in-place scroll headers are visible by default
          it.$el
            .removeClass("wopb-scroll-hidden")
            .addClass("wopb-scroll-visible");
        }
      });

      // adjust header padding to prevent layout jump only when items are fixed
      if (currentTop > 0) {
        $header.css("padding-top", currentTop + "px");
      } else {
        $header.css("padding-top", "");
      }

      lastScroll = current;
    }

    function rafThrottled() {
      if (ticking) {
        return;
      }
      ticking = true;
      window.requestAnimationFrame(function () {
        handleScroll();
        ticking = false;
      });
    }

    // attach events
    $win.on("scroll touchmove", rafThrottled);

    $win.on("resize", function () {
      updateOffsets();
      rafThrottled();
    });

    // reveal scroll headers if user interacts with header
    $header.on("mouseenter touchstart pointerenter", function () {
      $scrollHeaders
        .removeClass("wopb-scroll-hidden")
        .addClass("wopb-scroll-visible");
    });
  });
})(jQuery);
