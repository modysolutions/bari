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

The install script performs the following steps automatically:

1. Checks that `.env` exists and sources it
2. Generates local SSL certificates via `mkcert` (runs `./bin/certs`)
3. Creates log directories (`app/logs/`)
4. Builds the Docker image and starts all services
5. Downloads WordPress core into `app/wp/` (via `./bin/version`)
6. Waits for the PHP container to become ready
7. Configures Satispress authentication (if `SATISPRESS_KEY` is set)
8. Runs `composer install` inside the PHP container
9. Installs WordPress via WP-CLI (for a standard single-site)
10. Runs smoke tests via `./bin/test`
11. Prints a success message upon completion

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

# The `./bin/wp` script is a wrapper that executes `wp-cli` inside the running
# PHP container. It automatically determines the correct container name.

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

The project root contains a `wp-cli.yml` file to configure WP-CLI behavior. The important part is the alias definition:

```yaml
path: app/wp

@wp:
  ssh: docker:www-data@myproject_app
```

- `path` tells WP-CLI where WordPress is installed *relative to the project root inside the container*.
- `@wp` is a pre-configured alias. The `./bin/wp` script is hardcoded to use this alias.

The `ssh` key specifies the container and user for the command. The container name (`myproject_app` in the example) is dynamically generated based on your `CONTAINER_PREFIX` from `.env`. The `./bin/wp` wrapper script reads this configuration and automatically executes commands in the correct running container.

**You do not need to edit this file.** The script handles the dynamic container name for you.
