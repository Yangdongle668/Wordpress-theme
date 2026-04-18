#!/usr/bin/env bash
# =============================================================================
# deploy.sh — one-click deploy for the BatteryCo site
# -----------------------------------------------------------------------------
# Wraps the full pipeline on a fresh Linux VM:
#   1. preflight: docker / compose / files / ports
#   2. pull: git fetch + fast-forward (skipped if no upstream)
#   3. bootstrap TLS on first run (init-letsencrypt.sh)
#   4. build + start the stack
#   5. health check + smoke test
#
# Re-running is safe. First run issues a real Let's Encrypt cert; subsequent
# runs just rebuild and restart the containers.
#
# Usage:
#   bash deploy.sh                # normal deploy
#   bash deploy.sh --skip-pull    # skip 'git pull' step
#   bash deploy.sh --staging      # use Let's Encrypt staging endpoint
#   bash deploy.sh --force-cert   # re-issue TLS cert even if one exists
#   bash deploy.sh --help
# =============================================================================

set -euo pipefail

# ─── Colors ────────────────────────────────────────────────────────────────
if [[ -t 1 ]]; then
  C_RESET=$'\033[0m'; C_BOLD=$'\033[1m'
  C_GREEN=$'\033[32m'; C_YELLOW=$'\033[33m'; C_RED=$'\033[31m'; C_BLUE=$'\033[34m'
else
  C_RESET=""; C_BOLD=""; C_GREEN=""; C_YELLOW=""; C_RED=""; C_BLUE=""
fi
step() { echo "${C_BOLD}${C_BLUE}▶${C_RESET} $*"; }
ok()   { echo "${C_GREEN}✓${C_RESET} $*"; }
warn() { echo "${C_YELLOW}!${C_RESET} $*"; }
die()  { echo "${C_RED}✗${C_RESET} $*" >&2; exit 1; }

# ─── Args ──────────────────────────────────────────────────────────────────
SKIP_PULL=0
FORCE_CERT=0
STAGING_OVERRIDE=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --skip-pull)   SKIP_PULL=1 ;;
    --staging)     STAGING_OVERRIDE=1 ;;
    --force-cert)  FORCE_CERT=1 ;;
    -h|--help)
      sed -n '2,20p' "$0" | sed 's/^# \{0,1\}//'
      exit 0 ;;
    *) die "Unknown argument: $1 (try --help)" ;;
  esac
  shift
done

# ─── Work from repo root regardless of where the script is invoked ─────────
REPO_ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
cd "$REPO_ROOT"

echo
echo "${C_BOLD}BatteryCo — one-click deploy${C_RESET}"
echo "  repo: $REPO_ROOT"
echo

# ═══ 1. Preflight ══════════════════════════════════════════════════════════
step "1/5  Preflight checks"

# Docker + compose (v2 preferred, v1 fallback)
command -v docker >/dev/null 2>&1 || die "docker is not installed. Install Docker Engine first."
if docker compose version >/dev/null 2>&1; then
  DC="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
  DC="docker-compose"
else
  die "Neither 'docker compose' nor 'docker-compose' is available."
fi
ok "docker ok  ($DC)"

# Must run as a user that can talk to the Docker socket
if ! docker info >/dev/null 2>&1; then
  die "Cannot reach the Docker daemon. Run as root or add your user to the 'docker' group."
fi

# Required files
for f in docker-compose.yml .env server/.env deploy/nginx-docker.conf \
         deploy/init-letsencrypt.sh server/Dockerfile; do
  [[ -f "$f" ]] || die "Missing required file: $f"
done
ok "required files present"

# .env sanity (DOMAIN + LETSENCRYPT_EMAIL)
# shellcheck disable=SC1091
source .env
: "${DOMAIN:?DOMAIN must be set in .env}"
: "${LETSENCRYPT_EMAIL:?LETSENCRYPT_EMAIL must be set in .env}"
ok "domain: ${C_BOLD}${DOMAIN}${C_RESET}  (cert email: $LETSENCRYPT_EMAIL)"

# server/.env sanity (bare-minimum SMTP_HOST must be filled, not a placeholder)
if grep -q '^SMTP_HOST=$' server/.env || ! grep -q '^SMTP_HOST=' server/.env; then
  die "server/.env is missing SMTP_HOST. Fill in SMTP credentials before deploying."
fi
if grep -Eq '^SMTP_(USER|PASS)=$' server/.env; then
  warn "server/.env has an empty SMTP_USER or SMTP_PASS — forms will fail to send mail."
fi
ok "server/.env has SMTP config"

