# Coding Standards

_Last reviewed: 2026-06-07_

These standards are intended for all custom code in `app/web/themes/theme`, `app/web/plugins/omitsis-*`, and `app/web/plugins/bari-*`.

## Core principles

1. **Do not edit WordPress core, vendor code, or third-party plugins directly.** Put project behavior in custom plugins or the project FSE theme.
2. **Prefer feature plugins for business logic.** The theme should handle presentation; custom plugins should own CPTs, taxonomies, REST APIs, data processing, access rules, and integrations.
3. **Make code multisite-aware.** This stack is installed as a subdomain multisite network.
4. **Avoid hardcoded URLs and filesystem paths.** Use WordPress APIs such as `plugins_url()`, `plugin_dir_path()`, `plugin_dir_url()`, `get_stylesheet_directory_uri()`, `content_url()`, `WP_CONTENT_DIR`, and `WP_CONTENT_URL`.
5. **Use the `App` root namespace for project code.** New plugin/theme PHP should be scoped under `App\...` to avoid collisions across many active plugins.
6. **Document operational side effects.** Any plugin that creates tables, rewrites URLs, modifies users, sends external API calls, or changes access logic must explain that behavior in code comments and docs.

## PHP standards

### Formatting

- Follow WordPress PHP Coding Standards unless a file is already consistently using a different local style.
- Keep indentation consistent within the file being edited.
- Keep functions small and focused.
- Use strict comparisons where possible.
- Prefer early returns over deeply nested conditionals.

### Namespacing and prefixes

The repository currently has many generic global functions such as `register_acf_fields()`, `post_updated_messages()`, `template_include()`, and `init()`. New code should avoid adding more generic globals.

Preferred options:

- Namespaced functions/classes under `App`, for example `App\Reports\register_routes()`.
- Static service classes for cohesive feature areas.
- If a global function is unavoidable, prefix it with the plugin slug, for example `bari_reports_register_routes()`.

### WordPress hooks

- Register hooks near plugin/theme bootstrap so behavior is easy to find.
- Hook callbacks should be named after the behavior, not the hook itself.
- Avoid anonymous callbacks for important behavior that needs removal/testing.
- Always specify accepted arguments when a hook requires more than one argument.

Example convention:

```php
add_action( 'init', 'bari_events_register_post_type' );
add_filter( 'template_include', 'bari_events_template_include' );
```

### Sanitization, validation, and escaping

- Sanitize all input from `$_GET`, `$_POST`, request bodies, cookies, headers, and REST params.
- Validate data before storing or sending to external systems.
- Escape output at the latest possible point.
- Use the appropriate escaping function:
  - `esc_html()` for text nodes.
  - `esc_attr()` for attributes.
  - `esc_url()` for URLs.
  - `wp_kses_post()` for trusted rich HTML.
- Use `wp_unslash()` before sanitizing WordPress request data.

### Database access

- Use `$wpdb->prepare()` for any query with dynamic data.
- Use `$wpdb->prefix` for custom tables.
- Prefer reversible migrations through `bari-wp-cli` for schema changes.
- Use `dbDelta()` for table creation/alteration when possible.
- Never build SQL from unsanitized request data.
- Document every custom table: name, owner plugin, creation path, read/write paths, and retention expectations.

### Capabilities, nonces, and permissions

For admin pages, AJAX actions, and REST endpoints:

- Check user capabilities explicitly.
- Validate nonces for state-changing AJAX/admin requests.
- Provide `permission_callback` for every REST route.
- Public REST routes and `wp_ajax_nopriv_*` actions must be documented and reviewed for abuse, rate limiting, and data exposure.

### External APIs

- Keep API keys and tokens in environment variables or WordPress options that are not exported to Git.
- Use WordPress HTTP APIs (`wp_remote_get()`, `wp_remote_post()`) unless a library is required.
- Set timeouts.
- Handle failures with useful logs and safe fallbacks.
- Never expose raw upstream errors to users.

## Custom plugin standards

### Recommended plugin structure

For new custom plugins, use this project layout:

```text
plugin-slug/
  plugin-slug.php
  app/
    Bootstrap.php
    Hooks/
    Admin/
    Frontend/
    Rest/
  templates/
  template-parts/
  src/
    plugins/
      plugin-slug/
        plugin-slug.js
  assets/
  package.json       # only when plugin-specific asset build is required
```

Non-negotiable project rules for new plugins:

- Do not add `composer.json` inside plugin directories.
- Install PHP dependencies in the global Composer project (`app/composer.json`) and load them from global autoloading.
- Put plugin PHP functionality under the plugin `app/` directory.
- Use the `Bari` namespace in plugin code (for example `Bari\Reports`, `Bari\Search`).

### Plugin headers

Every custom plugin main file should include accurate headers:

- `Plugin Name`
- `Description`
- `Version`
- `Author`
- `Author URI`
- `Text Domain`
- `Requires PHP`
- `Requires at least`

Keep versions meaningful and update them when behavior/assets change.

### Bootstrap

- Define plugin constants for path, URL, version, and text domain.
- Include files once from the main file or a bootstrap file.
- Register activation/deactivation hooks only in the main plugin file.
- On activation, create required tables and flush rewrites only when the plugin owns rewrites.
- On deactivation, remove scheduled events and flush rewrites if needed.
- Avoid doing expensive work on every request.

### CPTs and taxonomies

- Register CPTs/taxonomies on `init`.
- Keep slugs stable after launch.
- Document rewrite slugs and permalink behavior.
- If a plugin supplies templates through `template_include`, document the template hierarchy and fallback behavior.
- If a CPT should be excluded from SEO indexing/sitemaps, document the Yoast filters used.

