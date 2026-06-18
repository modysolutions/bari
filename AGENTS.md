# AGENTS.md — Barí WordPress Stack

_Last reviewed: 2026-06-18_

This file gives automated coding agents the project-specific context and rules needed to work safely in this repository.

## Project identity

Barí is a self-contained, Docker-based WordPress project framework. It is intended as a white-label base for client and corporate WordPress projects where the full stack is controlled by the repository rather than by centralized SaaS services.

High-level stack:

- **Runtime:** Nginx, PHP-FPM WordPress image, MariaDB, Redis, Mailpit.
- **WordPress core:** isolated in `app/wp/`.
- **Public content directory:** `app/web/`, configured through `WP_CONTENT_DIR` and `WP_CONTENT_URL` in `app/wp-config.php`.
- **Custom theme:** `app/web/themes/theme/`.
- **Custom CLI plugin:** `app/web/plugins/bari-cli/`.
- **Frontend source:** `src/`, compiled to `app/dist/`.
- **Automation:** shell scripts in `bin/` proxy Composer, WP-CLI, setup, certificates, smoke tests, and WordPress core versioning.

## Source-of-truth priority

When files disagree, use this priority order:

1. Current source files in the repository.
2. `compose.yml`, `sample.env`, `wp-cli.yml`, `app/composer.json`, `app/wp-config.php`, `config/**`, `bin/**`.
3. Documentation in `documentation/**`.
4. Older prose in `readme.md` or stale comments.

Important current-state correction:

- Treat `app/web/themes/theme/` as a **Gutenberg-native theme** with block templates, block template parts, `theme.json`, style variations, and WordPress block markup.
- Do **not** convert the theme back to a Twig-first or Astra-child-theme architecture.
- Timber/Twig is installed and some hook code still registers Timber helpers, but Timber is not the primary theme rendering model. Use it only when existing code requires it or for plugin/server-rendered views where appropriate.
- The current `style.css` does **not** declare `Template: astra`; do not assume the custom theme is an Astra child theme.

## Repository map

```text
bari/
  app/
	composer.json             # PHP dependencies, WPackagist, Composer installer paths
	wp-config.php             # WordPress env/path bootstrap
	wp/                       # Isolated WordPress core
	web/                      # Public content root
	  plugins/
		bari-cli/             # WP-CLI custom commands and migrations
	  themes/
		theme/                # Gutenberg-native custom theme
  bin/                        # Project CLI wrappers; run from repo root
  config/                     # Docker, Nginx, PHP, SSL, Webpack config
  documentation/              # Project documentation
  src/                        # Frontend JS/SCSS source
  compose.yml                 # Docker Compose stack
  package.json                # pnpm + @wordpress/scripts build scripts
  sample.env                  # Safe environment template
  wp-cli.yml                  # WP-CLI config used by bin/wp
```

## Never edit these directly

Do not edit these paths unless the user explicitly asks for dependency/core/vendor patching:

- `app/wp/**` — WordPress core.
- `app/vendor/**` — Composer-installed PHP dependencies.
- `node_modules/**` — Node dependencies, if present.
- Third-party plugins in `app/web/plugins/**`, except custom project plugins such as `bari-cli` or plugins created for this project.
- Third-party themes in `app/web/themes/**`, except `app/web/themes/theme/`.
- Generated local secrets or environment files such as `.env`, `app/auth.json`, and SSL private keys.

Prefer changing project behavior in:

- Custom plugins under `app/web/plugins/bari-*` or a new project plugin.
- The Gutenberg-native theme under `app/web/themes/theme/` for presentation-only concerns.
- Shared frontend source under `src/`.
- Project configuration in `config/` only when infrastructure/build behavior must change.

## Local environment and commands

Run all project scripts from the repository root.

Initial setup:

```bash
cp sample.env .env
./bin/install
```

Daily startup:

```bash
./bin/start
```

WP-CLI through the project wrapper:

```bash
./bin/wp plugin list
./bin/wp theme list
./bin/wp post list
./bin/wp core is-installed
```

Composer through the project wrapper:

