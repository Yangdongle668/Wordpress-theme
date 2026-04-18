/**
 * home-snap.js
 * -----------------------------------------------------------------------------
 * JS-driven one-wheel-tick-per-section navigation for the homepage.
 *
 * CSS scroll-snap with `mandatory` has two real-world failure modes we hit:
 *   1. A slow wheel tick near the top of the hero doesn't cross the
 *      momentum threshold, so the browser snaps *back* to the hero instead
 *      of forward to section 2. User sees a stuck/partial scroll.
 *   2. Past the last snap point, `mandatory` won't release the scroll — the
 *      user can't reach sections 6+ (stats/tech/cases/blog/cta/footer).
 *
 * This module intercepts wheel events only while the user is within the
 * first five snap sections (hero + products + 3 application tiles) and
 * programmatically scrolls to the next/previous one with a cooldown so
 * momentum-scroll trackpads don't machine-gun through slides.
 *
 * Disabled on:
 *   - non-home pages (no .page-home class on body)
 *   - viewports narrower than 900px (touch-first layout)
 *   - prefers-reduced-motion users
 *   - ctrl+wheel (browser zoom)
 */

(() => {
  'use strict';

  if (!document.body.classList.contains('page-home')) return;

  const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const narrow       = matchMedia('(max-width: 900px)').matches;
  if (reduceMotion || narrow) return;

  const SNAP_SELECTOR = '.home-hero, .home-products, .home-apps__tile';
  const COOLDOWN_MS   = 900;   // matches typical smooth-scroll duration
  const DELTA_MIN     = 6;     // ignore trackpad noise below this

  let sections   = [];
  let lastScroll = 0;

  const gather = () => {
    sections = Array.from(document.querySelectorAll(SNAP_SELECTOR));
  };

  // Find the snap section closest to the current scroll position.
  const currentIdx = () => {
    if (!sections.length) return -1;
    const y = window.scrollY;
    let best = 0, bestDist = Infinity;
    sections.forEach((s, i) => {
      const top = s.getBoundingClientRect().top + y;
      const dist = Math.abs(top - y);
      if (dist < bestDist) { bestDist = dist; best = i; }
    });
    return best;
  };

  // True once the bottom of the last snap section is above the viewport —
  // user is in sections 6+, free scroll from there.
  const pastLastSnap = () => {
    if (!sections.length) return false;
    const last = sections[sections.length - 1];
    return last.getBoundingClientRect().bottom <= 1;
  };

  const scrollToIdx = (i) => {
    if (i < 0 || i >= sections.length) return;
    lastScroll = Date.now();
    sections[i].scrollIntoView({ behavior: 'smooth', block: 'start' });
  };

  const onWheel = (e) => {
    if (e.ctrlKey || e.metaKey) return;      // pinch-zoom, don't interfere

    const goingDown = e.deltaY > 0;

    // Below section 5: let native scroll run the whole rest of the page.
    if (pastLastSnap() && goingDown) return;

    const now = Date.now();
    if (now - lastScroll < COOLDOWN_MS) {
      e.preventDefault();
      return;
    }
    if (Math.abs(e.deltaY) < DELTA_MIN) return;

    const idx = currentIdx();

    // At the last snap section, scrolling down should release into native
    // scroll so sections 6+ are reachable — this is the fix for the
    // "stuck at section 5" bug.
    if (goingDown && idx >= sections.length - 1) return;

    // Above the first section going up: no-op.
    if (!goingDown && idx <= 0 && window.scrollY <= 2) return;

    e.preventDefault();
    scrollToIdx(idx + (goingDown ? 1 : -1));
  };

  const init = () => {
    gather();
    window.addEventListener('resize', gather, { passive: true });
    window.addEventListener('wheel', onWheel, { passive: false });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
