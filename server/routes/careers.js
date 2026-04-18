/**
 * routes/careers.js
 * ---------------------------------------------------------------------------
 * POST /api/careers — job application with optional resume upload.
 * Uses multer disk storage restricted to PDF/DOC/DOCX and a size cap from
 * MAX_UPLOAD_BYTES. Uploaded resumes are attached to the notification email
 * and preserved on disk under UPLOAD_DIR.
 */

'use strict';

const express = require('express');
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const crypto = require('crypto');

const { validate } = require('../utils/validate');
const { send, renderHtml, renderText } = require('../utils/mailer');
const { asyncHandler, logSubmission } = require('../middleware/logger');
const { honeypot, recaptcha } = require('../middleware/security');

const router = express.Router();


/* ─── Multer configuration ────────────────────────────────────────────── */

const UPLOAD_DIR = process.env.UPLOAD_DIR || './uploads';
fs.mkdirSync(UPLOAD_DIR, { recursive: true });

const ALLOWED_MIME = new Set([
  'application/pdf',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
]);

const storage = multer.diskStorage({
  destination: UPLOAD_DIR,
  filename(req, file, cb) {
    const safe = file.originalname.replace(/[^a-zA-Z0-9._-]/g, '_').slice(0, 80);
    const stamp = Date.now();
    const rand = crypto.randomBytes(4).toString('hex');
    cb(null, `${stamp}-${rand}-${safe}`);
  },
});

const upload = multer({
  storage,
  limits: { fileSize: Number(process.env.MAX_UPLOAD_BYTES) || 10 * 1024 * 1024 },
  fileFilter(req, file, cb) {
    if (!ALLOWED_MIME.has(file.mimetype)) {
      return cb(new Error('Only PDF, DOC, or DOCX resumes are accepted.'));
    }
    cb(null, true);
  },
});


/* ─── Validation rules ────────────────────────────────────────────────── */

const RULES = {
  name:     { required: true, max: 120, label: 'Name' },
  email:    { required: true, email: true, max: 254, label: 'Email' },
  phone:    { max: 40, label: 'Phone' },
  position: { required: true, max: 160, label: 'Position' },
  location: { max: 120, label: 'Location' },
  linkedin: { max: 240, label: 'LinkedIn' },
  message:  { max: 5000, label: 'Cover letter' },
  consent:  { required: true, label: 'Consent' },
};


/* ─── Route ───────────────────────────────────────────────────────────── */

router.post(
  '/',
  upload.single('resume'),
  honeypot,
  recaptcha,
  asyncHandler(async (req, res) => {
    const { data, errors } = validate(RULES, req.body);
    if (Object.keys(errors).length) {
      // If validation fails, clean up the uploaded file so we don't leak
      // storage on abandoned attempts.
      if (req.file) fs.unlink(req.file.path, () => {});
      return res.status(400).json({ ok: false, errors });
    }

    const fields = {
      Position:   data.position,
      Name:       data.name,
      Email:      data.email,
      Phone:      data.phone || '',
      Location:   data.location || '',
      LinkedIn:   data.linkedin || '',
      Message:    data.message || '',
      Resume:     req.file ? req.file.originalname : '(none attached)',
    };

    const subject = `[Careers] ${data.position} — ${data.name}`;
    const attachments = req.file
      ? [{ filename: req.file.originalname, path: req.file.path }]
      : [];

    const info = await send({
      to: process.env.MAIL_TO_CAREERS || 'careers@example.com',
      replyTo: data.email,
      subject,
      text: renderText(subject, fields),
      html: renderHtml(subject, fields),
      attachments,
    });

    logSubmission('careers', fields, {
      ip: req.ip,
      messageId: info.messageId,
      resumeFile: req.file ? req.file.filename : null,
    });

    res.json({
      ok: true,
      message: 'Application received. Our HR team will review and be in touch.',
    });
  })
);

module.exports = router;