```bash
./bin/composer install
./bin/composer require vendor/package
./bin/composer update vendor/package
```

Frontend build commands are run directly from the repository root, not through `bin/`:

```bash
pnpm start
pnpm build
```

Smoke tests for a running stack:

```bash
./bin/test
```

Stop containers:

```bash
docker compose down
```

Stop containers and destroy volumes/database:

```bash
docker compose down -v
```

## Docker and WordPress runtime specifics

- `compose.yml` defines services `nginx`, `wordpress`, `database`, `redis`, and `mailpit`.
- Container names are derived from `CONTAINER_PREFIX`, for example `${CONTAINER_PREFIX}app`, `${CONTAINER_PREFIX}server`, `${CONTAINER_PREFIX}db`, `${CONTAINER_PREFIX}redis`, `${CONTAINER_PREFIX}mail`.
- The PHP-FPM image is built from `config/Dockerfile` and includes WP-CLI, Composer, Redis extension, Xdebug, MariaDB client, msmtp, and image tooling.
- Nginx serves the app, terminates SSL, rewrites WordPress core paths to `app/wp/`, and can proxy missing uploads to `PROXY_NAME`.
- Mailpit captures development email at the host configured by `MAIL_HOST`.
- Redis is available to WordPress, but object-cache behavior depends on the active caching plugin/configuration.

## Environment rules

- `.env` is required locally and is derived from `sample.env`.
- Never commit real secrets, production credentials, API keys, salts, private keys, or database dumps.
- Add new environment variables to `sample.env` with safe placeholder defaults and document them in `documentation/03-environment.md`.
- Use WordPress APIs and environment constants instead of hardcoded URLs or filesystem paths.
- Important path constants in `app/wp-config.php`:
  - `WP_SITEURL` points to `/wp`.
  - `WP_CONTENT_DIR` points to `app/web`.
  - `WP_CONTENT_URL` points to `/web`.
  - `ABSPATH` points to `app/wp/`.

## PHP and Composer rules

- Main PHP dependency manifest: `app/composer.json`.
- Composer installs WordPress plugins to `app/web/plugins/{$name}/` and WordPress themes to `app/web/themes/{$name}/`.
- Do not manually download third-party plugins or themes into the repo.
- Do not add `composer.json` files inside custom plugin directories unless the user explicitly changes that architecture.
- Install shared PHP libraries through `app/composer.json` and rely on the global autoloader.
- Theme classes currently autoload from `App\\` to `web/themes/theme/app`.
- Use namespaced PHP under `App\...` for theme code and project plugins unless a plugin has an established namespace.
- Avoid generic global function names. If a global function is unavoidable, prefix it with the plugin slug.

## Coding standards

General principles:

- Do not edit WordPress core, vendor code, or third-party plugins directly.
- Prefer feature plugins for business logic.
- Keep the theme presentation-focused.
- Keep code multisite-aware where relevant.
- Avoid hardcoded URLs and paths; use WordPress helpers such as `plugins_url()`, `plugin_dir_path()`, `plugin_dir_url()`, `get_stylesheet_directory_uri()`, `content_url()`, `WP_CONTENT_DIR`, and `WP_CONTENT_URL`.
- Document operational side effects: custom tables, rewrites, scheduled jobs, user mutations, external API calls, access-control changes, and migrations.

PHP:

- Preserve the local indentation/style of the edited file.
- Prefer small methods, early returns, strict comparisons, and explicit hook argument counts.
- Sanitize all input from requests, cookies, headers, REST params, and AJAX payloads.
- Use `wp_unslash()` before sanitizing WordPress request data.
- Escape output as late as possible with the correct escaping function.
- Use `$wpdb->prepare()` for dynamic SQL and `$wpdb->prefix` for custom tables.
- Use reversible migrations for schema changes where possible.
- Check capabilities and nonces for admin/AJAX state changes.
- Every REST route must have a `permission_callback`.

JavaScript and CSS:

- Prefer modern JavaScript modules.
- Use `@wordpress/*` packages for Gutenberg/editor React code.
- Avoid undocumented globals; localize settings through WordPress enqueue APIs when needed.
- Scope CSS classes to the theme/plugin/component.
- Prefer `theme.json` presets and CSS custom properties over hardcoded design values.
- Avoid inline styles generated from PHP unless values are sanitized and the need is documented.

