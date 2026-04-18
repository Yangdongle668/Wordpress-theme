/**
 * reveal.js
 * -----------------------------------------------------------------------------
 * Two small scroll-linked primitives used across the site:
 *
 *   1. Reveal on scroll — any element with [data-reveal] fades/slides into
 *      view when it crosses into the viewport. Direction is picked via
 *      [data-reveal="fade|up|down|left|right|zoom"] (CSS handles the
 *      initial transform; JS just adds the .is-visible class).
 *
 *      Stagger: set inline style="--stagger: N" on siblings to offset
 *      their transition delay by N * 80ms. Useful for grids and lists.
 *
 *      Once revealed, observers are detached so no further work happens
 *      on scroll. Elements marked [data-reveal-repeat] re-hide when they
 *      leave the viewport (opt-in; disabled by default for performance).
 *
 *   2. Parallax — any element with .parallax receives a CSS variable --p
 *      on scroll (0..1 progress across the viewport). CSS applies the
 *      actual transform (see base.css), so JS stays cheap.
 *
 * Respects prefers-reduced-motion (no-op under that preference).
 * Honors .no-js fallback (CSS already makes revealed elements visible).
 */

(() => {
  'use strict';

  const prefersReducedMotion = window.matchMedia(
    '(prefers-reduced-motion: reduce)'
  ).matches;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
  document.addEventListener('partials:loaded', init);

  function init() {
    if (prefersReducedMotion) {
      // Just flag everything visible so nothing stays hidden.
      document.querySelectorAll('[data-reveal]').forEach((el) => {
        el.classList.add('is-visible');
      });
      return;
    }

    initReveal();
    initParallax();
  }


  /* ─── Reveal on scroll ─────────────────────────────────────────────── */

  function initReveal() {
    const targets = document.querySelectorAll(
      '[data-reveal]:not([data-reveal-bound])'
    );
    if (targets.length === 0) return;

    // Fire a fraction into the element so the reveal feels tied to content,
    // not the hairline top edge. `rootMargin` brings the intersection zone
    // up a bit on tall viewports.
    const observer = new IntersectionObserver(handleReveal, {
      root: null,
      rootMargin: '0px 0px -8% 0px',
      threshold: [0, 0.08, 0.25],
    });

    targets.forEach((el) => {
      el.setAttribute('data-reveal-bound', '');
      observer.observe(el);
    });
  }

  function handleReveal(entries, observer) {
    for (const entry of entries) {
      const el = entry.target;
      if (entry.isIntersecting && entry.intersectionRatio > 0.05) {
        el.classList.add('is-visible');
        if (!el.hasAttribute('data-reveal-repeat')) {
          observer.unobserve(el);
        }
      } else if (el.hasAttribute('data-reveal-repeat')) {
        el.classList.remove('is-visible');
      }
    }
  }


  /* ─── Parallax progress ────────────────────────────────────────────────
     Publishes a CSS variable --p (0..1) on each .parallax element. CSS
     handles the actual transform — JS never touches the style attribute
     beyond setProperty, which avoids layout thrash.
     -------------------------------------------------------------------- */

  function initParallax() {
    const targets = Array.from(
      document.querySelectorAll('.parallax:not([data-parallax-bound])')
    );
    if (targets.length === 0) return;

    targets.forEach((el) => el.setAttribute('data-parallax-bound', ''));

    // Only compute parallax for elements currently on-screen.
    const active = new Set();
    const visibilityObserver = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          if (entry.isIntersecting) active.add(entry.target);
          else active.delete(entry.target);
        }
        scheduleUpdate();
      },
      { rootMargin: '20% 0px 20% 0px' }
    );

    targets.forEach((t) => visibilityObserver.observe(t));

    let ticking = false;
    function scheduleUpdate() {
      if (ticking || active.size === 0) return;
      ticking = true;
      requestAnimationFrame(update);
    }

    function update() {
      const vh = window.innerHeight;
      for (const el of active) {
        const rect = el.getBoundingClientRect();
        // Progress: 0 when the element's top hits the viewport bottom,
        // 1 when its bottom passes the viewport top.
        const total = rect.height + vh;
        const passed = vh - rect.top;
        const p = Math.max(0, Math.min(1, passed / total));
        el.style.setProperty('--p', p.toFixed(4));
      }
      ticking = false;
    }

    window.addEventListener('scroll', scheduleUpdate, { passive: true });
    window.addEventListener('resize', scheduleUpdate, { passive: true });
    scheduleUpdate();
  }
})();
