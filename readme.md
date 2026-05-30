# 🛡️ Barí — WordPress Project Framework

Barí is a self-contained, opinionated WordPress development framework built around Docker. It provides a fully controlled, reproducible local and production-ready WordPress stack designed for teams that want to build WordPress sites without depending on centralised SaaS infrastructure. It is intended as a white-label base for corporate and client WordPress projects.

## What it is

Barí combines a **Docker-based infrastructure layer** with a **custom WordPress theme** and a **shell script CLI** that abstracts all container operations behind simple proxy commands. The result is a consistent, version-controlled environment where every developer on a team runs the same stack with one command.

The stack consists of:

- **Nginx** — Reverse proxy with HTTPS, URL rewriting for the isolated WordPress core directory, and a transparent production uploads proxy for local development
- **PHP-FPM** — A custom-built WordPress image with WP-CLI, Composer, Xdebug, Redis, and image processing extensions pre-installed
- **MariaDB** — The relational database, fully configured via environment variables
- **Redis** — Available for object caching
- **Mailpit** — Captures all outgoing email and provides a web UI, preventing any email from reaching real inboxes during development

All configuration — database credentials, WordPress constants, SMTP, memory limits, debug flags — is injected via a `.env` file. There is no hardcoded configuration in the codebase.


## How it's structured

```
bari/
├── app/          → WordPress application (plugins, themes, content)
├── bin/          → CLI proxy scripts (composer, wp, install, certs, db…)
├── config/       → Infrastructure config (Dockerfile, Nginx, PHP, Webpack)
├── src/          → Frontend source (JS + SCSS, compiled into the theme)
├── compose.yml   → Docker service orchestration
└── sample.env    → Environment variable template
```

WordPress core lives in `app/wp/` — isolated from `app/web/` (the content directory) following a Bedrock-like pattern. Nginx rewrites all `wp-admin`, `wp-includes`, and `wp-*.php` paths to `/wp/`. This keeps core upgrades clean and keeps content directories outside of the core directory.

PHP dependencies (plugins, themes) are managed via **Composer + WPackagist**. No manual plugin downloads. Premium plugins can be served from a private Satispress instance configured in `.env`.

## The theme

The custom theme (`app/web/themes/theme/`) is an Astra child theme built on a **hook-based OOP architecture** under the `App\` namespace. It uses **Timber/Twig** for all HTML output: Twig owns the page shell, and Gutenberg block content renders inside `{{ post.content }}` via WordPress's `do_blocks()`.

The block editor is configured via `theme.json`, which defines a controlled design system: colour palette, fluid typography scale, spacing presets, and element styles for headings, links, and buttons. Editors work within these constraints rather than having full freestyle control.

---

## The CLI

Every day-to-day operation has a shell script in `bin/` that proxies transparently into the correct Docker container:

```bash
./bin/install         # Full environment provisioning from scratch
./bin/start           # Start the stack (after initial install)
./bin/wp plugin list  # Run WP-CLI commands
./bin/composer install  # Run Composer
./bin/certs           # Regenerate SSL certs
./bin/version 6.7.2   # Install a specific WordPress core version
./bin/db pull         # Pull a database from a remote server
```

---

## Full Documentation

Detailed documentation for each layer of the project is in the `docs/` directory:

| Document | Contents |
|---|---|
| [`docs/01-architecture.md`](docs/01-architecture.md) | Full directory map and architectural decisions |
| [`docs/02-infrastructure.md`](docs/02-infrastructure.md) | Docker services, Dockerfile, Nginx, PHP config |
| [`docs/03-environment.md`](docs/03-environment.md) | Complete `.env` variable reference |
| [`docs/04-quick-start.md`](docs/04-quick-start.md) | Step-by-step setup guide |
| [`docs/05-cli-scripts.md`](docs/05-cli-scripts.md) | All `bin/` scripts documented |
| [`docs/06-php-wordpress.md`](docs/06-php-wordpress.md) | Composer setup, plugin dependencies, core isolation |
| [`docs/07-theme.md`](docs/07-theme.md) | Theme architecture, hook classes, Twig templates |
| [`docs/08-frontend-build.md`](docs/08-frontend-build.md) | Webpack, SCSS structure, design tokens |
| [`docs/09-known-issues.md`](docs/09-known-issues.md) | All known bugs and incomplete features |

---

## Quick Start

```bash
cp sample.env .env          # Configure your environment
# Edit .env: set SITE_NAME, HOST_NAME, APP_DOMAINS, admin credentials
./bin/install               # Provisions the full stack
```

---

## Known Issues

The following bugs and incomplete features exist in the current codebase. Full details and recommended fixes are in [`docs/09-known-issues.md`](docs/09-known-issues.md).

### 🔴 Critical

- **`functions.php` imports `App\Hooks\Acf` but `Acf.php` does not exist** — causes a PHP Fatal Error that prevents the theme from loading.
- **`single.twig` includes `partials/comment.twig` and `partials/comment-form.twig`** — neither file exists; Timber throws a loader error on any post with comments.

### 🟠 High

- **`bari-cli` plugin is a 5-line stub** — the migration engine, WP-CLI `migration` commands, and `pattern` commands described in the docs are not implemented.
- **`bin/to` script does not exist** — referenced in documentation but missing from disk. Use `docker exec -it {container} sh` directly.
- **`bin/pnpm` uses the wrong working directory** (`app/wp-content` instead of the project root or `app/web/`) — the script always fails.
- **`bin/pnpm` empty-check is broken** (`if [ PNPM = "" ]` should be `if [ -z "$PNPM" ]`) — the missing-pnpm error never triggers.

### 🟡 Medium

- **`bin/db` has hardcoded project-specific database aliases** (`ei`, `ti`, `ci`, `cti`) — unusable for new projects without modification.
- **`!\Timber::class` check is always `false`** in `functions.php` and `Views.php` — Timber-missing admin notices never show.
- **`single.twig` featured image has no `alt`, `loading`, or dimension attributes** — accessibility and performance issue.
- **`bin/pnpm` references `.env.example`** in its error message — the correct file is `sample.env`.
- **`Theme.php` sets `page_template` to `home.php`** — this template does not exist in the theme.

### 🔵 Low

- **`assets/styles/main.css`** exists but is not enqueued — any styles it contains are not loaded.
- **`bin/version` pipe error handling** — curl failures may not be caught correctly on all shells.
- **`WORDPRESS_VERSION=7.0`** in `sample.env` — verify this version exists before running `./bin/install`.

---

## License

GPL-2.0-or-later. Free to use, modify, redistribute, and package as a white-label foundation for client projects.
