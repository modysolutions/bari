# CLI Scripts (`./bin/`)

All scripts must be run from the **project root** directory. They read `.env` automatically and proxy commands into the appropriate Docker container.

---

## `./bin/install`

**Master provisioning script.** Runs the full installation from scratch for a standard single-site WordPress installation.

```bash
./bin/install
```

**Sequence:**
1. Validates `.env` exists
2. Generates SSL certificates (`./bin/certs`)
3. Creates log directories (`app/logs/`)
4. Builds the Docker image and starts all services
5. Downloads WordPress core (`./bin/version`)
6. Waits for the PHP container to become ready
7. Configures Satispress authentication (if `SATISPRESS_KEY` is set)
8. Runs `./bin/composer install`
9. Runs `wp core install`
10. Runs smoke tests (`./bin/test`)
11. Prints a success message

**Uses variables:** `WORDPRESS_VERSION`, `HOST_NAME`, `SITE_TITLE`, `SITE_ADMIN_USER`, `SITE_ADMIN_EMAIL`, `SITE_ADMIN_PASSWD`, `CONTAINER_PREFIX`, `SATISPRESS_URL`, `SATISPRESS_KEY`

---

## `./bin/start`

**Start the Docker stack without rebuilding.** Use this for daily development after the initial install.

```bash
./bin/start
```

Runs `docker compose up -d --force-recreate --remove-orphans` and prints the site URL.

---

## `./bin/certs`

**Generate local SSL certificates** using `mkcert` for all domains in `APP_DOMAINS`.

```bash
./bin/certs
```

- Creates `config/ssl/` if it doesn't exist
- Runs `mkcert -cert-file ./config/ssl/local-cert.pem -key-file ./config/ssl/local-key.pem $APP_DOMAINS 127.0.0.1`
- Runs `sudo mkcert -install` to trust the CA in the system store
- Called automatically by `./bin/install`

**Uses variables:** `APP_DOMAINS`

---

## `./bin/composer [command]`

**Proxy to Composer** inside the PHP container. Sets the working directory to `/var/www/html` (the app root, where `composer.json` is).

```bash
./bin/composer install
./bin/composer require timber/timber
./bin/composer update wpackagist-plugin/wordpress-seo

# Install in a specific plugin directory
./bin/composer install --working-dir=/var/www/html/web/plugins/my-plugin
```

**Note:** The script requires at least one argument — running `./bin/composer` with no arguments will exit with an error.

**Uses variables:** `CONTAINER_PREFIX`

---

## `./bin/config KEY VALUE`

**Read and write `.env` values.** Used internally by `./bin/install` to set multisite flags, and can be used directly.

```bash
./bin/config SITE_NAME myproject
./bin/config WORDPRESS_DEBUG false
```

- Uses `sed` to update existing keys in-place
- Appends the key if it doesn't exist
- Detects macOS vs GNU `sed` automatically for cross-platform compatibility

---

## `./bin/version VERSION [--no-backup]`

**Download and install a specific WordPress version** into `app/wp/`.

```bash
./bin/version 7.0             # Install with backup of current core
./bin/version 7.0 --no-backup  # Install, skip backup
```

- `$1` = WordPress version (required)
- `$2` = `--no-backup` flag (optional)
- Downloads `https://wordpress.org/wordpress-{VERSION}.tar.gz`
- Replaces `wp-admin/` and `wp-includes/` in `app/wp/`
- Copies root PHP files (`wp-login.php`, `wp-settings.php`, etc.) to `app/wp/`
- Optionally backs up the current core to `backups/wp_core_backup_{timestamp}/`
- Cleans up the temp extraction directory
- Uses `set -o pipefail` so a failed `curl` download is always caught

**Uses variables:** none (path-based)

> ℹ️ The current default `WORDPRESS_VERSION` in `sample.env` is `7.0`, which is the latest stable release.

---

## `./bin/wp [wp-cli-command]`

**Proxy to WP-CLI** inside the PHP container, running as `www-data`.

```bash
./bin/wp plugin list
./bin/wp post list --post_status=publish
./bin/wp core is-installed
./bin/wp db export /var/www/html/backup.sql
```

The script reads `wp-cli.yml` to find the container name for the current `$SITE_SLUG` alias, then runs `docker exec -u www-data {container} wp "$@"`.

**Uses variables:** `SITE_SLUG`, `CONTAINER_PREFIX`
**Reads:** `wp-cli.yml`

---

## `./bin/test`

**Run smoke tests** against a running stack to verify its health.

```bash
./bin/test
```

The script executes a series of checks to ensure all major infrastructure components are operational and correctly interconnected. It sources `.env` to get the correct container names and hostnames.

**Sequence of Tests:**

1.  **Container Orchestration**: Verifies that all core Docker containers (`app`, `db`, `server`, `redis`, `mail`) are up and running. It dynamically constructs the expected container names using the `CONTAINER_PREFIX`.
2.  **Database Connectivity**: Executes `wp db query "SELECT 1;"` to confirm that WordPress can successfully connect to and query the MariaDB database.
3.  **Object Cache**: Runs `wp cache get ...` to ensure the Redis object cache is responsive and integrated with WordPress.
4.  **Frontend HTTP Resolution**: Makes an HTTPS request to the site's hostname from within the `app` container to check that the Nginx web server is correctly routing requests and serving content (expects a `200`, `301`, or `302` status code).
5.  **Mail Catcher**: Checks that the Mailpit API is reachable from the `app` container, confirming that the transactional mail gateway is visible.

The script will exit with a status code of `0` if all tests pass, and `1` if any test fails.

**Uses variables:** `CONTAINER_PREFIX`, `HOST_NAME`

---

## Frontend Build

Frontend assets are **not** compiled through a `bin/` script. Run the following commands directly from the **project root** (where `package.json` lives):

```bash
# Development — watch mode with source maps
pnpm start

# Production — minified, optimised, no source maps
pnpm build
```

Both commands invoke `@wordpress/scripts` with the custom webpack config at `config/webpack/webpack.config.js` and write compiled assets to `app/dist/`. See [`docs/08-frontend-build.md`](./08-frontend-build.md) for full details.
