# Docker deployment — www.07691688.xyz

One-file deployment of the whole stack via `docker compose`. Three
containers, one HTTPS endpoint, auto-renewing certificates, zero manual
Nginx or Node install on the host.

## Stack

| Container | Image              | Purpose                                              |
|-----------|--------------------|------------------------------------------------------|
| `nginx`   | `nginx:1.27-alpine`| TLS termination, static site, reverse proxy to form  |
| `form`    | built from `server/Dockerfile` (Node 20 Alpine) | Express service handling /api/* |
| `certbot` | `certbot/certbot`  | Let's Encrypt auto-renewal every 12 hours            |

All three share two networks (`batteryco_frontend`, `batteryco_backend`)
and two named volumes for certificate state
(`batteryco_certbot_etc`, `batteryco_certbot_www`).

---

## Prerequisites

On the production VM:

- Docker Engine 24+ with compose v2 plugin (`docker compose version` works)
- Ports **80** and **443** open inbound on the firewall
- DNS A (and AAAA if IPv6) records pointing both `07691688.xyz` and
  `www.07691688.xyz` at the server's public IP. Verify with:
  ```bash
  dig +short 07691688.xyz
  dig +short www.07691688.xyz
  ```
- At least 1 GB free disk for image layers + certs

---

## First-time deploy (5 steps)

```bash
# 1.  Clone the repo onto the VM
git clone https://github.com/yangdongle668/wordpress-theme.git /srv/batteryco
cd /srv/batteryco
git checkout claude/battery-company-website-f3F3m

# 2.  Fill in SMTP credentials for the form service
cp server/.env.example server/.env
vi  server/.env                # set SMTP_HOST/USER/PASS/MAIL_TO_*

# 3.  Set docker-compose top-level env (domain + LE email)
cp .env.docker.example .env
vi  .env                       # verify DOMAIN=07691688.xyz, set LE email
                               # keep STAGING=1 for the first run to avoid
                               # Let's Encrypt's production rate limits

# 4.  Build images and bootstrap TLS
docker compose build
bash deploy/init-letsencrypt.sh

# 5.  Bring everything up
docker compose up -d
docker compose ps              # should show form + nginx + certbot "Up"
```

Smoke test:

```bash
curl -I https://www.07691688.xyz/
# HTTP/2 200  (plus HSTS, CSP, etc.)

curl -s  https://www.07691688.xyz/api/health
# {"ok":true,"service":"form",...}
```

Once the staging cert is working, flip `STAGING=0` in `.env` and re-run
`bash deploy/init-letsencrypt.sh` to get a browser-trusted cert.

---

## Daily operations

### Deploy a new version

```bash
cd /srv/batteryco
git pull
docker compose up -d --build       # rebuild only the form image; nginx
                                   # reloads its mounted config and HTML
                                   # on the 6-hour cadence baked in
docker compose exec nginx nginx -s reload   # instant reload after HTML/CSS changes
```

### View logs

```bash
docker compose logs -f nginx
docker compose logs -f form
docker compose logs -f certbot
```

### Health

```bash
docker compose ps
docker compose exec form curl -fsS http://127.0.0.1:3000/api/health
```

### Stop / start the whole stack

```bash
docker compose stop
docker compose start
# or, full teardown (keeps volumes, so certs + uploads survive):
docker compose down
```

---

## Certificates

- Located in the named volume `batteryco_certbot_etc`, mirrored to
  `/etc/letsencrypt/` inside nginx and certbot.
- Auto-renewal runs every 12 hours (no-op until T-30 days).
- Nginx reloads its config every 6 hours to pick up renewed certs with
  zero downtime.
- Force a manual renew:
  ```bash
  docker compose run --rm certbot renew --force-renewal --webroot -w /var/www/certbot
  docker compose exec nginx nginx -s reload
  ```

---

## Data that survives container rebuilds

- `server/logs/submissions.jsonl` — bind-mounted to the host, your audit log
- `server/uploads/*.pdf|doc|docx` — bind-mounted résumé uploads
- `batteryco_certbot_etc` volume — TLS keys + certs
- `batteryco_certbot_www` volume — ACME challenge webroot

Back up `server/logs/`, `server/uploads/`, and the contents of
`batteryco_certbot_etc` (via `docker run --rm -v batteryco_certbot_etc:/e
alpine tar c -C /e .`) on whatever cadence your audit requirements need.

---

## Troubleshooting

**`docker compose build` fails during `npm ci`**
→ the server directory is missing `package-lock.json`. Either commit one
(`cd server && npm install && git add package-lock.json`) or the
Dockerfile falls back to `npm install`. Both work; lockfile is just faster
and more reproducible.

**Cert challenge fails with "Timeout during connect"**
→ DNS not yet pointing at this host, or port 80 is blocked. Fix DNS
first, then re-run `bash deploy/init-letsencrypt.sh`.

**"Too many failed authorizations" from Let's Encrypt**
→ You're rate-limited on the production endpoint. Set `STAGING=1` in
`.env`, re-run the init script, verify, then switch back to `STAGING=0`
and re-run once (`--force-renewal` is already in the init script).

**Forms return 502 Bad Gateway**
→ `docker compose ps` — is `form` healthy? `docker compose logs form`
for stack traces. Usually a missing SMTP env var.

**I need to update HTML/CSS without rebuilding**
→ Edit files under `public/` on the host, then
`docker compose exec nginx nginx -s reload`. No rebuild needed; public
is mounted read-only into the nginx container.

---

## Uninstall

```bash
docker compose down -v   # -v removes certs and challenge volumes too
docker image rm batteryco-form:latest nginx:1.27-alpine certbot/certbot:latest
```
