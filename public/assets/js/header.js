/**
 * header.js
 * -----------------------------------------------------------------------------
 * Drives the fixed site header's three scroll-linked behaviors:
 *
 *   1. Scroll threshold  → toggle `.is-scrolled` (frosted glass background)
 *   2. Scroll direction  → toggle `.is-hidden` (hide on down, show on up)
 *   3. Background context → toggle `.is-over-light` / `.is-over-dark`
 *      based on sections tagged `[data-header-bg="light"|"dark"]`,
 *      so the header text/logo contrasts correctly as the user scrolls.
 *
 * Boots after `partials:loaded` (since the header is injected by
 * partials-loader.js) or on DOMContentLoaded if the header is inlined.
 *
 * No dependencies. ~2 KB gzipped.
 */

(() => {
  'use strict';

  const SCROLL_THRESHOLD = 24;     // px before .is-scrolled engages
  const HIDE_THRESHOLD   = 120;    // min scrollY before auto-hide kicks in
  const DELTA_HIDE       = 8;      // px of downward movement to hide
  const DELTA_SHOW       = 4;      // px of upward movement to show

  const state = {
    header: null,
    lastY: 0,
    ticking: false,
    bgObserver: null,
    menuOpen: false,
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tryInit);
  } else {
    tryInit();
  }
  document.addEventListener('partials:loaded', tryInit);

  function tryInit() {
    const header = document.querySelector('[data-header], .site-header');
    if (!header || header.dataset.headerBound === '1') return;
    header.dataset.headerBound = '1';
    state.header = header;

    state.lastY = window.scrollY;
    applyScrollState();

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });

    // Keep header visible while the mobile drawer is open — users need to
    // see the close affordance even if they scroll inside the drawer.
    document.addEventListener('mobilenav:open', () => {
      state.menuOpen = true;
      header.classList.remove('is-hidden');
      document.documentElement.classList.remove('header-hidden');
    });
    document.addEventListener('mobilenav:close', () => {
      state.menuOpen = false;
    });

    setupBgContext();
  }


  /* ─── Scroll handler (rAF-batched) ─────────────────────────────────── */

  function onScroll() {
    if (state.ticking) return;
    state.ticking = true;
    requestAnimationFrame(() => {
      applyScrollState();
      state.ticking = false;
    });
  }

  function applyScrollState() {
    const { header, lastY } = state;
    const y = window.scrollY;

    // 1. Frosted-glass state
    header.classList.toggle('is-scrolled', y > SCROLL_THRESHOLD);

    // 2. Hide-on-scroll-down
    if (!state.menuOpen) {
      const delta = y - lastY;
      if (y < HIDE_THRESHOLD) {
        setHidden(false);
      } else if (delta > DELTA_HIDE) {
        setHidden(true);
      } else if (delta < -DELTA_SHOW) {
        setHidden(false);
      }
    }

    state.lastY = y;
  }

  // Mirror the header's hidden state onto <html> so sticky widgets below
  // the header (filter bars, sub-nav) can drop their top offset to 0 when
  // the header slides up, and ride it back down when it reappears.
  function setHidden(hidden) {
    state.header.classList.toggle('is-hidden', hidden);
    document.documentElement.classList.toggle('header-hidden', hidden);
  }


  /* ─── Background-context observer ──────────────────────────────────────
     Sections tagged with data-header-bg="light" or "dark" tell the header
     what kind of backdrop it's currently over, so we swap the text color
     class while the header is transparent (pre-scroll).
     -------------------------------------------------------------------- */

  function setupBgContext() {
    const sections = document.querySelectorAll('[data-header-bg]');
    if (sections.length === 0) return;

    // Observe a thin band right under the header. When a section crosses
    // that band, it becomes the current backdrop.
    const rootMargin = `-${getHeaderHeight() + 8}px 0px -${
      Math.max(0, window.innerHeight - getHeaderHeight() - 16)
    }px 0px`;

    if (state.bgObserver) state.bgObserver.disconnect();

    state.bgObserver = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          if (!entry.isIntersecting) continue;
          const tone = entry.target.getAttribute('data-header-bg');
          state.header.classList.toggle('is-over-light', tone === 'light');
          state.header.classList.toggle('is-over-dark',  tone === 'dark');
        }
      },
      { rootMargin, threshold: 0 }
    );

    sections.forEach((s) => state.bgObserver.observe(s));

    // Recompute rootMargin on significant viewport resize.
    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(setupBgContext, 200);
    });
  }

  function getHeaderHeight() {
    const cs = getComputedStyle(state.header);
    return parseInt(cs.height, 10) || 64;
  }
})();
