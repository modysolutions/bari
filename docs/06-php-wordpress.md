# PHP / WordPress Layer

## Composer Setup

PHP dependencies are managed by **Composer** using [WPackagist](https://wpackagist.org) as the plugin/theme repository mirror.

**`app/composer.json`** — the main dependency manifest.

### Repositories

| Repository | URL | Packages |
|---|---|---|
| WPackagist | `https://wpackagist.org` | `wpackagist-plugin/*`, `wpackagist-theme/*` |

A private **Satispress** repository can be added to serve premium plugins (e.g. ACF Pro). Configure `SATISPRESS_URL` and `SATISPRESS_KEY` in `.env` and the credentials are injected by `./bin/install`.

### Installer Paths

Composer installs packages according to `extra.installer-paths`:

```
web/plugins/{$name}/   ← All WordPress plugins
web/themes/{$name}/    ← All WordPress themes
```

### PSR-4 Autoloader

The custom theme's PHP classes are autoloaded via Composer's PSR-4 autoloader:

```json
"autoload": {
    "psr-4": {
        "App\\": "web/themes/theme/app"
    }
}
```

This means all classes in `app/web/themes/theme/app/` under the `App\` namespace are automatically available without manual `require` calls. The autoloader is loaded in `functions.php`:

```php
require_once ABSPATH . '/../vendor/autoload.php';
```

---

## Production Dependencies

| Package | Version | Purpose |
|---|---|---|
| `wpackagist-plugin/wordpress-seo` | `26.8` | Yoast SEO |
| `wpackagist-plugin/ewww-image-optimizer` | `8.3.1` | Image optimisation |
| `wpackagist-plugin/user-role-editor` | `4.64.6` | Role and capability management |
| `wpackagist-plugin/w3-total-cache` | `2.9.1` | Page and object caching |
| `wpackagist-plugin/redirection` | `5.6.1` | 301/302 redirect manager |
| `wpackagist-plugin/smart-phone-field-for-gravity-forms` | `2.2.0` | Phone field for Gravity Forms |
| `wpackagist-plugin/ultimate-addons-for-gutenberg` | `2.19.26` | Extended Gutenberg blocks (Astra ecosystem) |
| `wpackagist-theme/astra` | `4.13.3` | Parent theme (Gutenberg-ready base) |
| `timber/timber` | `^2.0` | Twig templating engine for WordPress |

---

## Development Dependencies

| Package | Purpose |
|---|---|
| `wpackagist-plugin/query-monitor` | Database query and hook profiling |
| `roave/security-advisories` | Blocks packages with known CVEs at install time |
| `laravel/pint` | PHP code style fixer (PSR-12) |

---

## WordPress Core Structure

WordPress core is **isolated** in `app/wp/` rather than at the web root. The custom `app/index.php` and `app/wp-config.php` bootstrap WordPress from there.

Nginx URL rewrites route all core paths to the `wp/` subdirectory:

```nginx
rewrite ^/(wp-admin|wp-includes)/(.*)$ /wp/$1/$2 last;
rewrite ^/(wp-[^/]+\.php|xmlrpc\.php)$ /wp/$1 last;
rewrite ^/wp-admin$ $scheme://$host$uri/ permanent;
```

WordPress core files in `app/wp/` are downloaded and placed by `./bin/version`. The Docker image also downloads WordPress (as part of the official WordPress Docker image), but `./bin/version` extracts the exact version into the correct directory on the host filesystem.

---

## Plugin: `bari` (Stub)

The `app/web/plugins/bari/bari.php` file is a **scaffold stub** — a placeholder for a future `bari-cli` plugin that would provide WP-CLI commands (migration engine, pattern export).

**Current content:**
```php
<?php
/**
 * Plugin Name: Bari
 * Description: Scaffold plugin for new sites.
 */
```

The migration engine, pattern commands, and WP-CLI integration described in the main README are **not yet implemented**. See [`docs/09-known-issues.md`](./09-known-issues.md).

---

## wp-config.php

`app/wp-config.php` bootstraps the WordPress application. The official WordPress Docker image generates this file on first container start based on environment variables. Key path configurations:

- WordPress core: `app/wp/`
- Content directory: `app/web/` (overrides the default `wp-content/`)
- Autoloader: `app/vendor/autoload.php`

