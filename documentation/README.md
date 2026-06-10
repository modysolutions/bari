# Barí WordPress Stack Documentation

This directory contains all the documentation for the Barí WordPress project framework. Barí is a self-contained, opinionated WordPress development framework built around Docker. It provides a fully controlled, reproducible local and production-ready WordPress stack designed for teams that want to build WordPress sites without depending on centralised SaaS infrastructure. It is intended as a white-label base for corporate and client WordPress projects.

This documentation is intended to be a living document, updated recurrently by the development team and any automated agents working on the project. This ensures that all contributors have the same context when working on this project.

## Documentation Index

| File | Purpose |
|---|---|
| [Architecture](01-architecture.md) | Full directory map and architectural decisions. |
| [Infrastructure](02-infrastructure.md) | Docker services, Dockerfile, Nginx, and PHP configuration. |
| [Environment](03-environment.md) | Complete `.env` variable reference. |
| [Quick Start](04-quick-start.md) | A step-by-step setup guide for getting started with the project. |
| [CLI Scripts](05-cli-scripts.md) | Documentation for all the `bin/` scripts. |
| [PHP & WordPress](06-php-wordpress.md) | Composer setup, plugin dependencies, core isolation, and the `bari-cli` plugin. |
| [Theme Development](07-theme.md) | The theme architecture, hook classes, and Twig templates. |
| [Frontend Build](08-frontend-build.md) | Webpack, SCSS structure, and design tokens. |
| [Coding Standards](coding-standards.md) | Standards for PHP, plugins, assets, REST/AJAX, database changes, security, and reviews. |
| [Plugin Development](plugin-development.md) | Rules and a skeleton for creating new custom plugins. |
| [Known Issues](09-known-issues.md) | A log of resolved issues and other known issues. |
| [Tech Debt](tech-debt.md) | Concrete bugs, known issues, modernization work, and update recommendations. |


## High-level Stack Summary

- **Runtime:** Nginx + PHP-FPM WordPress image + MariaDB + Redis + Mailpit.
- **WordPress layout:** Core lives in `app/wp`; custom content lives in `app/web` and is wired via `WP_CONTENT_DIR`/`WP_CONTENT_URL` in `app/wp-config.php`.
- **Dependency management:** Root PHP dependencies are installed from `app/composer.json` into `app/vendor`; WordPress plugins/themes are installed into `app/web/plugins` and `app/web/themes` via Composer installer paths.
- **Theme:** The default theme is a starter block theme using `theme.json`, block templates, and template parts.
- **Twig/Timber:** Installed globally through the root Composer project for plugins that need server-side templates; it is not the theme layout system.

## Source-of-truth files

Use these repository files as the primary source when updating this documentation:

- `compose.yml`
- `sample.env`
- `wp-cli.yml`
- `app/composer.json`
- `app/wp-config.php`
- `config/Dockerfile`
- `config/nginx/*.template`
- `config/php/*.ini`
- `bin/*`
- `app/web/themes/theme/**`
- `app/web/plugins/bari-cli/*`

## Documentation Maintenance Rules

- Update this documentation whenever a service, environment variable, build tool, custom plugin, block pattern, or workflow changes.
- Document verified behavior separately from intended behavior.
- Do not document secrets from `.env`; refer to variable names only.

