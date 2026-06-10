# Theme Architecture

The custom theme lives at `app/web/themes/theme/` and is declared as a **child theme of Astra** (`Template: astra` in `style.css`). It inherits Astra's Gutenberg ecosystem, block library, and CSS framework while fully controlling all PHP logic and HTML output through Timber/Twig.

---

## Rendering Model

```
WordPress request
    │
    ▼
functions.php  →  Hook classes (PHP)  →  WordPress actions/filters
    │
    ▼
Timber::render()  →  Twig template  →  HTML response
                                           │
                                           └── {{ post.content }}
                                                   │
                                                   ▼
                                               do_blocks()  →  Gutenberg block output
```

- **Twig** (via Timber) owns the **page shell** — `<html>`, `<head>`, header, footer, layout wrappers
- **Gutenberg blocks** render inside `{{ post.content }}`, which internally calls `the_content()` → `apply_filters('the_content', ...)` → `do_blocks()`
- ACF fields are available in Twig via `{{ options }}` (global options page) and `{{ post.custom.field_name }}`

---

## Directory Structure

```
theme/
├── style.css                      # Theme header (declares child of Astra)
├── index.php                      # WordPress fallback template
├── template-canvas.php            # Blank canvas PHP template
├── functions.php                  # Bootstrap: loads autoloader, defines constants, boots hooks
├── theme.json                     # Gutenberg design system configuration
├── phpcs.xml.dist                 # PHP_CodeSniffer configuration
├── phpstan.neon                   # PHPStan static analysis configuration
├── phpunit.xml                    # PHPUnit test configuration
├── screenshot.png                 # Theme preview image
├── assets/
│   ├── fonts/                     # Custom font files (empty, use CSS @font-face)
│   ├── images/                    # Static theme images
│   └── styles/
│       └── main.css               # Static theme CSS (not compiled by webpack; all custom styles live in src/ at project root)
└── app/
    ├── Hooks/                     # WordPress hook classes
    │   ├── App.php
    │   ├── Gutenberg.php
    │   ├── Security.php
    │   ├── Theme.php
    │   └── Views.php
    └── Views/                     # Twig templates
        ├── admin/
        │   └── plugin-notice.php  # WP admin notice (not a Twig file)
        ├── layouts/
        │   ├── base.twig          # Master layout
        │   └── canvas.twig        # Blank canvas layout
        ├── partials/
        │   ├── head.twig          # <head> element
        │   ├── menu.twig          # Navigation menu
        │   ├── footer.twig        # Footer
        │   └── pagination.twig    # Pagination links
        ├── scripts/
        │   └── lazyload.twig      # Lazy-load script snippet
        └── templates/
            ├── index.twig         # Blog index
            ├── page.twig          # Static page
            ├── single.twig        # Single post
            ├── archive.twig       # Post archive
            ├── 404.twig           # 404 error page
            ├── search.twig        # Search results
            ├── author.twig        # Author archive
            ├── canvas.twig        # Canvas template (no header/footer)
            └── single-password.twig  # Password-protected post
```

---

## Hook Classes

All WordPress integration is handled through OOP hook classes. Each class follows a consistent interface: a public `init()` method that registers all actions and filters.

### `App\Hooks\App` — Core Application Setup

Registered in `functions.php`. Handles:

- **Navigation menus** — Registers `header_menu`, `footer_top_menu`, `footer_bottom_menu`
- **Theme supports** — `post-thumbnails`, `title-tag`
- **Head cleanup** — Removes WordPress feed links, generator tag, oEmbed discovery links, emoji scripts, resource hints
- **Asset enqueuing** — Registers and enqueues compiled `dist/app.js` and `dist/app.css` with versioning from the `.asset.php` manifest; localises `AppSettings` JS object with `ajaxUrl`, `nonce`, `siteUrl`
- **Script deferring** — The `bari-app` script tag is output with `defer` attribute
- **Template redirect** — Returns 404 for tag, date, author, and attachment archives (category and `news-category` taxonomy archives are allowed through)
- **Content filter** — Removes empty `<p>` wrappers around images
- **ACF WYSIWYG toolbar** — Adds a "Simple Text" toolbar with bold, italic, underline
- **Admin head** — Injects CSS to hide Yoast SEO upsell elements
- **Admin footer** — Removes wp-embed script
- **Admin menu** — Removes Comments menu, removes "Howdy" footer

### `App\Hooks\Gutenberg` — Block Editor

Registered in `functions.php`. Handles:

- **Theme supports** — Declares `align-wide`, `wp-block-styles`, `responsive-embeds`, `editor-styles`
- **Editor stylesheet** — Registers `dist/editor.css` as the editor stylesheet (mirrors frontend styles)
- **Editor assets** — Enqueues `dist/editor.js` and `dist/editor.css` only inside the block editor (reads the `.asset.php` manifest for versioning and dependencies)
- **Block types** — All blocks allowed by default; projects can restrict via the `bari/allowed_blocks` filter:
  ```php
  add_filter('bari/allowed_blocks', function($allowed, $context) {
      return ['core/paragraph', 'core/heading', 'acf/hero'];
  }, 10, 2);
  ```
- **Block categories** — Prepends three custom categories to the block picker:
  - `bari-sections` — Sections
  - `bari-content` — Content
  - `bari-media` — Media

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

### `App\Hooks\Views` — Timber/Twig Integration

Registered in `functions.php`. Handles all Timber/Twig wiring:

- **Timber context** (`timber/context`) — Merges global context:
  - `options` — ACF global options page fields (if ACF active)
  - `header_menu`, `footer_top_menu`, `footer_bottom_menu` — Timber menu objects
- **Twig environment** (`timber/twig`) — Adds custom Twig filters:
  - `admin_url` — Wraps `admin_url()` for use in templates
  - `print_id` — Outputs ` id="value" ` if value is truthy
- **Timber locations** (`timber/locations`) — Registers `@theme` namespace pointing to `app/Views/`

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

`theme.json` controls what editors can customise in the Gutenberg block editor. Key settings:

- `appearanceTools: true` — Enables spacing, padding, border controls in the editor
- **Layout**: content max-width `1160px`, wide max-width `1440px`
- **Colour palette**: 9 preset colours (primary, secondary, accent, white, grey-10, grey-20, grey-80, grey-90, black). Custom colours disabled.
- **Typography**: `Sansation` font family, 8 fluid font sizes (XS through Display using `clamp()`). Custom font sizes disabled.
- **Spacing**: 6-stop preset scale (XS `0.5rem` → 2XL fluid). Margin and padding controls enabled.
- **Element styles**: Links, headings (h1–h6) and buttons are wired to design tokens.

---

## Twig View Hierarchy

Templates follow **Twig inheritance** via `{% extends %}` and `{% block %}`.

```
layouts/base.twig          ← Master layout (HTML shell)
    ├── templates/page.twig
    ├── templates/single.twig
    ├── templates/index.twig
    ├── templates/archive.twig
    ├── templates/404.twig
    ├── templates/search.twig
    └── templates/author.twig

layouts/canvas.twig        ← Blank canvas (no header/footer)
    └── templates/canvas.twig
```

Partials (`@theme/partials/*.twig`) are included with `{% include %}` and receive explicit context via `with {}`.

---

## Adding Templates for New Post Types

1. Create a PHP template file (e.g. `app/web/themes/theme/single-product.php`) that calls `Timber::render()`
2. Create the Twig template at `app/Views/templates/single-product.twig`
3. Extend `@theme/layouts/base.twig` and fill the `content` block

