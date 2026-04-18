/**
 * home-apps-stack.js
 * -----------------------------------------------------------------------------
 * Drives the folded Applications slideshow on the homepage (B1.3).
 *
 * HTML shape (rendered in /en/index.html):
 *
 *   <section class="home-apps home-apps--stack">            ← 300svh tall
 *     <div class="home-apps__stage">                        ← sticky, 100svh
 *       <div class="home-apps__slides">
 *         <a class="home-apps__tile is-active" data-index="0">…</a>
 *         <a class="home-apps__tile"           data-index="1">…</a>
 *         <a class="home-apps__tile"           data-index="2">…</a>
 *       </div>
 *     </div>
 *   </section>
 *
 * As the user scrolls through the 300svh stack, the stage stays pinned
 * and the active tile is computed from the stack's progress within that
 * range (0–1). Each 1/N of the range activates the matching tile.
 *
 * No wheel hijacking — native scroll carries the user in and out of the
 * stack. Transitions (fade + slide + background lift) live in CSS.
 */

(() => {
  'use strict';

  const stack = document.querySelector('.home-apps--stack');
  if (!stack) return;

  const tiles = Array.from(stack.querySelectorAll('.home-apps__tile'));
  const N     = tiles.length;
  if (N === 0) return;

  let currentIdx = -1;
  let ticking    = false;

  const setActive = (idx) => {
    if (idx === currentIdx) return;
    currentIdx = idx;
    tiles.forEach((t, i) => t.classList.toggle('is-active', i === idx));
  };

  const update = () => {
    // Disabled layout: mobile/reduced-motion CSS swaps the tiles to
    // normal flow — just keep tile 0 marked active for analytics parity.
    if (getComputedStyle(stack).height === 'auto' ||
        window.innerWidth <= 900) {
      setActive(0);
      return;
    }

    const rect  = stack.getBoundingClientRect();
    const range = stack.offsetHeight - window.innerHeight;
    if (range <= 0) { setActive(0); return; }

    // 0 when stack top meets viewport top; 1 when stack bottom meets
    // viewport bottom. Clamp so outside the stack doesn't misreport.
    const progress = Math.max(0, Math.min(1, -rect.top / range));

    // Use the midpoint of each slide's range as the switch point so
    // the active tile feels in sync with the user's scroll position.
    const idx = Math.min(N - 1, Math.floor(progress * N + 0.0001));
    setActive(idx);
  };

  const onScroll = () => {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => { update(); ticking = false; });
  };

  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll, { passive: true });

  update();
})();
