# 🛡️ Barí — WordPress Project Framework

Barí is a self-contained, opinionated WordPress development framework built around Docker. Its goal is to provide *digital sovereignty* — removing dependency on centralized SaaS platforms by giving teams a fully controlled, reproducible local and production-ready WordPress stack. It is designed to be used as a white-label base for multiple client WordPress projects.

---

## 🏗️ Architecture

The project follows a monorepo-style layout with clean separation of concerns across four zones:

```
bari/
├── compose.yml         → Docker service orchestration
├── config/             → Infrastructure config (Nginx, PHP, MySQL, Webpack)
├── bin/                → CLI automation layer (shell scripts)
├── src/                → Frontend source (JS/SCSS)
└── app/                → WordPress application root
    ├── composer.json   → PHP dependency management
    ├── wp/             → WordPress core (isolated from content)
    └── web/            → Public web root (plugins, themes, uploads)
```

---

## 🚀 Prerequisites

Before initializing the development environment, ensure you have the following tools installed on your host system:

- **Docker & Docker Compose** (V2+)
- **pnpm** (fast, disk-space-efficient Node.js package manager)
- **mkcert** (for managing local, trusted SSL certificates automatically)

---

## ⚡ Quick Start

```bash
# 1. Configure environment variables
cp sample.env .env
# Edit .env and set APP_DOMAINS:
# APP_DOMAINS="mysite.local mail.mysite.local"

# 2. Generate local SSL certificates
./bin/certs

# 3. Boot services, install dependencies and WordPress
./bin/install
```

---

## 🐳 Infrastructure Layer

Five Docker services form the stack (`compose.yml`):

| Service       | Image                         | Role                               |
|---------------|-------------------------------|------------------------------------|
| **nginx**     | `nginx:alpine`                | Reverse proxy + SSL termination    |
| **wordpress** | Custom `wordpress:fpm-alpine` | PHP-FPM application server         |
| **wp_db**     | `mariadb:11.4.7`              | Relational database                |
| **redis**     | `redis:alpine`                | Object cache                       |
| **mailpit**   | `axllent/mailpit`             | Local email trap & web UI          |

**Key infrastructure decisions:**

- **WordPress core is isolated** in `app/wp/` (separate from `app/web/`), following a Bedrock-like pattern. Nginx rewrites `wp-admin`, `wp-includes`, and `wp-*.php` paths to `/wp/`.
- **SSL is handled locally** via `mkcert` — certificates are generated and mounted into Nginx read-only.
- The **custom Dockerfile** (`config/Dockerfile`) extends the official WordPress FPM Alpine image, adding: WP-CLI, Composer, Redis extension, Xdebug, GD/ImageMagick, msmtp for mail, and `mariadb-client`.
- **Nginx has a production proxy fallback**: missing uploads are transparently proxied from the production server, so local dev doesn't need the full uploads folder.
- **All WordPress constants** (debug, SMTP, cron, SSL, memory, etc.) are injected via environment variables in `compose.yml`, making configuration fully environment-driven.

---

## 📦 PHP / WordPress Layer

Dependencies are managed via **Composer + WPackagist** — no manual plugin zip downloads needed.

**Production plugins:**
- `wordpress-seo` — Yoast SEO
- `ewww-image-optimizer`
- `user-role-editor`
- `w3-total-cache`
- `redirection`
- `smart-phone-field-for-gravity-forms`
- `ultimate-addons-for-gutenberg`
- `astra` — base theme

**Key PHP library:**
- `timber/timber ^2.0` — Twig templating engine for WordPress

**Dev dependencies:**
- `query-monitor` — profiling
- `roave/security-advisories` — blocks packages with known CVEs
- `laravel/pint` — PHP code style fixer

Premium plugins (e.g. ACF Pro) are served through a self-hosted **Satispress** instance, configured via `SATISPRESS_URL` and `SATISPRESS_KEY` in `.env`.

---

## 🎨 Theme Layer

The custom theme lives in `app/web/themes/theme/` and is built on a **hook-based OOP architecture** under the `App\` namespace. It is a child theme of **Astra**, combining Gutenberg's block editor with Twig templating via Timber.

### Architecture

- **Twig** (via Timber) owns the page shell — layouts, header, footer, partials
- **Gutenberg** owns the content — `{{ post.content }}` in Twig processes all blocks automatically via `do_blocks()`
- **ACF custom blocks** bridge the two: registered in `app/Blocks/`, rendered via `app/Views/blocks/*.twig`
- **Block patterns** capture complete Gutenberg layouts as reusable code in `app/Patterns/`

### Service Classes (`app/Hooks/`)

`functions.php` boots eight service classes:

| Class                  | Responsibility                                                                                          |
|------------------------|---------------------------------------------------------------------------------------------------------|
| `App\Hooks\App`        | Core setup: menus, asset enqueuing (fixed paths), head cleanup, admin tweaks, deferred scripts         |
| `App\Hooks\Acf`        | ACF options pages, Local JSON save/load paths (`app/acf-json/`)                                        |
| `App\Hooks\Blocks`     | Auto-discovers `app/Blocks/*/block.php` and registers ACF blocks with a default Twig render callback   |
| `App\Hooks\Gutenberg`  | Theme supports (`align-wide`, `wp-block-styles`, `editor-styles`), editor asset enqueuing, block categories |
| `App\Hooks\Patterns`   | Auto-discovers `app/Patterns/**/*.php` and registers each as a WordPress block pattern                  |
| `App\Hooks\Security`   | Removes REST API discovery links and oEmbed headers from `wp_head`                                      |
| `App\Hooks\Theme`      | Scaffold: on theme activation, creates a clean "Home" page                                              |
| `App\Hooks\Views`      | Timber/Twig integration: global context (menus, ACF options), custom Twig filters, view locations      |

### Block Pattern System (`app/Patterns/`)

Patterns are PHP files that return an array. They are discovered automatically — no registration code needed.

```
app/Patterns/
├── Sections/
│   └── hero.php       ← Full-width hero (included as example)
├── Content/
└── Exports/           ← Generated by: ./bin/wp pattern export <post-id> <slug>
```

**Export workflow — design in Gutenberg, save to code:**

```bash
# Design a page in the editor, then:
./bin/wp pattern export 42 sections/my-layout

