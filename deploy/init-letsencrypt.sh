#!/usr/bin/env bash
# =============================================================================
# deploy/init-letsencrypt.sh
# -----------------------------------------------------------------------------
# One-time bootstrap for Let's Encrypt TLS certificates under docker-compose.
#
# Problem: nginx can't start without certs, but certbot can't obtain certs
# without a running HTTP server for the ACME challenge.
# Solution: drop in a self-signed dummy cert, start nginx, issue the real
# cert via HTTP-01 challenge, reload nginx.
#
# Usage:
#   cp .env.docker.example .env
#   vi .env                             # set DOMAIN, LETSENCRYPT_EMAIL
#   bash deploy/init-letsencrypt.sh
#
# Re-running is safe — it prompts before replacing an existing certificate.
# Based on the well-known nginx-certbot pattern by @wmnnd.
# =============================================================================

set -euo pipefail

# ─── Load config from .env (next to docker-compose.yml) ────────────────────
if [[ ! -f .env ]]; then
  echo "! .env not found. Copy .env.docker.example → .env and fill it in."
  exit 1
fi
# shellcheck disable=SC1091
source .env

DOMAIN="${DOMAIN:?DOMAIN must be set in .env}"
EMAIL="${LETSENCRYPT_EMAIL:?LETSENCRYPT_EMAIL must be set in .env}"
STAGING="${STAGING:-0}"

# Both apex and www — matches what nginx-docker.conf serves
DOMAINS=("$DOMAIN" "www.$DOMAIN")
RSA_KEY_SIZE=4096

# ─── Pick docker compose command (v1 vs v2) ────────────────────────────────
if docker compose version >/dev/null 2>&1; then
  DC="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
  DC="docker-compose"
else
  echo "! Neither 'docker compose' nor 'docker-compose' is available."
  exit 1
fi

# ─── Confirm overwrite if a cert already exists ────────────────────────────
CERT_PATH="/etc/letsencrypt/live/$DOMAIN"
if $DC run --rm --entrypoint "test -d $CERT_PATH" certbot >/dev/null 2>&1; then
  read -r -p "Existing certs for $DOMAIN found. Replace? (y/N) " reply
  if [[ ! "$reply" =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 1
  fi
fi

# ─── 1. Seed recommended TLS options files ─────────────────────────────────
echo "### 1/5  Seeding recommended Nginx TLS options…"
$DC run --rm --entrypoint "\
  sh -c '\
    mkdir -p /etc/letsencrypt && \
    [ -f /etc/letsencrypt/options-ssl-nginx.conf ] || \
      wget -q -O /etc/letsencrypt/options-ssl-nginx.conf \
        https://raw.githubusercontent.com/certbot/certbot/main/certbot-nginx/certbot_nginx/_internal/tls_configs/options-ssl-nginx.conf; \
    [ -f /etc/letsencrypt/ssl-dhparams.pem ] || \
      openssl dhparam -out /etc/letsencrypt/ssl-dhparams.pem 2048'" \
  certbot


# ─── 2. Drop a dummy self-signed cert so nginx can boot ────────────────────
echo "### 2/5  Creating dummy certificate for $DOMAIN…"
DUMMY_PATH="/etc/letsencrypt/live/$DOMAIN"
$DC run --rm --entrypoint "\
  sh -c '\
    mkdir -p $DUMMY_PATH && \
    openssl req -x509 -nodes -newkey rsa:$RSA_KEY_SIZE -days 1 \
      -keyout $DUMMY_PATH/privkey.pem \
      -out    $DUMMY_PATH/fullchain.pem \
      -subj \"/CN=localhost\"'" \
  certbot


# ─── 3. Start nginx so it can answer ACME challenges ───────────────────────
echo "### 3/5  Starting nginx…"
$DC up --force-recreate -d nginx


# ─── 4. Remove dummy and obtain the real certificate ───────────────────────
echo "### 4/5  Deleting dummy certificate and requesting the real one…"
$DC run --rm --entrypoint "rm -rf /etc/letsencrypt/live/$DOMAIN \
                                  /etc/letsencrypt/archive/$DOMAIN \
                                  /etc/letsencrypt/renewal/$DOMAIN.conf" \
  certbot

# Build certbot -d flags: -d foo -d www.foo
DOMAIN_ARGS=""
for d in "${DOMAINS[@]}"; do DOMAIN_ARGS="$DOMAIN_ARGS -d $d"; done

# Staging or production endpoint
if [[ "$STAGING" == "1" ]]; then
  STAGING_ARG="--staging"
  echo "    (using Let's Encrypt STAGING endpoint — certs will NOT be trusted)"
else
  STAGING_ARG=""
fi

$DC run --rm --entrypoint "\
  certbot certonly --webroot -w /var/www/certbot \
    $STAGING_ARG \
    --email $EMAIL \
    $DOMAIN_ARGS \
    --rsa-key-size $RSA_KEY_SIZE \
    --agree-tos --no-eff-email --non-interactive \
    --force-renewal" \
  certbot


# ─── 5. Reload nginx to pick up the real cert ──────────────────────────────
echo "### 5/5  Reloading nginx…"
$DC exec nginx nginx -s reload

echo ""
echo "✓ TLS setup complete. https://www.$DOMAIN/ should be live."
echo "  Certbot will auto-renew every 12 hours (no-op until T-30 days)."
echo "  Next: run  '$DC up -d'  to ensure all services are running."