## Gutenberg-native theme rules

The theme at `app/web/themes/theme/` is Gutenberg-native. Work with WordPress block theme conventions first.

Primary theme files and directories:

- `theme.json` — design tokens, editor controls, global styles, block styles, and style variation foundations.
- `templates/*.html` — block templates such as `index.html`, `page.html`, `single.html`, `archive.html`, `home.html`, `search.html`, and `404.html`.
- `parts/*.html` — block template parts such as `header.html`, `footer.html`, and `sidebar.html`.
- `styles/*.json` and nested style files — style variations and additional global style presets.
- `functions.php` — bootstrap for autoloading and hook classes.
- `app/Hooks/*.php` — WordPress integration hooks.
- `assets/` — static theme assets such as fonts/images/editor styles.

When changing the theme:

- Prefer block markup in `templates/*.html` and `parts/*.html` for layout.
- Prefer `theme.json` for design-system changes: palette, typography, spacing, layout widths, block supports, and element styles.
- Keep editor and frontend parity in mind. If a style affects blocks, ensure it works in the editor as well as the frontend.
- Do not replace block templates with Twig templates.
- Do not introduce theme-level business logic, custom data access, integrations, or access-control decisions.
- Keep CPTs, taxonomies, REST APIs, migrations, external integrations, and business rules in plugins.
- Use block patterns for reusable presentation-only sections.
- If a native custom block is needed, use `block.json` as the source of truth and register it with `register_block_type()` from a plugin unless it is strictly presentation-only theme functionality.
- Avoid ACF blocks for highly interactive editor experiences; prefer native Gutenberg blocks using `@wordpress/scripts`.
- Spectra/UAGB blocks are present via `ultimate-addons-for-gutenberg`; use them only as presentation/layout primitives when existing content requires them.

Current theme hook classes:

- `App\Hooks\Gutenberg` registers theme supports including `block-templates`, `block-template-parts`, `editor-styles`, `wp-block-styles`, `responsive-embeds`, and `appearance-tools`; it removes core block patterns.
- `App\Hooks\App` handles menus, frontend asset enqueues, cleanup, archive restrictions, content filtering, admin tweaks, and script behavior.
- `App\Hooks\Security` removes REST/oEmbed discovery links from public output.
- `App\Hooks\Theme` performs first-activation scaffold cleanup and creates a Home page.
- `App\Hooks\Views` contains Timber integration helpers. Treat these as compatibility/support code, not as the primary rendering architecture.

Known theme implementation notes agents must verify before changing:

- `src/theme.js` imports `./app.scss`, but the documented webpack entry currently expects `src/theme/app.js`. Check `config/webpack/webpack.config.js` and the actual `src/` tree before changing build entries.
- `App\Hooks\App::scripts()` and `styles()` read `app/dist/theme.asset.php` and enqueue `theme.js`/`theme.css` from `/dist/`. Verify asset paths if touching enqueue code.
- Existing docs may mention `app/Views/*.twig`; do not assume those views are used for frontend page rendering while block templates exist.

## Frontend build rules

- Build system: `@wordpress/scripts` via `package.json`.
- Package manager: `pnpm@10.14.0` as declared in `package.json`.
- Webpack extension file: `config/webpack/webpack.config.js`.
- Compiled output: `app/dist/`.
- Generated `*.asset.php` files should be used for WordPress dependencies and cache-busting.
- If adding plugin frontend assets, prefer `src/plugins/<plugin-slug>/app.js` and `app.scss`, then ensure the webpack config discovers or registers the entry.
- Do not create independent build pipelines inside theme/plugin directories unless the project architecture is intentionally changed.

## Custom plugin rules

Business features belong in custom plugins, not in the theme.

Recommended custom plugin layout:

```text
app/web/plugins/plugin-slug/
  plugin-slug.php
  app/
	Bootstrap.php
	Hooks/
	Admin/
	Frontend/
	Rest/
  templates/
  template-parts/
  assets/
```

