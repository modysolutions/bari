# Theme Architecture

The custom theme lives at `app/web/themes/theme/` and is a **Gutenberg-native theme**. Its primary rendering layer is WordPress block templates, template parts, `theme.json`, style variations, and block markup.

The current `style.css` does **not** declare `Template: astra`; do not treat this theme as Astra-derived. Timber/Twig remains installed through Composer for compatibility and for plugins that need server-rendered views, but it is not the theme's primary page-rendering model.

---

## Rendering Model

```
WordPress request
    │
    ▼
functions.php  →  Hook classes (PHP)  →  Theme support, assets, filters
    │
    ▼
WordPress template resolver
    │
    ▼
templates/*.html  →  parts/*.html  →  blocks / patterns / post content
    │                                      │
    └──────────────────────────────────────┴── do_blocks() → HTML response
```

- **Block templates** in `templates/*.html` own the page shell for standard frontend requests.
- **Template parts** in `parts/*.html` own reusable layout regions such as header, footer, and sidebar.
- **`theme.json`** defines editor controls, presets, global styles, block styles, layout widths, typography, and color foundations.
- **Gutenberg blocks** are rendered by WordPress through `do_blocks()` as part of the normal block-template flow.
- **Timber/Twig** should be used only when existing compatibility code requires it or when a plugin needs explicit server-side rendering (SSR) templates.

---

## Directory Structure

```
theme/
├── style.css                      # Theme header; no Astra Template declaration
├── index.php                      # WordPress fallback template
├── template-canvas.php            # Blank canvas PHP template
├── functions.php                  # Bootstrap: loads autoloader, defines constants, boots hooks
├── theme.json                     # Gutenberg design system configuration
├── phpcs.xml.dist                 # PHP_CodeSniffer configuration
├── phpstan.neon                   # PHPStan static analysis configuration
├── phpunit.xml                    # PHPUnit test configuration
├── screenshot.png                 # Theme preview image
├── templates/                     # Gutenberg block templates
│   ├── 404.html
│   ├── archive.html
│   ├── home.html
│   ├── index.html
│   ├── page.html
│   ├── page-no-title.html
│   ├── search.html
│   └── single.html
├── parts/                         # Gutenberg block template parts
│   ├── footer.html
│   ├── header.html
│   └── sidebar.html
├── styles/                        # Global style variations and grouped style JSON files
│   ├── 01-ei.json
│   ├── 02-ti.json
│   ├── 03-ci.json
│   ├── blocks/
│   ├── sections/
│   └── typography/
├── assets/
│   ├── fonts/                     # Custom font files referenced by theme.json
│   ├── images/                    # Static theme images
│   └── css/                       # Static editor/frontend CSS when present
└── app/
    ├── Hooks/                     # WordPress hook classes
    │   ├── App.php
    │   ├── Gutenberg.php
    │   ├── Security.php
    │   ├── Theme.php
    │   └── Views.php
    └── Views/                     # Timber/Twig compatibility helpers, not primary rendering
```

---

## Hook Classes

All WordPress integration is handled through OOP hook classes. Each class follows a consistent interface: a public `init()` method that registers all actions and filters.

### `App\Hooks\App` — Core Application Setup

Registered in `functions.php`. Handles:

- **Navigation menus** — Registers `menu`
- **Theme supports** — Complementary setup is handled mostly in `App\Hooks\Gutenberg`
- **Head cleanup** — Removes WordPress feed links, generator tag, oEmbed discovery links, emoji scripts, resource hints
- **Asset enqueuing** — Registers and enqueues compiled `dist/theme.js` and `dist/theme.css` with versioning/dependencies from `theme.asset.php` when present
- **Script behavior** — Adds a defer filter for the legacy `app` handle; verify handles before changing asset code
- **Template redirect** — Returns 404 for tag, date, author, and attachment archives (category and `news-category` taxonomy archives are allowed through)
- **Content filter** — Removes empty `<p>` wrappers around images
- **ACF WYSIWYG toolbar** — Adds a "Simple Text" toolbar with bold, italic, underline
- **Admin head** — Injects CSS to hide Yoast SEO upsell elements
- **Admin footer** — Removes wp-embed script
- **Admin menu** — Removes Comments menu, removes "Howdy" footer

### `App\Hooks\Gutenberg` — Block Editor

Registered in `functions.php`. Handles:

- **Theme supports** — Declares `post-thumbnails`, `title-tag`, `custom-logo`, `block-templates`, `block-template-parts`, `editor-styles`, `wp-block-styles`, `responsive-embeds`, and `appearance-tools`
- **Editor stylesheet** — Calls `add_editor_style('assets/css/editor-style.css')`; verify the file exists before changing this path
- **Core pattern cleanup** — Removes core block patterns with `remove_theme_support('core-block-patterns')` so editor suggestions stay focused on project-owned patterns

