# Environment Variables

All configuration is driven by a `.env` file at the project root. Copy `sample.env` to get started:

```bash
cp sample.env .env
```

Never commit `.env` to version control. Only commit `sample.env` with safe placeholder defaults.

---

## Database

| Variable | Default | Description |
|---|---|---|
| `DB_NAME` | `local` | Database name |
| `DB_USER` | `user` | Database user |
| `DB_PASSWORD` | `password` | Database user password |
| `DB_ROOT_PASSWORD` | `rootpassword` | MariaDB root password |

---

## WordPress Application

| Variable | Default | Description |
|---|---|---|
| `WORDPRESS_VERSION` | `7.0` | Core version downloaded by `./bin/version` |
| `WORDPRESS_IMAGE_VERSION` | `${WORDPRESS_VERSION}-fpm-alpine` | Docker image tag for the PHP container |
| `WORDPRESS_ENVIRONMENT` | `local` | Environment name — sets `WP_ENV` and `WP_ENVIRONMENT_TYPE` |
| `WORDPRESS_DEBUG` | `true` | Enable WP_DEBUG |
| `WORDPRESS_DEBUG_LOG` | `/var/www/html/logs/wp.log` | WP debug log path inside the container |
| `WORDPRESS_DEBUG_DISPLAY` | `false` | Display errors on screen |
| `WORDPRESS_TABLE_PREFIX` | `wp_` | Database table prefix |
| `WORDPRESS_MEMORY_LIMIT` | `256M` | `WP_MEMORY_LIMIT` constant |

### WordPress Behaviour Flags

| Variable | Default | Description |
|---|---|---|
| `WORDPRESS_ALLOW_MULTISITE` | `0` | Set to `1` before first install for multisite |
| `WORDPRESS_MULTISITE` | `0` | Enable WordPress Multisite |
| `WORDPRESS_SUBDOMAIN_INSTALL` | `0` | Use subdomain structure for multisite |
| `WORDPRESS_AUTO_UPDATE_CORE` | `false` | Allow automatic core updates |
| `WORDPRESS_AUTOMATIC_UPDATER_DISABLED` | `true` | Disable the background updater entirely |
| `WORDPRESS_DISALLOW_FILE_EDIT` | `true` | Prevent file editing via WP admin |
| `WORDPRESS_DISALLOW_FILE_MODS` | `false` | Prevent all file modifications (plugins, themes) |
| `WORDPRESS_POST_REVISIONS` | `3` | Number of post revisions to keep |
| `WORDPRESS_FORCE_SSL_ADMIN` | `true` | Force HTTPS for admin |
| `WORDPRESS_DISABLE_WP_CRON` | `true` | Disable pseudo-cron (use system cron) |
| `WORDPRESS_EMPTY_TRASH_DAYS` | `3` | Days before permanently deleting trash |
| `WORDPRESS_CONCATENATE_SCRIPTS` | `true` | Concatenate admin JS files |
| `WORDPRESS_COMPRESS_CSS` | `true` | Compress CSS in admin |
| `WORDPRESS_COMPRESS_SCRIPTS` | `true` | Compress scripts in admin |
| `WORDPRESS_ENFORCE_GZIP` | `true` | Enforce Gzip compression |

---

## SMTP (Mailpit)

All mail is routed to the local Mailpit container during development.

| Variable | Default | Description |
|---|---|---|
| `WORDPRESS_SMTP_ON` | `true` | Enable WP Mail SMTP plugin |
| `WORDPRESS_SMTP_MAILER` | `smtp` | Mailer type |
| `WORDPRESS_SMTP_HOST` | `mailpit` | SMTP hostname (Docker service name) |
| `WORDPRESS_SMTP_PORT` | `1025` | Mailpit SMTP port |

---

## Site Identity

| Variable | Default | Description |
|---|---|---|
| `SITE_TITLE` | `My WordPress Site` | WordPress site title |
| `SITE_NAME` | `default` | Slug used to construct local hostnames |
| `SITE_ADMIN_USER` | `admin` | WP admin username (used by `./bin/install`) |
| `SITE_ADMIN_EMAIL` | `admin@admin.local` | WP admin email |
| `SITE_ADMIN_PASSWD` | `password` | WP admin password |

---

## Networking and Domains

| Variable | Default | Description |
|---|---|---|
| `HOST_NAME` | `${SITE_NAME}.local` | Primary local domain (used in Nginx and WP install) |
| `MAIL_HOST` | `mail.${SITE_NAME}.local` | Mailpit UI domain |
| `APP_DOMAINS` | `${SITE_NAME}.local *.${SITE_NAME}.local` | Space-separated list of domains for SSL cert generation |
| `PROXY_NAME` | _(none)_ | Production domain for Nginx uploads fallback proxy |
| `SSH_HOST_PRODUCTION` | _(none)_ | Production server SSH host (used by `./bin/db`) |
| `SSH_HOST_STAGE` | _(none)_ | Staging server SSH host (used by `./bin/db`) |

---

## Docker and Container Naming

| Variable | Default | Description |
|---|---|---|
| `SITE_SLUG` | `wp` | Short project slug |
| `CONTAINER_PREFIX` | `${SITE_SLUG}_` | Prefix applied to all container names and volume names |

Container names are derived as `{CONTAINER_PREFIX}{service}`:
- `wp_app` — PHP-FPM
- `wp_server` — Nginx
- `wp_db` — MariaDB
- `wp_redis` — Redis
- `wp_mail` — Mailpit

---

## Private Package Registry (Satispress)

| Variable | Default | Description |
|---|---|---|
| `SATISPRESS_URL` | `plugins.domain.com` | Domain of your Satispress instance |
| `SATISPRESS_KEY` | `satispress-key-placeholder` | API key for Satispress authentication |

`./bin/install` automatically skips Satispress configuration if `SATISPRESS_URL` still equals the placeholder value `plugins.domain.com`.

---

## User and Group IDs (Build Args)

These are passed as Docker build arguments, not environment variables in the running container. They ensure the `www-data` user inside the container matches your host user's UID/GID, preventing file permission issues.

| Arg | Default | Description |
|---|---|---|
| `USER_ID` | `1000` | Host user ID to map to `www-data` |
| `GROUP_ID` | `1000` | Host group ID to map to `www-data` |