Plugin requirements:

- Include complete plugin headers: `Plugin Name`, `Description`, `Version`, `Author`, `Author URI`, `Text Domain`, `Requires PHP`, and `Requires at least`.
- Define plugin constants for path, URL, version, and text domain.
- Register activation/deactivation hooks only in the main plugin file.
- Flush rewrites only when the plugin owns rewrite rules.
- Remove scheduled events on deactivation.
- Register CPTs and taxonomies on `init`.
- Keep slugs stable after launch.
- Document public endpoints, CPT rewrite slugs, custom tables, scheduled jobs, and external API behavior.
- Enqueue assets only where needed and use generated `*.asset.php` metadata where available.

## `bari-cli` plugin

The custom plugin `app/web/plugins/bari-cli/` provides WP-CLI commands.

Migration commands:

```bash
./bin/wp migration create "create orders table"
./bin/wp migration migrate
./bin/wp migration rollback
./bin/wp migration status
```

Pattern commands:

```bash
./bin/wp pattern create sections/hero
./bin/wp pattern export 42 sections/hero --title="Hero – Full Width" --overwrite
./bin/wp pattern list
```

Migration rules:

- Default migration directory is `app/web/plugins/bari-cli/migrations/` unless `BARI_MIGRATIONS_DIR` is overridden.
- Migrations should return an anonymous class extending `BariCli\Migration\AbstractMigration`.
- Provide both `up()` and `down()` methods.
- Use helper methods such as `createTable()`, `dropTable()`, `tableExists()`, `columnExists()`, and `statement()`.
- Use `$this->db->prefix` for table names.

Pattern rules:

- Pattern files are intended for reusable Gutenberg block markup.
- Keep patterns presentation-only.
- Do not put database queries, REST calls, or business decisions inside patterns.

## Validation checklist

Use the narrowest reliable validation for the files changed.

For PHP syntax on changed PHP files:

```bash
php -l path/to/file.php
```

For Composer-managed PHP changes, when the Docker stack is running:

```bash
./bin/composer install
./bin/wp plugin list
./bin/wp theme list
```

For frontend source or webpack changes:

```bash
pnpm build
```

For infrastructure or WordPress runtime changes, when containers are expected to be available:

```bash
./bin/test
```

For custom database/schema changes:

```bash
./bin/wp migration status
./bin/wp migration migrate
./bin/wp migration rollback
```

Also inspect logs when debugging runtime issues:

```bash
tail -n 100 app/logs/wp.log
tail -n 100 app/logs/xdebug.log
```

## Documentation maintenance

Update documentation whenever behavior changes:

- `documentation/01-architecture.md` for directory layout or architectural decisions.
- `documentation/02-infrastructure.md` for Docker, Nginx, PHP, Redis, MariaDB, Mailpit, or volumes.
- `documentation/03-environment.md` for environment variables.
- `documentation/05-cli-scripts.md` for `bin/` scripts.
- `documentation/06-php-wordpress.md` for Composer, WordPress core/content layout, plugins, migrations, or `bari-cli` commands.
- `documentation/07-theme.md` for Gutenberg-native theme architecture, templates, parts, `theme.json`, hooks, patterns, and blocks.
- `documentation/08-frontend-build.md` for build entries, output paths, or asset conventions.
- `documentation/plugin-development.md` for plugin conventions.
- `documentation/coding-standards.md` for project coding rules.
- `documentation/tech-debt.md` when discovering or resolving known issues.

When documentation and source are currently inconsistent, document both:

- Verified current behavior from source/runtime.
- Intended behavior or cleanup recommendation.

## Security checklist before finishing changes

- No secrets, credentials, salts, private keys, or personal data committed.
- Inputs sanitized and validated.
- Outputs escaped.
- SQL prepared with `$wpdb->prepare()` when dynamic values are used.
- REST routes include `permission_callback`.
- AJAX/admin state changes check nonces and capabilities.
- Public endpoints are intentionally public and documented.
- External HTTP calls use timeouts and safe error handling.
- File paths for uploads/downloads are validated.
- Multisite implications are considered where relevant.


