/**
 * middleware/security.js
 * ---------------------------------------------------------------------------
 * Baseline protections for the public form endpoints:
 *   - helmet:          sensible security headers
 *   - cors:            allow only configured origins
 *   - rate-limit:      throttle submissions per IP
 *   - honeypot:        reject bots that fill the hidden "company_website" field
 *   - reCAPTCHA v3:    optional server-side score verification
 */

'use strict';

const helmet = require('helmet');
const cors = require('cors');
const rateLimit = require('express-rate-limit');

const HONEYPOT_FIELD = 'company_website';


/* ─── helmet with CSP loose enough for email HTML ─────────────────────── */

function securityHeaders() {
  return helmet({
    // Form service returns JSON only; no CSP needed here. Keep defaults
    // except crossOriginResourcePolicy which trips on simple POSTs.
    crossOriginResourcePolicy: { policy: 'cross-origin' },
    contentSecurityPolicy: false,
  });
}


/* ─── CORS ─────────────────────────────────────────────────────────────── */

function corsPolicy() {
  const list = (process.env.CORS_ORIGINS || '')
    .split(',')
    .map((s) => s.trim())
    .filter(Boolean);

  if (list.length === 0) {
    // Same-origin only: reflect the request origin for ease of dev.
    return cors({ origin: true, credentials: false, maxAge: 600 });
  }

  return cors({
    origin(origin, cb) {
      // Non-browser (no origin) requests — let them through; rate limiter
      // still protects us.
      if (!origin) return cb(null, true);
      if (list.includes(origin)) return cb(null, true);
      return cb(new Error('Not allowed by CORS'));
    },
    credentials: false,
    maxAge: 600,
  });
}


/* ─── Rate limiter (per IP) ───────────────────────────────────────────── */

function rateLimiter() {
  const windowMs = Number(process.env.RATE_LIMIT_WINDOW_MS) || 15 * 60 * 1000;
  const max = Number(process.env.RATE_LIMIT_MAX) || 12;

  return rateLimit({
    windowMs,
    max,
    standardHeaders: 'draft-7',
    legacyHeaders: false,
    skipSuccessfulRequests: false,
    message: {
      ok: false,
      error: 'Too many submissions from this IP. Please try again later.',
    },
  });
}


/* ─── Honeypot ────────────────────────────────────────────────────────── */

function honeypot(req, res, next) {
  const trap = (req.body && req.body[HONEYPOT_FIELD]) || '';
  if (String(trap).trim() !== '') {
    // Pretend success so the bot doesn't realize it was caught.
    return res.json({ ok: true });
  }
  if (req.body) delete req.body[HONEYPOT_FIELD];
  next();
}


/* ─── Optional reCAPTCHA v3 verification ──────────────────────────────── */

async function recaptcha(req, res, next) {
  const secret = process.env.RECAPTCHA_SECRET;
  if (!secret) return next();

  const token = (req.body && req.body.recaptchaToken) || '';
  if (!token) {
    return res.status(400).json({ ok: false, error: 'Missing verification token.' });
  }

  try {
    const params = new URLSearchParams({ secret, response: token });
    if (req.ip) params.set('remoteip', req.ip);

    const resp = await fetch('https://www.google.com/recaptcha/api/siteverify', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: params.toString(),
    });
    const data = await resp.json();

    const minScore = Number(process.env.RECAPTCHA_MIN_SCORE) || 0.5;
    if (!data.success || (typeof data.score === 'number' && data.score < minScore)) {
      return res.status(400).json({
        ok: false,
        error: 'Verification failed. Please try again.',
      });
    }

    if (req.body) delete req.body.recaptchaToken;
    return next();
  } catch (err) {
    req.log?.warn('[recaptcha] verification error', err);
    return res.status(502).json({ ok: false, error: 'Verification service unavailable.' });
  }
}


module.exports = {
  securityHeaders,
  corsPolicy,
  rateLimiter,
  honeypot,
  recaptcha,
  HONEYPOT_FIELD,
};