### `App\Hooks\Security` — Security Hardening

Registered in `functions.php`. Removes:

- `rest_output_link_wp_head` — REST API discovery link from `<head>`
- `wp_oembed_add_discovery_links` — oEmbed discovery links from `<head>`
- `rest_output_link_header` — REST API link from HTTP response header

### `App\Hooks\Theme` — Theme Activation Scaffold

Registered in `functions.php`. On theme activation (`after_switch_theme`):

1. Checks if scaffold has already run (option `scaffold_defaultPosts`)
2. Deletes default WordPress sample content (posts 1, 2, 3 and comment 1)
3. Creates a "Home" page with the `home.php` template
4. Sets it as the static front page
5. Adds a placeholder Yoast meta description
6. Marks the scaffold as complete

### `App\Hooks\Views` — Timber/Twig compatibility

Registered in `functions.php`. This class wires Timber/Twig support if Timber is available. Treat it as compatibility/support code, not as the theme page renderer.

- **Timber context** (`timber/context`) — Merges global context:
  - `options` — ACF global options page fields (if ACF active)
  - `header_menu`, `footer_top_menu`, `footer_bottom_menu` — Timber menu objects
- **Twig environment** (`timber/twig`) — Adds custom Twig filters:
  - `admin_url` — Wraps `admin_url()` for use in templates
  - `print_id` — Outputs ` id="value" ` if value is truthy
- **Timber locations** (`timber/locations`) — Registers `@theme` namespace pointing to `app/Views/`

Use Timber/Twig for plugin SSR templates only when a feature benefits from server-side templating. Keep Twig templates thin: prepare data in PHP, escape output in templates, and avoid direct database/API access in Twig.

---

## `functions.php` — Bootstrap

```php
namespace App;

// Autoloader (loaded from Composer vendor directory)
require_once ABSPATH . '/../vendor/autoload.php';

// Theme constants
define('APP_THEME_DIR', __DIR__);           // Absolute filesystem path to theme root
define('APP_THEME_URI', get_template_directory_uri());  // URL to theme root
define('APP_THEME_DOMAIN', 'theme');        // Text domain for translations

// Boot hook classes
$gutenberg = new Gutenberg(); $gutenberg->init();
$mody      = new App();       $mody->init();
$security  = new Security();  $security->init();
$theme     = new Theme();     $theme->init();
$views     = new Views();     $views->init();
```


---

## `theme.json` — Design System

`theme.json` is the theme's design-system source of truth. Current source settings include:

- `appearanceTools: false` while `App\Hooks\Gutenberg` also declares the `appearance-tools` theme support; verify both if changing editor controls.
- **Layout**: content size `1160`, wide size `1220`.
- **Colour palette**: controlled palette with `primary`, `secondary`, `tertiary`, and accent colors. Custom colors and default palettes are disabled.
- **Typography**: `Bitter` font family loaded from `assets/fonts/bitter/*.woff2`; custom and default font sizes are disabled in favor of project presets.
- **Spacing**: unit choices are declared, while margin/padding controls are currently disabled in `theme.json`.
- **Block styles and variations**: many core blocks define section color/style variations directly in `theme.json`.

---

## Block template hierarchy

Use WordPress block templates and template parts for theme rendering. Create or edit HTML templates under `templates/` instead of introducing Twig page templates.

```
templates/page.html      ← Page shell
templates/single.html    ← Single post shell
templates/archive.html   ← Archive shell
templates/search.html    ← Search shell
templates/404.html       ← 404 shell
parts/header.html        ← Header template part
parts/footer.html        ← Footer template part
parts/sidebar.html       ← Sidebar template part
```

Use WordPress block comments such as `<!-- wp:template-part {"slug":"header"} /-->`, core blocks, block patterns, and approved plugin blocks. Prefer design tokens from `theme.json` over hardcoded style values.

---

## Adding templates for new post types

Business-owned CPTs should be registered in custom plugins. When a CPT needs theme presentation:

1. Prefer a block template such as `templates/single-product.html` or `templates/archive-product.html` when WordPress supports the target template type.
2. Compose the layout with core/Spectra blocks, template parts, and pattern markup.
3. Keep data access, permissions, REST endpoints, and business rules in the owning plugin.
4. Use Timber/Twig only from the plugin when the CPT requires plugin-owned SSR views that cannot be represented cleanly as block templates.