# Ports 80 + 443 available (unless our own nginx is already on them)
port_busy() {
  local port="$1"
  if command -v ss >/dev/null 2>&1; then
    ss -ltn "sport = :$port" 2>/dev/null | tail -n +2 | grep -q .
  else
    netstat -ltn 2>/dev/null | awk '{print $4}' | grep -Eq "[.:]${port}$"
  fi
}
own_port() {
  $DC ps --format '{{.Service}} {{.Publishers}}' 2>/dev/null \
    | grep -E "^nginx .*:$1->" >/dev/null 2>&1
}
for p in 80 443; do
  if port_busy "$p" && ! own_port "$p"; then
    warn "port $p appears to be in use by another process — compose will fail to bind"
  fi
done

# ═══ 2. Pull latest code ═══════════════════════════════════════════════════
if [[ "$SKIP_PULL" -eq 0 ]] && [[ -d .git ]]; then
  step "2/5  git pull --ff-only"
  if git remote get-url origin >/dev/null 2>&1; then
    git fetch --prune origin
    BRANCH="$(git rev-parse --abbrev-ref HEAD)"
    if git show-ref --verify --quiet "refs/remotes/origin/$BRANCH"; then
      git pull --ff-only origin "$BRANCH" || warn "fast-forward pull failed; continuing with local HEAD"
    else
      warn "no origin/$BRANCH — skipping pull"
    fi
  else
    warn "no 'origin' remote configured — skipping pull"
  fi
  ok "code up to date  ($(git rev-parse --short HEAD))"
else
  step "2/5  Skipping git pull"
fi

# ═══ 3. Bootstrap TLS cert on first run ════════════════════════════════════
step "3/5  TLS certificate"

CERT_EXISTS=0
# volume exists + has a live/<domain> dir?
if docker volume inspect batteryco_certbot_etc >/dev/null 2>&1; then
  if docker run --rm -v batteryco_certbot_etc:/etc/letsencrypt alpine:3 \
       test -f "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" 2>/dev/null; then
    CERT_EXISTS=1
  fi
fi

if [[ "$FORCE_CERT" -eq 1 ]] || [[ "$CERT_EXISTS" -eq 0 ]]; then
  if [[ "$CERT_EXISTS" -eq 1 ]]; then
    warn "--force-cert: re-issuing TLS certificate for $DOMAIN"
  else
    echo "  first run — issuing Let's Encrypt cert for $DOMAIN"
  fi

  # Pass STAGING through to init-letsencrypt.sh via env
  if [[ -n "$STAGING_OVERRIDE" ]]; then
    export STAGING=1
    warn "using Let's Encrypt STAGING endpoint — browsers will NOT trust this cert"
  fi

  # init-letsencrypt.sh prompts on existing certs; auto-confirm when forced
  if [[ "$FORCE_CERT" -eq 1 ]]; then
    yes y | bash deploy/init-letsencrypt.sh
  else
    bash deploy/init-letsencrypt.sh
  fi
  ok "TLS cert in place"
else
  ok "TLS cert already present — skipping bootstrap (use --force-cert to re-issue)"
fi

# ═══ 4. Build + start the stack ════════════════════════════════════════════
step "4/5  Building images and starting services"
$DC pull --ignore-pull-failures nginx certbot >/dev/null 2>&1 || true
$DC up -d --build --remove-orphans
ok "stack up"
$DC ps

# ═══ 5. Health check + smoke test ══════════════════════════════════════════
step "5/5  Health check"

# Wait up to 60s for the form service to respond on the nginx-proxied path
HEALTH_URL="https://www.${DOMAIN}/api/health"
for i in $(seq 1 30); do
  if curl -fsSk --max-time 3 "$HEALTH_URL" >/dev/null 2>&1; then
    ok "/api/health  → 200"
    break
  fi
  if [[ "$i" -eq 30 ]]; then
    warn "/api/health did not respond after 60s"
    warn "check: $DC logs --tail=80 form  and  $DC logs --tail=80 nginx"
  fi
  sleep 2
done

# Homepage reachability (through the real cert — no -k)
if curl -fsI --max-time 5 "https://www.${DOMAIN}/en/" >/dev/null 2>&1; then
  ok "https://www.${DOMAIN}/en/  → 200 (trusted TLS)"
elif curl -fsIk --max-time 5 "https://www.${DOMAIN}/en/" >/dev/null 2>&1; then
  warn "homepage reachable but TLS cert is not trusted (staging, dummy, or DNS mismatch?)"
else
  warn "homepage did not respond on https://www.${DOMAIN}/en/"
fi

echo
echo "${C_GREEN}${C_BOLD}✓ deploy finished${C_RESET}"
echo "  site:     https://www.${DOMAIN}/"
echo "  api:      https://www.${DOMAIN}/api/health"
echo "  logs:     $DC logs -f --tail=100"
echo "  redeploy: bash deploy.sh"
echo
