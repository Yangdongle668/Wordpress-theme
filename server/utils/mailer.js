/**
 * utils/mailer.js
 * ---------------------------------------------------------------------------
 * Nodemailer wrapper with lazy-initialized SMTP transport.
 * Export a single `send()` function used by all routes.
 *
 * If SMTP credentials aren't configured, send() becomes a logging no-op so
 * local development still works without a real mail server.
 */

'use strict';

const nodemailer = require('nodemailer');

let transporter = null;
let initialized = false;


function init() {
  if (initialized) return transporter;
  initialized = true;

  const { SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_SECURE } = process.env;

  if (!SMTP_HOST || !SMTP_USER || !SMTP_PASS) {
    console.warn('[mailer] SMTP not configured — send() will log payloads only.');
    return null;
  }

  transporter = nodemailer.createTransport({
    host: SMTP_HOST,
    port: Number(SMTP_PORT) || 587,
    secure: SMTP_SECURE === 'true',
    auth: { user: SMTP_USER, pass: SMTP_PASS },
    pool: true,
    maxConnections: 2,
    maxMessages: 50,
  });

  // Best-effort connection check (doesn't block boot).
  transporter.verify((err) => {
    if (err) console.warn('[mailer] verify failed:', err.message);
    else console.log('[mailer] SMTP transport ready');
  });

  return transporter;
}


/**
 * Send an email. Falls back to logging if SMTP isn't configured.
 *
 * @param {object} opts
 * @param {string|string[]} opts.to    recipient(s)
 * @param {string} opts.subject        email subject
 * @param {string} [opts.text]         plaintext body
 * @param {string} [opts.html]         html body
 * @param {string} [opts.replyTo]      override reply-to
 * @param {Array}  [opts.attachments]  Nodemailer attachments
 */
async function send(opts) {
  const t = init();
  const message = {
    from: process.env.MAIL_FROM || 'no-reply@example.com',
    replyTo: opts.replyTo || process.env.MAIL_REPLY_TO,
    to: opts.to,
    subject: opts.subject,
    text: opts.text,
    html: opts.html,
    attachments: opts.attachments,
  };

  if (!t) {
    console.log('[mailer:dry-run]', {
      to: message.to,
      subject: message.subject,
      textPreview: (message.text || '').slice(0, 200),
    });
    return { accepted: [].concat(message.to), messageId: 'dry-run' };
  }

  const info = await t.sendMail(message);
  return info;
}


/* ─── HTML body builder (shared across routes) ────────────────────────── */

function renderHtml(title, fields) {
  const rows = Object.entries(fields)
    .map(
      ([k, v]) => `
        <tr>
          <td style="padding:6px 12px 6px 0;color:#5C5E62;font-size:13px;letter-spacing:.06em;text-transform:uppercase;vertical-align:top;width:160px;">${escapeHtml(k)}</td>
          <td style="padding:6px 0;color:#171A20;font-size:14px;line-height:1.55;white-space:pre-wrap;">${escapeHtml(v || '')}</td>
        </tr>`
    )
    .join('');

  return `<!doctype html>
<html><body style="margin:0;padding:24px;background:#FAFAFA;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
  <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:640px;margin:0 auto;background:#fff;border:1px solid #E4E6E8;border-radius:12px;overflow:hidden;">
    <tr><td style="padding:24px 28px 4px;">
      <h2 style="margin:0;color:#171A20;font-size:20px;font-weight:600;letter-spacing:-.01em;">${escapeHtml(title)}</h2>
    </td></tr>
    <tr><td style="padding:8px 28px 24px;">
      <table role="presentation" cellpadding="0" cellspacing="0" width="100%">${rows}</table>
    </td></tr>
    <tr><td style="padding:16px 28px;background:#F4F4F4;color:#5C5E62;font-size:12px;">
      Sent automatically by the BatteryCo website form service.
    </td></tr>
  </table>
</body></html>`;
}

function renderText(title, fields) {
  const lines = [title, '─'.repeat(title.length)];
  for (const [k, v] of Object.entries(fields)) {
    lines.push(`${k}: ${v || '—'}`);
  }
  return lines.join('\n');
}

function escapeHtml(s) {
  return String(s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}


module.exports = { send, renderHtml, renderText };
