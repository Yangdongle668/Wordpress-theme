# Deployment runbook — BatteryCo marketing site

Single-box deployment on a Linux VM with Nginx + Node.js + PM2.
Static assets live under `/var/www/batteryco/public`, the Node form
service runs as a PM2 app on `127.0.0.1:3000`, and Nginx both serves
the static files and reverse-proxies `/api/*`.

Everything in this runbook is idempotent — re-running any step is safe.

---

## 1. One-time server prep

Tested on Ubuntu 22.04 / Debian 12. Adjust package names for other distros.

```bash
# 1.1  System packages
sudo apt update
sudo apt install -y nginx git curl ca-certificates ufw

# 1.2  Node.js 20 LTS via NodeSource
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# 1.3  PM2 (global) + pm2-logrotate so logs don't eat the disk
sudo npm i -g pm2
pm2 install pm2-logrotate
pm2 set pm2-logrotate:max_size 50M
pm2 set pm2-logrotate:retain 14

# 1.4  UFW firewall — allow only SSH + HTTP + HTTPS
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw --force enable

# 1.5  Dedicated deploy user (if not already present)
sudo adduser --disabled-password --gecos '' deploy
sudo mkdir -p /var/www/batteryco /var/log/batteryco
sudo chown -R deploy:deploy /var/www/batteryco /var/log/batteryco
```

---

## 2. Let's Encrypt / certbot

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot certonly --webroot -w /var/www/letsencrypt \
  -d example.com -d www.example.com \
  --email ops@example.com --agree-tos --no-eff-email

# certbot auto-renewal timer is installed by default; verify with:
systemctl list-timers | grep certbot
```

---

## 3. Deploy the code

```bash
sudo -u deploy bash <<'EOF'
set -euo pipefail
cd /var/www/batteryco

# First deploy
if [ ! -d .git ]; then
  git clone https://github.com/yangdongle668/wordpress-theme.git .
else
  git fetch --prune
  git checkout main
  git pull --ff-only
fi

# Install Node deps for the form service only (public/ is pure static)
cd server
cp -n .env.example .env   # don't overwrite a real .env
npm ci --omit=dev
EOF
```

Edit `/var/www/batteryco/server/.env` and fill in:

- `SMTP_HOST` / `SMTP_USER` / `SMTP_PASS`
- `MAIL_FROM` and the `MAIL_TO_*` recipient addresses
- `CORS_ORIGINS=https://www.example.com`
- `TRUST_PROXY=1`

---

## 4. Start / reload the Node service

```bash
sudo -u deploy pm2 start /var/www/batteryco/deploy/ecosystem.config.js --env production
sudo -u deploy pm2 save

# Enable pm2 on boot (run once per VM; writes a systemd unit)
sudo env PATH=$PATH:/usr/bin pm2 startup systemd -u deploy --hp /home/deploy
```

Subsequent deploys:

```bash
sudo -u deploy bash <<'EOF'
set -euo pipefail
cd /var/www/batteryco
git fetch --prune && git checkout main && git pull --ff-only
cd server && npm ci --omit=dev
pm2 reload /var/www/batteryco/deploy/ecosystem.config.js --env production
EOF
```

This reload is zero-downtime: the two clustered workers restart one at
a time, and Nginx's upstream keepalive pool keeps proxying to whichever
worker is healthy.

---

## 5. Wire up Nginx

```bash
sudo cp /var/www/batteryco/deploy/nginx.conf /etc/nginx/sites-available/batteryco.conf
sudo ln -sf /etc/nginx/sites-available/batteryco.conf \
           /etc/nginx/sites-enabled/batteryco.conf
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

If `nginx -t` fails on paths (cert files, webroot), fix those first —
don't skip.

---

## 6. Smoke tests

```bash
# From the VM
curl -I https://www.example.com/en/               # 200 + HSTS + CSP headers
curl -I https://www.example.com/sitemap.xml       # 200, Content-Type: application/xml
curl -s  https://www.example.com/api/health       # {"ok":true,"service":"form",...}

# Reverse-proxy check (posts a tiny JSON so the rate limiter doesn't flare)
curl -sS -X POST https://www.example.com/api/contact \
  -H 'Content-Type: application/json' \
  --data '{"name":"test","email":"t@example.com","message":"hello","consent":"on"}'
```

On external reachability: check the cert with `openssl s_client -connect
www.example.com:443 -servername www.example.com </dev/null | openssl
x509 -noout -dates` and hit https://www.ssllabs.com/ssltest/analyze.html
for a full TLS audit (target grade: A+).

---

## 7. Logs

| Stream                  | Path                                                   |
|-------------------------|--------------------------------------------------------|
| Nginx access            | `/var/log/nginx/batteryco.access.log`                  |
| Nginx error             | `/var/log/nginx/batteryco.error.log`                   |
| Node form service stdout| `/var/log/batteryco/form-out.log`                      |
| Node form service stderr| `/var/log/batteryco/form-err.log`                      |
| Form submissions (JSON) | `/var/www/batteryco/server/logs/submissions.jsonl`     |
| Uploaded résumés        | `/var/www/batteryco/server/uploads/`                   |

Rotate Nginx logs via `/etc/logrotate.d/nginx` (default is daily/14
days); PM2 logs rotate via the `pm2-logrotate` module installed in §1.

---

## 8. Rollback

```bash
sudo -u deploy bash <<'EOF'
cd /var/www/batteryco
git log --oneline -10                        # pick the last good commit
git checkout <commit>                        # detach HEAD at that SHA
cd server && npm ci --omit=dev
pm2 reload /var/www/batteryco/deploy/ecosystem.config.js --env production
EOF
```

No database, so rollback is just git + `pm2 reload`.

---

## 9. Backups

What matters on this VM:

- `server/.env` — SMTP credentials (back up out-of-band)
- `server/logs/submissions.jsonl` — business correspondence audit trail
- `server/uploads/` — résumé PDFs

Suggested: nightly `tar` of `server/logs/` + `server/uploads/` → S3 or
equivalent, encrypted with age or openssl. Retain 90 days.

---

## 10. Monitoring

Minimum viable monitoring for a marketing site:

- **Uptime**: UptimeRobot / Better Uptime / Pingdom on
  `https://www.example.com/en/` and `https://www.example.com/api/health`,
  1-minute interval
- **Cert expiry**: certbot renewal timer + an alert at T-14 days
  (`journalctl -u certbot.timer` sanity-check monthly)
- **Disk**: alert when `/` above 85% or `/var/log` above 1 GB
- **PM2**: `pm2 monit` during incident response; long-term hook via
  `pm2 install pm2-server-monit` or a Datadog / Prometheus exporter

Submissions themselves show up as emails at `MAIL_TO_CONTACT` etc. — the
inboxes are the lowest-fidelity monitoring and usually the fastest way
to tell whether the end-to-end path still works.
