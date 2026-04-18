/**
 * form.js
 * -----------------------------------------------------------------------------
 * Shared AJAX submission handler for every [data-ajax-form] on the site.
 *
 * Works with the three backend endpoints defined by server/:
 *   POST /api/contact
 *   POST /api/inquiry
 *   POST /api/careers        (multipart/form-data with a `resume` file)
 *
 * Responsibilities:
 *   - Intercept submit, run HTML5 + lightweight custom validation
 *   - Surface per-field errors via the .field__error element next to each input
 *   - Set a global form-status banner (success / error) with aria-live
 *   - Toggle the submit button into .is-loading while the request is in flight
 *   - Pick the right encoding automatically (multipart if a file is present,
 *     urlencoded otherwise)
 *   - On success: show the server-provided message and reset the form
 *   - On 4xx: render server-side validation errors field-by-field
 *   - On 5xx / network error: show a generic retryable message
 *   - Optional reCAPTCHA v3: if `window.__recaptchaSiteKey` is set, fetch a
 *     token before submit and include it in the payload
 *
 * No framework, no build step. Idempotent bind via data-form-bound flag.
 */

(() => {
  'use strict';

  const SELECTOR = 'form[data-ajax-form]';
  const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  /* ─── Boot ─────────────────────────────────────────────────────────── */

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
  document.addEventListener('partials:loaded', init);
  document.addEventListener('site:ready', init);

  function init() {
    document.querySelectorAll(SELECTOR).forEach(bindForm);
  }


  /* ─── Per-form binding ─────────────────────────────────────────────── */

  function bindForm(form) {
    if (form.dataset.formBound === '1') return;
    form.dataset.formBound = '1';

    const statusEl = form.querySelector('.form-status');
    const submitBtn = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      clearStatus(statusEl);
      clearFieldErrors(form);

      if (!validate(form)) {
        setStatus(statusEl, 'error', 'Please fix the highlighted fields and try again.');
        focusFirstInvalid(form);
        return;
      }

      setLoading(submitBtn, true);

      try {
        const payload = await buildPayload(form);
        const res = await fetch(form.action, {
          method: (form.method || 'POST').toUpperCase(),
          body: payload.body,
          headers: payload.headers,
          credentials: 'same-origin',
        });

        let data = {};
        try { data = await res.json(); } catch (_) { /* non-JSON response */ }

        if (res.ok && data.ok) {
          setStatus(statusEl, 'success', data.message || 'Thanks — your message is in.');
          form.reset();
          // Reset file-input preview text if the page provided one
          form.querySelectorAll('[data-file-placeholder]').forEach((n) => {
            if (n.dataset.original) n.textContent = n.dataset.original;
          });
          scrollIntoView(statusEl);
          return;
        }

        // 4xx with per-field errors
        if (res.status >= 400 && res.status < 500 && data.errors) {
          renderServerErrors(form, data.errors);
          setStatus(statusEl, 'error', 'Please fix the highlighted fields and try again.');
          focusFirstInvalid(form);
          return;
        }

        // Rate limit or generic server error
        const msg =
          data.error ||
          (res.status === 429
            ? 'Too many submissions. Please try again in a few minutes.'
            : 'Something went wrong on our side. Please try again.');
        setStatus(statusEl, 'error', msg);
      } catch (err) {
        console.error('[form] submit failed', err);
        setStatus(
          statusEl,
          'error',
          "We couldn't reach the server. Check your connection and try again."
        );
      } finally {
        setLoading(submitBtn, false);
      }
    });

    // Clear per-field errors as the user edits.
    form.addEventListener('input', (e) => {
      const field = e.target.closest('.field');
      if (field && field.classList.contains('is-invalid')) {
        field.classList.remove('is-invalid');
        const err = field.querySelector('.field__error');
        if (err) err.textContent = err.dataset.default || err.textContent;
      }
    });
  }


  /* ─── Validation ───────────────────────────────────────────────────── */

  function validate(form) {
    let ok = true;

    form.querySelectorAll('input, textarea, select').forEach((el) => {
      if (el.type === 'hidden' || el.hasAttribute('disabled')) return;
      const field = el.closest('.field') || el.closest('.checkbox') || el.closest('.radio');

      // HTML5 validity
      if (el.willValidate && !el.checkValidity()) {
        markInvalid(field, el, customMessage(el));
        ok = false;
        return;
      }

      // Extra custom checks
      if (el.type === 'email' && el.value && !EMAIL_RE.test(el.value.trim())) {
        markInvalid(field, el, 'Please enter a valid email address.');
        ok = false;
      }
      if (el.type === 'url' && el.value) {
        try { new URL(el.value); }
        catch (_) { markInvalid(field, el, 'Please enter a valid URL.'); ok = false; }
      }
    });

    return ok;
  }

  function customMessage(el) {
    if (el.validity.valueMissing) return 'This field is required.';
    if (el.validity.typeMismatch && el.type === 'email') return 'Please enter a valid email address.';
    if (el.validity.typeMismatch && el.type === 'url') return 'Please enter a valid URL.';
    if (el.validity.tooShort) return `At least ${el.minLength} characters, please.`;
    if (el.validity.tooLong) return `Keep it under ${el.maxLength} characters.`;
    if (el.validity.patternMismatch) return 'Please match the requested format.';
    return 'Please correct this field.';
  }

  function markInvalid(container, el, msg) {
    if (!container) return;
    container.classList.add('is-invalid');
    const errEl = container.querySelector('.field__error');
    if (errEl) {
      if (!errEl.dataset.default) errEl.dataset.default = errEl.textContent;
      errEl.textContent = msg;
    }
    el.setAttribute('aria-invalid', 'true');
  }

  function clearFieldErrors(form) {
    form.querySelectorAll('.is-invalid').forEach((n) => n.classList.remove('is-invalid'));
    form.querySelectorAll('[aria-invalid="true"]').forEach((n) => n.removeAttribute('aria-invalid'));
  }

  function renderServerErrors(form, errors) {
    for (const [field, msg] of Object.entries(errors)) {
      const input = form.querySelector(`[name="${field}"]`);
      if (!input) continue;
      const container = input.closest('.field') || input.closest('.checkbox') || input.closest('.radio');
      markInvalid(container, input, msg);
    }
  }

  function focusFirstInvalid(form) {
    const first = form.querySelector('.is-invalid input, .is-invalid textarea, .is-invalid select');
    if (first && typeof first.focus === 'function') first.focus();
  }


  /* ─── Payload ──────────────────────────────────────────────────────── */

  async function buildPayload(form) {
    const hasFile = Array.from(form.querySelectorAll('input[type="file"]'))
      .some((f) => f.files && f.files.length > 0);

    // Optional reCAPTCHA v3 token
    const siteKey = window.__recaptchaSiteKey;
    let recaptchaToken = null;
    if (siteKey && window.grecaptcha && typeof window.grecaptcha.execute === 'function') {
      try {
        recaptchaToken = await window.grecaptcha.execute(siteKey, { action: 'submit' });
      } catch (err) {
        console.warn('[form] recaptcha execute failed', err);
      }
    }

    if (hasFile) {
      const fd = new FormData(form);
      if (recaptchaToken) fd.append('recaptchaToken', recaptchaToken);
      return { body: fd, headers: {} /* browser sets boundary */ };
    }

    // URL-encoded by default — smaller and matches backend expectations.
    const params = new URLSearchParams();
    new FormData(form).forEach((v, k) => params.append(k, typeof v === 'string' ? v : ''));
    if (recaptchaToken) params.append('recaptchaToken', recaptchaToken);

    return {
      body: params.toString(),
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        Accept: 'application/json',
      },
    };
  }


  /* ─── Status banner ────────────────────────────────────────────────── */

  function setStatus(el, kind, message) {
    if (!el) return;
    el.classList.remove('is-success', 'is-error');
    el.classList.add(kind === 'success' ? 'is-success' : 'is-error');
    el.textContent = message;
  }

  function clearStatus(el) {
    if (!el) return;
    el.classList.remove('is-success', 'is-error');
    el.textContent = '';
  }


  /* ─── Submit button loading state ──────────────────────────────────── */

  function setLoading(btn, on) {
    if (!btn) return;
    if (on) {
      btn.classList.add('is-loading');
      btn.setAttribute('disabled', 'disabled');
      btn.setAttribute('aria-busy', 'true');
    } else {
      btn.classList.remove('is-loading');
      btn.removeAttribute('disabled');
      btn.removeAttribute('aria-busy');
    }
  }


  /* ─── Misc ─────────────────────────────────────────────────────────── */

  function scrollIntoView(el) {
    if (!el || typeof el.scrollIntoView !== 'function') return;
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    el.scrollIntoView({ behavior: reduced ? 'auto' : 'smooth', block: 'center' });
  }
})();
