# ─── Node form service ────────────────────────────────────────────────────
# Multi-stage isn't needed — just Express + deps — but we use a two-layer
# approach to keep the image tight and cache npm install separately from
# source code for faster incremental rebuilds.

FROM node:20-alpine AS deps
WORKDIR /app
COPY package.json package-lock.json* ./
# Prefer ci (reproducible) when lockfile is present, fall back to install.
RUN if [ -f package-lock.json ]; then npm ci --omit=dev; \
    else npm install --omit=dev --no-audit --no-fund; fi


FROM node:20-alpine AS runtime
WORKDIR /app

# Small tools for healthchecks
RUN apk add --no-cache curl tini \
 && addgroup -g 1001 -S app \
 && adduser  -u 1001 -S app -G app

# Copy installed deps from the deps stage
COPY --from=deps /app/node_modules ./node_modules

# Copy application source (respects .dockerignore at the build context)
COPY --chown=app:app . .

# Ensure runtime dirs exist and are writable by the non-root user
RUN mkdir -p /app/logs /app/uploads \
 && chown -R app:app /app/logs /app/uploads

USER app
EXPOSE 3000

# tini as PID 1 → clean signal handling, prompt graceful shutdown
ENTRYPOINT ["/sbin/tini", "--"]
CMD ["node", "server.js"]

HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
  CMD curl -fsS http://127.0.0.1:3000/api/health || exit 1