# Scaffold an empty pattern file:
./bin/wp pattern create sections/feature-grid

# List all patterns in the theme:
./bin/wp pattern list
```

### Custom ACF Block System (`app/Blocks/` + `app/Views/blocks/`)

Each custom block lives in its own directory:

```
app/Blocks/
└── hero/
    └── block.php          ← ACF block config (returns array, no render_callback needed)

app/Views/blocks/
└── hero.twig              ← Twig template (receives block, fields, is_preview, post_id)
```

Extend the Twig context per block via filter:

```php
add_filter( 'bari/block/hero/context', function ( $ctx, $block ) {
    $ctx['related'] = Timber::get_posts([ 'post_type' => 'post', 'posts_per_page' => 3 ]);
    return $ctx;
}, 10, 2 );
```

### View Layer (Twig via Timber)

- **`layouts/base.twig`** — page shell with proper ARIA landmarks and block-ready `<main>` (no overflow or max-width that would fight `alignfull`)
- **`layouts/canvas.twig`** — blank canvas layout
- **`templates/`** — `page.twig` (block-driven, no wrapper), `single.twig`, `archive.twig`, `404.twig`, `search.twig`, `author.twig`
- **`partials/`** — `head.twig`, `menu.twig`, `footer.twig`, `pagination.twig`

### `theme.json`

Fully Gutenberg-ready design system:
- `appearanceTools: true` — spacing, padding, border controls in the editor
- Fluid typography scale (XS → Display) with `clamp()` values
- 6-stop spacing preset scale (XS → 2XL) usable in block spacing controls
- Constrained layout: content `1160px`, wide `1440px`
- Element styles for headings, links and buttons wired to design tokens
- Custom colour palette (9 colours) — no custom colours allowed

---

## 🎛️ Frontend Build Layer

- **Entry points:** `src/app.js` (frontend) and `src/editor.js` (block editor)
- **Build tool:** `@wordpress/scripts` (wp-scripts), with a custom `config/webpack/webpack.config.js` extending WP defaults with Babel/React support and image asset handling
- **CSS:** SCSS with a structured `src/scss/` architecture (variables, mixins, normalize, media queries, typography, layout, components)
- **Output:** `app/dist/` — versioned assets with `.asset.php` manifests for automatic cache-busting

```bash
# Development (watch mode)
pnpm start

# Production build
pnpm build
```

---

## 🔧 CLI Automation Layer (`./bin/`)

Every daily task has a shell script proxy, abstracting Docker complexity:

| Script           | What it does                                                                                          |
|------------------|-------------------------------------------------------------------------------------------------------|
| `./bin/install`  | Master provisioner: generates certs → builds Docker → runs Composer → installs WordPress             |
| `./bin/certs`    | Sources `.env`, runs `mkcert` for all domains in `APP_DOMAINS`                                        |
| `./bin/version`  | Downloads a specific WP core version, backs up the old core, replaces `app/wp/` safely               |
| `./bin/wp`       | Proxies any WP-CLI command into the running container via `wp-cli.yml` alias                          |
| `./bin/db`       | Pulls a DB dump from a remote server via `rsync`, imports it, and runs `search-replace` for local URL |
| `./bin/composer` | Proxies Composer into the PHP container                                                               |
| `./bin/build`    | Builds frontend assets across plugins and themes for staging or production                            |
| `./bin/to`       | Opens a shell inside a specific container (e.g. `./bin/to php`, `./bin/to nginx`)                    |
| `./bin/log`      | Formatted log output with INFO / SUCCESS / ERROR levels                                               |
| `./bin/pnpm`     | Proxies pnpm inside the environment                                                                   |

---

## 🗄️ Database Migration Engine (`bari-cli`)

Barí ships with a native WP-CLI plugin — **`bari-cli`** — that keeps database schema changes version-controlled and CI/CD-friendly.

### Plugin structure

```
app/web/plugins/bari-cli/
├── bari-cli.php                   ← Plugin entry point + autoloader
├── migrations/                    ← Default migrations directory
└── src/
    ├── CLI/
    │   └── MigrationCommand.php   ← WP-CLI command handler
    └── Migration/
        ├── AbstractMigration.php  ← Base class all migrations extend
        ├── Migrator.php           ← Core engine (run / rollback / status)
        └── stubs/
            └── migration.stub     ← Template for scaffolded files