### ACF fields

- Register field groups in PHP on `acf/init` or `acf/include_fields`.
- Use stable field keys (`field_...`) and group keys (`group_...`).
- Keep field names unique enough to avoid cross-plugin ambiguity.
- Group options by feature/plugin.
- Document option pages and option keys.
- Avoid relying only on ACF UI exports that are not committed.

### Assets

- Enqueue assets with WordPress APIs, not hardcoded `/wp-content/...` paths.
- Use the custom content URL (`/web`) correctly.
- Use dependency arrays and versions from generated `*.asset.php` files where available.
- Prefer `filemtime()` versions in local/dev if no build metadata exists.
- Only enqueue assets on screens/pages where they are needed.
- Build outputs should be committed only when the deployment model requires committed assets.
- If a plugin needs frontend JS/CSS behavior, create the source entry at `src/plugins/plugin-slug/plugin-slug.js`.
- Register each plugin entry in `config/webpack/webpack.config.js` so it is included in the shared build.

### Templates

- Keep plugin templates scoped to their plugin.
- Use `locate_template()` only when intentionally allowing theme overrides.
- Escape template output.
- Do not perform expensive queries directly in templates; prepare data before rendering.

### AJAX and REST

- Prefer REST for new structured APIs.
- Use AJAX only when integrating with legacy WordPress/admin patterns.
- Name endpoints/actions with the plugin prefix.
- Return `wp_send_json_success()` / `wp_send_json_error()` for AJAX.
- Return `WP_REST_Response` or arrays from REST callbacks.
- Validate all request parameters.

## Theme standards

The FSE theme should primarily own:

- Shared presentation.
- Block templates and template parts.
- Theme-level editor configuration and template locking.
- Design tokens in `theme.json`.
- Style variations and block style presets.
- Presentation-only patterns.

Business logic should stay in custom plugins.

Theme PHP should:

- Use `APP_THEME_DIR` and `APP_THEME_URI` for paths/URLs.
- Avoid hardcoded root constants unless defined.
- Keep theme hooks focused on editor/theme setup, asset loading, and presentation concerns.
- Avoid business queries, access decisions, and integration logic.
- New theme classes/functions should also use the `App` root namespace.

Theme templates/patterns should:

- Use block markup, template parts, patterns, and `theme.json` tokens.
- Keep layout structural and editor-friendly.
- Avoid direct DB/API access and business logic.
- Use Spectra only for presentation/layout primitives.

Twig/Timber is available globally through Composer for plugins that need server-side templating. Plugin Twig should keep logic minimal, escape output, and avoid direct data access in templates.

## ACF standards

Use ACF when content editors need structured fields without a custom React editing experience.

Good use cases:

- Options pages.
- CPT metadata.
- Relationship fields.
- Flexible modules that render server-side.
- Plugin-owned dynamic blocks that need structured fields without a custom React editor.

Avoid ACF for:

- Highly interactive editor UI.
- Complex nested client-side state.
- Blocks that need rich transforms, variations, or client-side editing behavior.

## Native Gutenberg standards

Use native Gutenberg blocks when:

- The editor experience is complex or highly interactive.
- The block needs InspectorControls, block supports, variations, transforms, or React-based editing.
- The block should be portable outside ACF.
- The front end can be static or rendered from `render.php`.

Native blocks should:

- Have a `block.json` source of truth.
- Use `@wordpress/scripts` where practical.
- Register with `register_block_type()`.
- Use generated asset metadata.
- Provide translation strings through WordPress i18n APIs.
- Include editor and frontend styles separately when needed.

## JavaScript and CSS standards

- Prefer modern JavaScript modules for new code.
- Keep frontend scripts scoped by plugin/theme handle.
- Avoid global variables except documented settings objects localized/enqueued by WordPress.
- Use `@wordpress/*` packages for Gutenberg/React admin code.
- Keep CSS class names scoped to plugin/theme components.
- Prefer design tokens/CSS custom properties for colors, spacing, typography, and responsive values.
- Avoid inline styles generated from PHP unless values are sanitized and the use case is documented.

## Security checklist for review

Before merging a change, verify:

- [ ] No secrets were committed.
- [ ] Inputs are sanitized and validated.
- [ ] Outputs are escaped.
- [ ] SQL is prepared.
- [ ] REST routes have permission callbacks.
- [ ] AJAX actions validate nonces and capabilities where appropriate.
- [ ] Public endpoints are intentionally public and documented.
- [ ] External API calls have timeouts and failure handling.
- [ ] File uploads/downloads validate paths and permissions.
- [ ] Multisite behavior is tested where relevant.

## Testing checklist

For plugin/theme changes:

```bash
./bin/wp plugin list
./bin/wp theme list
./bin/wp site list
./bin/wp cache flush
```

Recommended additional checks:

- PHP syntax check for changed files.
- Composer install/update only when manifests change.
- Plugin-specific `npm run build` where assets changed.
- Smoke test affected pages on each relevant multisite domain.
- Check `app/logs/debug.log` after testing.
- Verify Query Monitor does not show new fatal errors.

## Documentation checklist

When adding or changing a custom plugin/module/block:

- [ ] Update [`plugin-development.md`](plugin-development.md) if responsibilities, endpoints, CPTs, shortcodes, or statuses change.
- [ ] Update [`07-theme.md`](07-theme.md) for block conventions or theme architecture changes.
- [ ] Update [`tech-debt.md`](tech-debt.md) when fixing or discovering known issues.
- [ ] Document migrations and rollback steps.
- [ ] Document new environment variables.

