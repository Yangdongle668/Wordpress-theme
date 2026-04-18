/**
 * server.js
 * ---------------------------------------------------------------------------
 * Entry point for the BatteryCo form service.
 *
 * Run:
 *   cp .env.example .env    # fill in SMTP values
 *   npm install
 *   npm start
 *
 * Nginx reverse-proxies /api/* to this process (see deploy/nginx.conf).
 */

'use strict';

require('dotenv').config();

const express = require('express');
const {
  securityHeaders,
  corsPolicy,
  rateLimiter,
} = require('./middleware/security');
const { requestLogger, errorHandler } = require('./middleware/logger');

const contactRoute = require('./routes/contact');
const inquiryRoute = require('./routes/inquiry');
const careersRoute = require('./routes/careers');


const app = express();

// Trust Nginx X-Forwarded-For so req.ip sees the real client IP.
const trust = Number(process.env.TRUST_PROXY);
if (!Number.isNaN(trust)) app.set('trust proxy', trust);

app.disable('x-powered-by');

app.use(securityHeaders());
app.use(corsPolicy());
app.use(requestLogger);
app.use(express.urlencoded({ extended: true, limit: '128kb' }));
app.use(express.json({ limit: '128kb' }));


/* ─── Health check ────────────────────────────────────────────────────── */

app.get('/api/health', (_req, res) => {
  res.json({ ok: true, service: 'form', ts: new Date().toISOString() });
});


/* ─── Rate-limited form routes ────────────────────────────────────────── */

const limiter = rateLimiter();
app.use('/api/contact', limiter, contactRoute);
app.use('/api/inquiry', limiter, inquiryRoute);
app.use('/api/careers', limiter, careersRoute);


/* ─── 404 + error ─────────────────────────────────────────────────────── */

app.use((req, res) => {
  res.status(404).json({ ok: false, error: `Not found: ${req.method} ${req.originalUrl}` });
});

app.use(errorHandler);


/* ─── Boot ────────────────────────────────────────────────────────────── */

const PORT = Number(process.env.PORT) || 3000;
const HOST = process.env.HOST || '127.0.0.1';

const server = app.listen(PORT, HOST, () => {
  console.log(
    `[boot] ${new Date().toISOString()}  form service listening on http://${HOST}:${PORT}`
  );
});


/* ─── Graceful shutdown ───────────────────────────────────────────────── */

function shutdown(signal) {
  console.log(`[shutdown] received ${signal}, closing server…`);
  server.close(() => {
    console.log('[shutdown] closed');
    process.exit(0);
  });
  // Force exit after 10s if something hangs
  setTimeout(() => process.exit(1), 10_000).unref();
}

process.on('SIGINT', () => shutdown('SIGINT'));
process.on('SIGTERM', () => shutdown('SIGTERM'));

process.on('unhandledRejection', (reason) => {
  console.error('[unhandledRejection]', reason);
});
process.on('uncaughtException', (err) => {
  console.error('[uncaughtException]', err);
});


module.exports = app;
