/**
 * utils/validate.js
 * ---------------------------------------------------------------------------
 * Small, dependency-free form validators. Every route uses `validate(rules,
 * body)` which returns { data, errors }. If errors is non-empty, reject with
 * 400; otherwise forward `data` into the email builder.
 */

'use strict';

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


function required(val, label) {
  if (val === undefined || val === null || String(val).trim() === '') {
    return `${label} is required.`;
  }
}

function maxLen(val, n, label) {
  if (val && String(val).length > n) {
    return `${label} must be ${n} characters or fewer.`;
  }
}

function email(val, label) {
  if (val && !EMAIL_RE.test(String(val).trim())) {
    return `${label} must be a valid email address.`;
  }
}


/**
 * Run a rules object against a body.
 *
 * rules = {
 *   name:    { required: true, max: 120, label: 'Name' },
 *   email:   { required: true, email: true, max: 254, label: 'Email' },
 *   message: { required: true, max: 5000, label: 'Message' },
 * }
 *
 * Returns { data: { ...trimmed }, errors: { field: 'message', ... } }
 */
function validate(rules, body = {}) {
  const data = {};
  const errors = {};

  for (const [field, rule] of Object.entries(rules)) {
    const raw = body[field];
    const value = typeof raw === 'string' ? raw.trim() : raw;
    const label = rule.label || field;

    if (rule.required) {
      const err = required(value, label);
      if (err) { errors[field] = err; continue; }
    }
    if (rule.max) {
      const err = maxLen(value, rule.max, label);
      if (err) { errors[field] = err; continue; }
    }
    if (rule.email) {
      const err = email(value, label);
      if (err) { errors[field] = err; continue; }
    }

    if (value !== undefined && value !== '') data[field] = value;
  }

  return { data, errors };
}


module.exports = { validate, EMAIL_RE };
