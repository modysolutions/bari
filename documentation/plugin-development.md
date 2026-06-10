# Plugin Development Guide

_Last reviewed: 2026-06-07_

This is the source of truth for creating new custom plugins in this repository.

## Mandatory conventions

1. Use the `App` root namespace in all plugin and theme PHP code.
   - Examples: `App\Reports`, `App\Search`.
2. Keep all plugin PHP functionality inside the plugin `app/` directory.
3. Do not create a `composer.json` inside plugin directories.
4. Install all PHP dependencies in global Composer (`app/composer.json`) and rely on global autoloading.
5. If a plugin needs frontend behavior, create the JS entry at:
   - `src/plugins/plugin-name/plugin-name.js`
6. Add that entry to `config/webpack/webpack.config.js` so it is compiled with the shared build.

## Recommended plugin skeleton

```text
app/web/plugins/plugin-name/
  plugin-name.php
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

If plugin JS is needed:

```text
src/plugins/plugin-name/plugin-name.js
```

## Bootstrap example

```php
<?php
/**
 * Plugin Name: App Plugin Name
 */

namespace App\PluginName;

if (!defined('ABSPATH')) {
    exit;
}

final class Bootstrap {
    public function init(): void {
        add_action('init', [$this, 'register_hooks']);
    }

    public function register_hooks(): void {
        // Register CPTs, taxonomies, routes, etc.
    }
}

$plugin = new Bootstrap();
$plugin->init();
```

## Frontend Asset Integration

If your plugin requires frontend assets (JavaScript or CSS), you have two main options for integrating them into the centralized webpack build process.

### Option 1: Separate Entry Point (Recommended for heavy plugins)

For plugins with a significant amount of assets or for those that require strict separation, you can create a dedicated entry point.

1.  Create your plugin's asset files in a new directory under `src/plugins/`, for example:
    -   `src/plugins/my-plugin/app.js`
    -   `src/plugins/my-plugin/app.scss`

2.  Add this new entry to `config/webpack/webpack.config.js`. The build process will automatically generate separate `.js`, `.css`, and `.asset.php` files for your plugin in the `app/dist/` directory.

    ```javascript
    // config/webpack/webpack.config.js
    // ...
    entry: {
      theme: path.resolve(process.cwd(), 'src', 'theme', 'app.js'),
      'my-plugin': path.resolve(process.cwd(), 'src', 'plugins', 'my-plugin', 'app.js'),
    },
    // ...
    ```

3.  Enqueue the generated assets from your plugin's PHP code, using the `my-plugin.asset.php` file for dependencies and versioning.

### Option 2: Import into Main Assets (For lightweight plugins)

For plugins with only a small amount of CSS or JavaScript, you can import them directly into the main theme asset files.

1.  Create your plugin's asset files, for example:
    -   `src/plugins/my-plugin/styles.scss`
    -   `src/plugins/my-plugin/script.js`

2.  Import them into the main theme entry points:

    ```javascript
    // In src/theme/app.js
    import '../../plugins/my-plugin/script.js';
    ```

    ```scss
    // In src/theme/app.scss
    @import '../../plugins/my-plugin/styles.scss';
    ```

This approach is simpler as it doesn't require editing the webpack configuration, but it bundles your plugin's assets with the main theme assets. Both options are valid, and the choice depends on the specific needs of your plugin.

## Twig Templating

Timber/Twig is available globally via Composer and can be used for server-side templating in plugins. To maintain a consistent and secure codebase, please follow the guidelines outlined in the [Coding Standards](coding-standards.md#twigtimber).

### Example: Rendering a Twig Template

To render a Twig template from your plugin, you can use the `Timber::render` method. It's recommended to store your plugin's Twig templates in a `views` or `templates` directory within your plugin.

```php
// In your plugin's main file or a relevant class method
use Timber\Timber;

// ...

// Ensure Timber is available
if (class_exists('Timber\Timber')) {
    $context = Timber::context();
    $context['my_plugin_data'] = 'Hello from the plugin!';
    
    // Assuming you have a 'views' directory in your plugin
    $template_path = plugin_dir_path(__FILE__) . 'views/my-template.twig';
    
    Timber::render($template_path, $context);
}
```

Your `my-template.twig` file would then be able to access the data passed in the context:

```twig
{# in your-plugin/views/my-template.twig #}
<div>
    <h1>{{ my_plugin_data }}</h1>
</div>
```
