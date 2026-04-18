/**
 * middleware/logger.js
 * ---------------------------------------------------------------------------
 * Minimal request + submission logging. Writes:
 *   - request lines to stdout (captured by PM2 / systemd)
 *   - successful submissions to logs/submissions.jsonl for later analysis
 *
 * Does not depend on any logging framework — stays portable.
 */

'use strict';

const fs = require('fs');
const path = require('path');

const LOG_DIR = process.env.LOG_DIR || './logs';
const SUBMISSIONS_FILE = path.join(LOG_DIR, 'submissions.jsonl');


function ensureDir() {
  try { fs.mkdirSync(LOG_DIR, { recursive: true }); } catch (_) { /* ignore */ }
}
ensureDir();


/* ─── Per-request console line ────────────────────────────────────────── */

function requestLogger(req, res, next) {
  const start = Date.now();
  res.on('finish', () => {
    const ms = Date.now() - start;
    const ip = req.ip || req.connection?.remoteAddress || '-';
    console.log(
      `[${new Date().toISOString()}] ${req.method} ${req.originalUrl} ${res.statusCode} ${ms}ms ${ip}`
    );
  });

  // Expose a tiny logger to downstream middleware.
  req.log = {
    info: (...args) => console.log(`[${new Date().toISOString()}]`, ...args),
    warn: (...args) => console.warn(`[${new Date().toISOString()}]`, ...args),
    error: (...args) => console.error(`[${new Date().toISOString()}]`, ...args),
  };

  next();
}


/* ─── Submission persistence (JSON lines) ─────────────────────────────── */

function logSubmission(kind, payload, meta = {}) {
  if (process.env.LOG_SUBMISSIONS === 'false') return;

  const entry = {
    ts: new Date().toISOString(),
    kind,
    ...meta,
    payload,
  };
  try {
    fs.appendFileSync(SUBMISSIONS_FILE, JSON.stringify(entry) + '\n', 'utf8');
  } catch (err) {
    console.error('[logger] failed to write submission', err);
  }
}


/* ─── Async error wrapper ─────────────────────────────────────────────── */

function asyncHandler(fn) {
  return (req, res, next) => Promise.resolve(fn(req, res, next)).catch(next);
}


/* ─── Central error handler ───────────────────────────────────────────── */

function errorHandler(err, req, res, _next) {
  console.error(`[error] ${req.method} ${req.originalUrl}:`, err.stack || err.message);

  if (res.headersSent) return;

  // Multer size/type errors have a .code — surface their message to the client.
  if (err.code === 'LIMIT_FILE_SIZE') {
    return res.status(413).json({ ok: false, error: 'File is too large.' });
  }
  if (err.code === 'LIMIT_UNEXPECTED_FILE') {
    return res.status(400).json({ ok: false, error: 'Unexpected file field.' });
  }

  if (err.message && /CORS/.test(err.message)) {
    return res.status(403).json({ ok: false, error: 'Origin not allowed.' });
  }

  res.status(err.status || 500).json({
    ok: false,
    error: process.env.NODE_ENV === 'production'
      ? 'Something went wrong. Please try again later.'
      : (err.message || 'Internal error'),
  });
}


module.exports = { requestLogger, logSubmission, asyncHandler, errorHandler };
