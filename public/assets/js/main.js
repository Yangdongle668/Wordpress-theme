/**
 * main.js
 * -----------------------------------------------------------------------------
 * Main entry point. Loaded with `defer` on every page AFTER the module scripts
 * (partials-loader, header, mobile-nav, reveal, back-to-top). Its job is to:
 *
 *   1. Expose a tiny `window.Site` namespace for page-level scripts
 *   2. Apply small cross-page enhancements that don't warrant their own file:
 *        - Smooth scroll for same-page anchor links (honors reduced-motion)
 *        - Mark external links with rel="noopener noreferrer" + target="_blank"
 *        - Auto-update the footer year
 *        - Lazy-init videos with data-autoplay-on-visible
 *        - Keep focus-visible behavior consistent across browsers
 *   3. Emit `site:ready` on document once everything above has run
 *
 * Design:
 *   - No module system, no build step. ES2019 syntax only.
 *   - Everything is optional — if an element isn't on the page, the feature
 *     silently no-ops.
 */

(() => {
  'use strict';

  /* ─── Site namespace ─────────────────────────────────────────────────── */

  const Site = (window.Site = window.Site || {});

  Site.version = '0.1.0';

  /**
   * Safe DOM helper: run `fn` once both the DOM is ready AND partials have
   * been injected. Useful for page scripts that reference the injected
   * header/footer.
   */
  Site.ready = function (fn) {
    let fired = false;
    const run = () => {
      if (fired) return;
      fired = true;
      fn();
    };
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => {
        document.addEventListener('partials:loaded', run, { once: true });
        // Fallback: if there are no partials on this page, fire on next tick.
        if (!document.querySelector('[data-include]')) {
          queueMicrotask(run);
        }
      });
    } else {
      if (document.querySelector('[data-include]')) {
        document.addEventListener('partials:loaded', run, { once: true });
      } else {
        queueMicrotask(run);
      }
    }
  };

  /**
   * Small matchMedia cache so modules share the same MQL instead of
   * each spawning their own.
   */
  Site.media = {
    _cache: new Map(),
    match(query) {
      let mql = this._cache.get(query);
      if (!mql) {
        mql = window.matchMedia(query);
        this._cache.set(query, mql);
      }
      return mql;
    },
    get reducedMotion() {
      return this.match('(prefers-reduced-motion: reduce)').matches;
    },
    get isTouch() {
      return this.match('(hover: none) and (pointer: coarse)').matches;
    },
    get isDesktop() {
      return this.match('(min-width: 992.01px)').matches;
    },
  };


  /* ─── Boot ───────────────────────────────────────────────────────────── */

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  function boot() {
    enhanceAnchorLinks();
    enhanceExternalLinks();
    updateFooterYear();
    initVisibleVideos();
    document.addEventListener(
      'partials:loaded',
      () => {
        updateFooterYear();
        enhanceExternalLinks();
        document.dispatchEvent(new CustomEvent('site:ready'));
      },
      { once: true }
    );

    // Fallback for pages without any data-include slots.
    if (!document.querySelector('[data-include]')) {
      queueMicrotask(() => {
        document.dispatchEvent(new CustomEvent('site:ready'));
      });
    }
  }


  /* ─── Smooth anchor scrolling ────────────────────────────────────────── */

  function enhanceAnchorLinks() {
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a[href^="#"]');
      if (!link) return;

      const id = link.getAttribute('href');
      if (id.length <= 1) return;   // just "#"
      const target = document.querySelector(id);
      if (!target) return;

      e.preventDefault();
      target.scrollIntoView({
        behavior: Site.media.reducedMotion ? 'auto' : 'smooth',
        block: 'start',
      });

      // Move focus to the target for a11y parity with real navigation.
      if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
      target.focus({ preventScroll: true });

      // Reflect the anchor in the URL without triggering a jump.
      history.pushState(null, '', id);
    });
  }


  /* ─── External link safety ───────────────────────────────────────────── */

  function enhanceExternalLinks() {
    const here = window.location.hostname;
    document.querySelectorAll('a[href^="http"]').forEach((a) => {
      try {
        const url = new URL(a.href);
        if (url.hostname === here || url.hostname === '') return;
      } catch (_) {
        return;
      }

      if (!a.hasAttribute('target')) a.setAttribute('target', '_blank');
      const relSet = new Set((a.getAttribute('rel') || '').split(/\s+/).filter(Boolean));
      relSet.add('noopener');
      relSet.add('noreferrer');
      a.setAttribute('rel', Array.from(relSet).join(' '));
    });
  }


  /* ─── Footer year auto-update ────────────────────────────────────────── */

  function updateFooterYear() {
    document.querySelectorAll('[data-year]').forEach((el) => {
      el.textContent = String(new Date().getFullYear());
    });
  }


  /* ─── Visible-video autoplay (mobile-safe) ───────────────────────────── */

  function initVisibleVideos() {
    const videos = document.querySelectorAll(
      'video[data-autoplay-on-visible]:not([data-video-bound])'
    );
    if (videos.length === 0) return;

    const observer = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          const v = entry.target;
          if (entry.isIntersecting) {
            const promise = v.play();
            if (promise && typeof promise.catch === 'function') {
              promise.catch(() => { /* autoplay blocked — ignore */ });
            }
          } else {
            v.pause();
          }
        }
      },
      { threshold: 0.25 }
    );

    videos.forEach((v) => {
      v.setAttribute('data-video-bound', '');
      v.muted = true;
      v.playsInline = true;
      observer.observe(v);
    });
  }
})();
