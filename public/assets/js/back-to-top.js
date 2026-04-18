/**
 * back-to-top.js
 * -----------------------------------------------------------------------------
 * Shows / hides the .back-to-top button based on scroll depth and scrolls
 * the page back to the top when clicked.
 *
 * Behavior:
 *   - Appears after the user scrolls past ~60% of the viewport height
 *   - Fades via .is-visible class (CSS handles the transform/opacity)
 *   - Click / keyboard activation scrolls smoothly to top
 *   - Respects prefers-reduced-motion (instant jump instead of smooth)
 *   - rAF-batched scroll handler to avoid layout thrash
 */

(() => {
  'use strict';

  const SHOW_RATIO = 0.6;           // show after scrolling 60% of viewport

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
  document.addEventListener('partials:loaded', init);

  const state = { footerInView: false };

  function init() {
    const btn = document.querySelector('[data-back-to-top], .back-to-top');
    if (!btn || btn.dataset.backTopBound === '1') return;
    btn.dataset.backTopBound = '1';

    btn.addEventListener('click', (e) => {
      e.preventDefault();
      scrollToTop();
      // Return focus to a meaningful landmark after the jump.
      requestAnimationFrame(() => {
        const target = document.querySelector(
          'main, [role="main"], .site-header a, body'
        );
        if (target && typeof target.focus === 'function') {
          target.setAttribute('tabindex', '-1');
          target.focus({ preventScroll: true });
        }
      });
    });

    let ticking = false;
    const onScroll = () => {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(() => {
        applyVisibility(btn);
        ticking = false;
      });
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });

    // Hide the button while the footer is in view, so it doesn't collide
    // visually with the social icon row (both live in the bottom-right).
    observeFooter(btn, onScroll);

    applyVisibility(btn);
  }

  function observeFooter(btn, refresh) {
    const footer = document.querySelector('.site-footer, footer[role="contentinfo"]');
    if (!footer) {
      // Footer may come in via partials-loader later; retry once on load.
      document.addEventListener('partials:loaded', () => observeFooter(btn, refresh), { once: true });
      return;
    }
    const io = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          state.footerInView = entry.isIntersecting;
        }
        refresh();
      },
      { threshold: 0 }
    );
    io.observe(footer);
  }

  function applyVisibility(btn) {
    const threshold = window.innerHeight * SHOW_RATIO;
    const show = window.scrollY > threshold && !state.footerInView;
    btn.classList.toggle('is-visible', show);
  }

  function scrollToTop() {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    window.scrollTo({
      top: 0,
      left: 0,
      behavior: reduced ? 'auto' : 'smooth',
    });
  }
})();
