/**
 * routes/inquiry.js
 * ---------------------------------------------------------------------------
 * POST /api/inquiry — product quote / sample request. Accepts a `product`
 * field so the backend knows which SKU the customer is asking about.
 */

'use strict';

const express = require('express');
const { validate } = require('../utils/validate');
const { send, renderHtml, renderText } = require('../utils/mailer');
const { asyncHandler, logSubmission } = require('../middleware/logger');
const { honeypot, recaptcha } = require('../middleware/security');

const router = express.Router();

const RULES = {
  name:        { required: true, max: 120, label: 'Name' },
  email:       { required: true, email: true, max: 254, label: 'Email' },
  company:     { max: 200, label: 'Company' },
  phone:       { max: 40, label: 'Phone' },
  product:     { max: 120, label: 'Product' },
  quantity:    { max: 80, label: 'Quantity' },
  application: { max: 200, label: 'Application' },
  message:     { max: 5000, label: 'Project details' },
  consent:     { required: true, label: 'Consent' },
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
      Product:     data.product || 'Unspecified',
      Name:        data.name,
      Email:       data.email,
      Company:     data.company || '',
      Phone:       data.phone || '',
      Quantity:    data.quantity || '',
      Application: data.application || '',
      Details:     data.message || '',
    };

    const subject = `[Inquiry] ${data.product || 'Product'} — ${data.name}`;
    const info = await send({
      to: process.env.MAIL_TO_INQUIRY || 'sales@example.com',
      replyTo: data.email,
      subject,
      text: renderText(subject, fields),
      html: renderHtml(subject, fields),
    });

    logSubmission('inquiry', fields, { ip: req.ip, messageId: info.messageId });

    res.json({
      ok: true,
      message: 'Inquiry received. Sales will come back within one business day.',
    });
  })
);

module.exports = router;
