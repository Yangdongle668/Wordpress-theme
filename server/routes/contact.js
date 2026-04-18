/**
 * routes/contact.js
 * ---------------------------------------------------------------------------
 * POST /api/contact — general-purpose contact form submission.
 */

'use strict';

const express = require('express');
const { validate } = require('../utils/validate');
const { send, renderHtml, renderText } = require('../utils/mailer');
const { asyncHandler, logSubmission } = require('../middleware/logger');
const { honeypot, recaptcha } = require('../middleware/security');

const router = express.Router();

const RULES = {
  name:    { required: true, max: 120, label: 'Name' },
  email:   { required: true, email: true, max: 254, label: 'Email' },
  company: { max: 200, label: 'Company' },
  phone:   { max: 40, label: 'Phone' },
  subject: { max: 80, label: 'Topic' },
  message: { required: true, max: 5000, label: 'Message' },
  consent: { required: true, label: 'Consent' },
};

router.post(
  '/',
  honeypot,
  recaptcha,
  asyncHandler(async (req, res) => {
    const { data, errors } = validate(RULES, req.body);
    if (Object.keys(errors).length) {
      return res.status(400).json({ ok: false, errors });
    }

    const fields = {
      Name:    data.name,
      Email:   data.email,
      Company: data.company || '',
      Phone:   data.phone || '',
      Topic:   data.subject || 'contact',
      Message: data.message,
    };

    const subject = `[Contact] ${data.subject || 'New message'} — ${data.name}`;
    const info = await send({
      to: process.env.MAIL_TO_CONTACT || 'contact@example.com',
      replyTo: data.email,
      subject,
      text: renderText(subject, fields),
      html: renderHtml(subject, fields),
    });

    logSubmission('contact', fields, { ip: req.ip, messageId: info.messageId });

    res.json({
      ok: true,
      message: "Thanks — we've received your message and will reply within one business day.",
    });
  })
);

module.exports = router;
