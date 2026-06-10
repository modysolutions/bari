# Quick Start

## Prerequisites

Install the following tools on your host machine before continuing:

| Tool | Version | Purpose |
|---|---|---|
| [Docker](https://docs.docker.com/get-docker/) + Docker Compose | V2+ | Container runtime |
| [pnpm](https://pnpm.io/installation) | 10+ | Node.js package manager |
| [mkcert](https://github.com/FiloSottile/mkcert) | Any | Local SSL certificate generation |

---

## Step 1 — Configure environment variables

```bash
cp sample.env .env
```

Open `.env` and update at minimum:

```dotenv
# Your project's local domain(s) — must match APP_DOMAINS for SSL certs
SITE_NAME=myproject
HOST_NAME="${SITE_NAME}.local"
MAIL_HOST="mail.${SITE_NAME}.local"
APP_DOMAINS="${SITE_NAME}.local *.${SITE_NAME}.local"

# Gutenberg/WP container prefix (keep unique if running multiple projects)
SITE_SLUG=myproject
CONTAINER_PREFIX="${SITE_SLUG}_"

# Admin credentials for the WordPress install
SITE_ADMIN_USER=admin
SITE_ADMIN_EMAIL=admin@myproject.local
SITE_ADMIN_PASSWD=yourpassword

# Site title
SITE_TITLE="My Project"
```

---

## Step 2 — Run the install script

```bash
./bin/install
```

For a **WordPress Multisite** installation:

```bash
./bin/install multisite
```

The install script performs the following steps automatically:

1. Checks that `.env` exists and sources it
2. Generates local SSL certificates via `mkcert` (runs `./bin/certs`)
3. Creates log directories (`app/logs/`, `logs/nginx/`, `logs/wordpress/`)
4. Writes multisite/single-site flags to `.env` based on the install mode
5. Builds the Docker image and starts all services
6. Downloads WordPress core into `app/wp/` (via `./bin/version`)
7. Waits for the PHP container to become ready (polls up to 90 seconds)
8. Configures Satispress authentication (skipped if using placeholder URL)
9. Runs `composer install` inside the PHP container
10. Installs WordPress via WP-CLI (skipped if already installed)
11. Prints the site URL, admin URL, credentials, and mail UI URL

---

## Step 3 — Access the site

After `./bin/install` completes:

| URL | Content |
|---|---|
| `https://myproject.local` | Your WordPress site |
| `https://myproject.local/wp/wp-admin/` | WordPress admin |
| `https://mail.myproject.local` | Mailpit email UI |

> The Nginx production uploads proxy means you do not need a copy of the production `uploads/` folder locally. Missing files are fetched transparently from `$PROXY_NAME`.

---

## Daily Workflow

```bash
# Start the stack (after first install, no rebuild)
./bin/start

# Run WP-CLI commands
./bin/wp plugin list
./bin/wp post list

# Open a shell inside a container
# (Note: ./bin/to does not currently exist — use docker exec directly)
docker exec -it wp_app sh

# Install a new PHP dependency
./bin/composer require some/package

# Build frontend assets (watch mode)
pnpm start

# Build for production
pnpm build
```

---

## Stopping and Cleaning Up

```bash
# Stop all containers
docker compose down

# Stop and remove volumes (destroys database)
docker compose down -v
```

---

## Re-running Install on Existing Site

`./bin/install` checks `wp core is-installed` before attempting a WordPress install. Running it again on an already-installed site is safe — it rebuilds Docker, re-downloads WP core, and re-runs Composer, but skips the WP database install step.

---

## wp-cli.yml

WP-CLI is configured in `wp-cli.yml` at the project root:

```yaml
path: /var/www/html

@wp:
  ssh: docker:www-data@wp_app
```

- `path` tells WP-CLI where WordPress is installed inside the container
- `@wp` is the alias used by `./bin/wp` (derived from `$SITE_SLUG` in `.env`)

If you change `SITE_SLUG`, update the alias name in `wp-cli.yml` to match.

