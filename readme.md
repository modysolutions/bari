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
├── bin/          → CLI proxy scripts (composer, wp, install, certs, version…)
├── config/       → Infrastructure config (Dockerfile, Nginx, PHP, Webpack)
├── src/          → Frontend source (JS + SCSS, compiled into app/dist/)
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
./bin/version 7.0     # Install a specific WordPress core version
```

Frontend assets are built directly from the project root using `pnpm` (no `bin/` wrapper):

```bash
pnpm start   # Development — watch mode with source maps
pnpm build   # Production — minified, optimised, no source maps
```

Compiled assets are written to `app/dist/` and enqueued by the theme automatically.

---

## The `bari-cli` Plugin

The `app/web/plugins/bari-cli/` plugin ships two WP-CLI command groups that are available via `./bin/wp`:

### `wp migration` — Database Migration Engine

```bash
./bin/wp migration create "create orders table"
./bin/wp migration migrate
./bin/wp migration rollback
./bin/wp migration status
```

Migration files live in `app/web/plugins/bari-cli/migrations/` by default and extend `AbstractMigration` with `up()` and `down()` methods. See [`docs/06-php-wordpress.md`](docs/06-php-wordpress.md) for full details.

### `wp pattern` — Gutenberg Block Pattern Management

```bash
./bin/wp pattern create sections/hero
./bin/wp pattern export 42 sections/hero --title="Hero – Full Width"
./bin/wp pattern list
```

Patterns are stored in `app/web/themes/theme/app/Patterns/` and auto-registered by the theme. See [`docs/06-php-wordpress.md`](docs/06-php-wordpress.md) for full details.

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
| [`docs/06-php-wordpress.md`](docs/06-php-wordpress.md) | Composer setup, plugin dependencies, core isolation, bari-cli |
| [`docs/07-theme.md`](docs/07-theme.md) | Theme architecture, hook classes, Twig templates |
| [`docs/08-frontend-build.md`](docs/08-frontend-build.md) | Webpack, SCSS structure, design tokens |
| [`docs/09-known-issues.md`](docs/09-known-issues.md) | Resolved issues log |

---

## Quick Start

```bash
cp sample.env .env          # Configure your environment
# Edit .env: set SITE_NAME, HOST_NAME, APP_DOMAINS, admin credentials
./bin/install               # Provisions the full stack
```

---

## License

GPL-2.0-or-later. Free to use, modify, redistribute, and package as a white-label foundation for client projects.
