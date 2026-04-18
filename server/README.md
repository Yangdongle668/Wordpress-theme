# BatteryCo form service

Lightweight Node.js service that receives contact, product-inquiry, and
careers submissions from the marketing site and forwards them via SMTP.

- Three routes, one binary, zero frontend dependency
- Rate-limited, honeypot-guarded, optional reCAPTCHA v3
- Resume uploads (PDF / DOC / DOCX) stored on disk + attached to emails
- Each submission is also appended to `logs/submissions.jsonl` for
  post-hoc analysis

## Quick start

```bash
cd server
cp .env.example .env          # fill in SMTP values
npm install
npm run dev                   # http://127.0.0.1:3000
```

## Endpoints

| Method | Path           | Purpose                         |
|--------|----------------|---------------------------------|
| GET    | /api/health    | health check (JSON)             |
| POST   | /api/contact   | general contact form            |
| POST   | /api/inquiry   | product quote / sample request  |
| POST   | /api/careers   | job application (multipart)     |

All POST endpoints accept `application/x-www-form-urlencoded` or
`application/json` bodies. Careers additionally accepts `multipart/form-data`
with a `resume` file field.

Every route returns JSON:

```json
{ "ok": true,  "message": "…"                       }
{ "ok": false, "errors":  { "email": "…" }          }
{ "ok": false, "error":   "Too many submissions…"   }
```

## Security layers

- **helmet** — default security headers
- **CORS** — restricted to `CORS_ORIGINS` (empty = same-origin only)
- **express-rate-limit** — 12 requests per IP per 15 min (configurable)
- **Honeypot** — the `company_website` field must stay empty; if a bot fills
  it, the server returns `{ok:true}` but never processes or emails anything
- **reCAPTCHA v3** — optional; set `RECAPTCHA_SECRET` and client must POST a
  `recaptchaToken` field. Server verifies with Google and rejects below
  `RECAPTCHA_MIN_SCORE`
- **Input validation** — every field is size-capped; `email` is format-
  checked; required fields enforced

## Deployment

See `deploy/nginx.conf` for the Nginx reverse-proxy stanza (proxies
`/api/*` → `127.0.0.1:3000`) and `deploy/ecosystem.config.js` for the PM2
process definition.

```bash
# on the server
cd /var/www/batteryco/server
cp .env.example .env
vi .env                         # SMTP creds + CORS_ORIGINS + MAIL_TO_* recipients
npm ci --omit=dev
pm2 start ecosystem.config.js --env production
pm2 save
```

## Logs

- Request lines → stdout (PM2 captures them)
- Submissions   → `logs/submissions.jsonl` (one JSON object per line)
- Uploaded resumes preserved under `uploads/` with `<timestamp>-<rand>-<safe-name>`

## Nginx snippet

```nginx
location /api/ {
  proxy_pass http://127.0.0.1:3000;
  proxy_http_version 1.1;
  proxy_set_header Host $host;
  proxy_set_header X-Real-IP $remote_addr;
  proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
  proxy_set_header X-Forwarded-Proto $scheme;
  client_max_body_size 12m;
}
```
