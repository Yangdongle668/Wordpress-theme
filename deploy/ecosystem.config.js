/**
 * deploy/ecosystem.config.js
 * ---------------------------------------------------------------------------
 * PM2 process definition for the BatteryCo Node form service.
 *
 * Use:
 *   pm2 start  deploy/ecosystem.config.js --env production
 *   pm2 reload deploy/ecosystem.config.js --env production    # zero-downtime
 *   pm2 save
 *   pm2 logs batteryco-form
 *
 * On first install of pm2 on a new box:
 *   sudo pm2 startup systemd -u deploy --hp /home/deploy
 *   pm2 save                                                  # persist the list
 */

module.exports = {
  apps: [
    {
      name: 'batteryco-form',
      cwd: '/var/www/batteryco/server',
      script: 'server.js',

      // Cluster mode — lets PM2 scale across CPU cores and do zero-downtime
      // reloads. For a form service the traffic is tiny; 2 instances is
      // plenty and keeps a warm standby during reload.
      instances: 2,
      exec_mode: 'cluster',

      // Auto-restart policy
      autorestart: true,
      max_restarts: 20,
      min_uptime: '30s',
      restart_delay: 2000,
      kill_timeout: 8000,          // graceful SIGTERM window

      // Keep memory in check — restart a leaked worker before it bites
      max_memory_restart: '256M',

      // Log rotation is handled by pm2-logrotate module (installed separately)
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      out_file: '/var/log/batteryco/form-out.log',
      error_file: '/var/log/batteryco/form-err.log',
      merge_logs: true,

      // Environment variables. Real secrets live in /var/www/batteryco/server/.env
      // which dotenv loads at process start — we don't duplicate them here.
      env: {
        NODE_ENV: 'development',
      },
      env_production: {
        NODE_ENV: 'production',
      },

      // Prevent PM2 watch from interfering in production; rely on manual reload.
      watch: false,
    },
  ],

  /* ─── Deploy block (optional): use `pm2 deploy production setup / update` ─
     Requires passwordless SSH from your laptop to the production box. */
  deploy: {
    production: {
      user: 'deploy',
      host: ['web-01.example.com'],
      ref: 'origin/main',
      repo: 'git@github.com:yangdongle668/wordpress-theme.git',
      path: '/var/www/batteryco',
      'post-deploy':
        'cd server && npm ci --omit=dev && pm2 reload deploy/ecosystem.config.js --env production && pm2 save',
      'pre-setup': 'mkdir -p /var/log/batteryco && chown -R deploy:deploy /var/log/batteryco',
    },
  },
};