```

### Commands

All migration operations run via the `./bin/wp` proxy:

```bash
# Create a new timestamped migration file
./bin/wp migration create "create orders table"

# Apply all pending migrations
./bin/wp migration migrate

# Roll back the last batch
./bin/wp migration rollback

# Show the run status of every migration
./bin/wp migration status
./bin/wp migration status --format=json
```

---

## 🧩 Pattern CLI (`bari-cli`)

The `wp pattern` command group manages Gutenberg block patterns stored in the theme:

```bash
# Scaffold an empty pattern file
./bin/wp pattern create sections/feature-grid

# Export a page's block content as a reusable pattern
./bin/wp pattern export <post-id> sections/my-layout

# Export and overwrite an existing pattern
./bin/wp pattern export <post-id> sections/my-layout --title="My Layout" --overwrite

# List all pattern files in the theme
./bin/wp pattern list
./bin/wp pattern list --format=json
```

### Migration files

Generated files are prefixed with a `YYYYmmddHHiiss` timestamp (e.g. `20260529120000_create_orders_table.php`) and sorted in chronological order. Each file returns an anonymous class extending `AbstractMigration`:

```php
return new class extends AbstractMigration {
    public function up(): void
    {
        $this->createTable( "
            CREATE TABLE {$this->db->prefix}orders (
                id   bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                name varchar(255)        NOT NULL,
                PRIMARY KEY (id)
            ) {$this->db->get_charset_collate()}
        " );
    }

    public function down(): void
    {
        $this->dropTable( $this->db->prefix . 'orders' );
    }
};
```

`AbstractMigration` exposes `$this->db` (wpdb) and helpers: `createTable()`, `dropTable()`, `tableExists()`, `columnExists()`, `statement()`.

### Batch tracking

A `{prefix}migrations` table is created automatically on first run, recording each migration's filename, batch number, and execution timestamp.

### Overriding the migrations directory

By default migrations are stored inside the plugin. To move them elsewhere (e.g. into your theme), define the constant before the plugin loads — typically in `wp-config.php`:

```php
define( 'BARI_MIGRATIONS_DIR', get_template_directory() . '/database/migrations' );
```

---

## ⚙️ Environment Variables

All configuration is driven by `.env`. Copy the sample to get started:

```bash
cp sample.env .env
```

Key variables:

| Variable                                | Description                                         |
|-----------------------------------------|-----------------------------------------------------|
| `DB_NAME`, `DB_USER`, `DB_PASSWORD`     | Database credentials                                |
| `SITE_NAME`, `HOST_NAME`                | Local domain configuration                          |
| `APP_DOMAINS`                           | Space-separated list of domains for SSL certs       |
| `SITE_SLUG`, `CONTAINER_PREFIX`         | Docker container naming prefix                      |
| `WORDPRESS_VERSION`                     | WordPress core version to install                   |
| `WORDPRESS_ENVIRONMENT`                 | Environment type (`local`, `stage`, `production`)   |
| `SATISPRESS_URL`, `SATISPRESS_KEY`      | Private Composer package registry credentials       |
| `WORDPRESS_DEBUG`                       | Enable WP debug mode                                |
| `SSH_HOST_PRODUCTION`, `SSH_HOST_STAGE` | Remote hosts for DB pull and deploy                 |

---

## 📁 Repository Structure

```
bari/
├── app/                          # Monolithic WordPress application
│   ├── composer.json             # Backend dependency management
│   ├── wp/                       # WordPress core (isolated)
│   └── web/                      # Server public root
│       ├── plugins/              # Composer-managed plugins + bari-cli
│       ├── themes/
│       │   └── theme/            # Custom theme
│       │       ├── functions.php
│       │       ├── theme.json
│       │       ├── app/
│       │       │   ├── Hooks/    # OOP WordPress hook classes
│       │       │   └── Views/    # Twig templates (layouts, templates, partials)
│       │       └── assets/
│       └── uploads/
├── bin/                          # CLI automation utilities
├── config/                       # Infrastructure configuration
│   ├── Dockerfile
│   ├── nginx/                    # Nginx config templates
│   ├── php/                      # PHP ini overrides + Xdebug
│   └── webpack/                  # Webpack config
├── src/                          # Frontend source (JS + SCSS)
├── compose.yml                   # Docker service orchestration
└── sample.env                    # Environment variable template
```

---

## ⚠️ Known Issues

- **`bari-cli`** is fully implemented. Activate the plugin via WP admin or `./bin/wp plugin activate bari-cli`, then add your site alias to `wp-cli.yml` so the `./bin/wp` proxy can target the correct container.

---

## 📝 License

This project is distributed under the **GPL-2.0-or-later** license. You are free to use, modify, redistribute, and package it as a white-label foundation for your corporate software products or client implementations.
