# Known Issues

This document catalogues all confirmed bugs, incomplete features, and structural problems in the current codebase. Each entry includes the affected file, the nature of the problem, and the recommended fix.

---

## 🔴 Critical — Will Break at Runtime

### 1. `functions.php` imports `App\Hooks\Acf` which does not exist

**File:** `app/web/themes/theme/functions.php`

```php
use App\Hooks\Acf;  // ← Acf.php does not exist on disk
```

The Composer PSR-4 autoloader will attempt to load `app/Hooks/Acf.php` when `functions.php` is parsed. Since the file doesn't exist, this causes a **PHP Fatal Error** that prevents the theme from loading entirely.

**Fix:** Create `app/web/themes/theme/app/Hooks/Acf.php` with an `Acf` class implementing `init()`. Minimum viable stub:
```php
<?php
namespace App\Hooks;
class Acf {
    public function init(): void {}
}
```

---

### 2. `single.twig` includes non-existent partial templates

**File:** `app/web/themes/theme/app/Views/templates/single.twig`

```twig
{% include 'partials/comment.twig' with { comment: cmt } %}
{% include 'partials/comment-form.twig' %}
```

Neither `partials/comment.twig` nor `partials/comment-form.twig` exist in `app/Views/partials/`. Timber will throw a `Twig\Error\LoaderError` for any single post that has comments or an open comment status.

**Fix:** Create both partial templates, or guard the includes:
```twig
{% if 'partials/comment.twig' is defined %}
    {% include 'partials/comment.twig' %}
{% endif %}
```

---

## 🟠 High — Significant Functionality Missing or Broken

### 3. `bari-cli` plugin is a non-functional stub

**File:** `app/web/plugins/bari/bari.php`

The README and documentation describe a full `bari-cli` plugin with:
- WP-CLI `migration` commands (`create`, `migrate`, `rollback`, `status`)
- WP-CLI `pattern` commands (`create`, `export`, `list`)
- A `{prefix}bari_migrations` database tracking table
- An `AbstractMigration` base class
- A `Migrator` engine class

**None of this exists.** The file contains only a 5-line plugin header.

**Fix:** Implement the plugin as described in `docs/07-theme.md` and the project README.

---

### 4. `bin/to` script does not exist

**Reference in README:** `./bin/to` — Opens a shell inside a specific container

The script is documented but not present on disk. Running `./bin/to php` will result in "command not found."

**Fix:** Create `bin/to` as a generic service-to-container mapper:
```bash
#!/bin/bash
source .env
case "${1:-php}" in
    php|app|wordpress) CONTAINER="${CONTAINER_PREFIX}app" ;;
    nginx|server)      CONTAINER="${CONTAINER_PREFIX}server" ;;
    db|mysql)          CONTAINER="${CONTAINER_PREFIX}db" ;;
    redis)             CONTAINER="${CONTAINER_PREFIX}redis" ;;
    mail|mailpit)      CONTAINER="${CONTAINER_PREFIX}mail" ;;
esac
docker exec -it "${CONTAINER}" "${2:-sh}"
```

---

### 5. `bin/pnpm` uses wrong working directory

**File:** `bin/pnpm`

```bash
$PNPM -C "${ROOT_DIR}/app/wp-content" "$@"
```

The path `app/wp-content` does not exist. The correct content directory is `app/web/`. This means `./bin/pnpm` will always fail with "no such file or directory."

**Fix:** Change to `$PNPM -C "${ROOT_DIR}" "$@"` to run pnpm from the project root (where `package.json` lives), or `$PNPM -C "${ROOT_DIR}/app/web" "$@"`.

---

### 6. `bin/pnpm` empty-check is broken (missing `$` and `-z`)

**File:** `bin/pnpm`

```bash
if [ PNPM = "" ]; then   # ← This is a string comparison of literals "PNPM" and ""
```

This should be:
```bash
if [ -z "$PNPM" ]; then
```

As written, the condition is always false (the string `PNPM` never equals `""`), so the missing-pnpm error is never shown.

---

## 🟡 Medium — Incorrect Behaviour or Poor Practice

### 7. `bin/db` has hardcoded project-specific database aliases

**File:** `bin/db`

```bash
case "$DB" in
    ei|ti|ci|cti) DB_NAME="${DB}_db" ;;
    *) ./bin/log "ERROR" "Parameters are ei|ti|ci|cti"; exit 1 ;;
esac
```

The aliases `ei`, `ti`, `ci`, `cti` are from a specific origin project and are meaningless in the framework context. Any project using this script must use one of these codes or the command fails entirely.

**Fix:** Remove the `case` block and accept a free-form database name:
```bash
DB_NAME="${DB}_db"
```
Or make the naming convention configurable via an env variable.

---

### 8. `functions.php` checks Timber with `!\Timber::class`

**File:** `app/web/themes/theme/functions.php`

```php
if (!is_plugin_active('advanced-custom-fields-pro/acf.php') || !\Timber::class) {
```

`\Timber::class` is a string (`"Timber\Timber"`) — it is always truthy. `!\Timber::class` is therefore always `false`, meaning the Timber-missing admin notice will **never** be shown even when Timber is not loaded.

The correct check is:
```php
! class_exists('Timber\Timber')
```

---

### 9. `single.twig` uses inline `<img>` without lazy loading or responsive attributes

**File:** `app/web/themes/theme/app/Views/templates/single.twig`

```twig
<img src="{{ post.thumbnail.src|resize(1200, 300) }}" />
```

No `alt`, `loading`, `width`, or `height` attributes. This causes an accessibility violation and removes browser lazy-loading hints.

**Fix:**
```twig
<img
    src="{{ post.thumbnail.src|resize(1200, 300) }}"
    alt="{{ post.thumbnail.alt }}"
    loading="lazy"
    width="1200"
    height="300"
/>
```

---

### 10. `bin/pnpm` references non-existent `.env.example`

**File:** `bin/pnpm`

```bash
./bin/log "ERROR" "No .env file found. Please create one based on .env.example."
```

The correct template file is `sample.env`, not `.env.example`.

---

### 11. `Theme.php` scaffold sets `page_template` to `home.php`

**File:** `app/web/themes/theme/app/Hooks/Theme.php`

```php
'page_template' => 'home.php'
```

`home.php` is not a registered page template in the theme. WordPress will silently ignore this and fall back to the default template. The Home page will render using the standard `page.php` / Timber template resolution.

---

## 🔵 Low — Cosmetic or Minor Issues

### 12. `Views.php` checks Timber with `\Timber::class` (always true)

**File:** `app/web/themes/theme/app/Hooks/Views.php`

Same as issue #8 — `\Timber::class` resolves to the string `"Timber\Timber"`, which is always truthy. The fallback `admin_notice` for missing Timber will never trigger.

---

### 13. `app/web/themes/theme/assets/styles/main.css` is outside the webpack pipeline

**File:** `app/web/themes/theme/assets/styles/main.css`

This CSS file exists but is not compiled, versioned, or enqueued by `App.php`. It appears to be a legacy file. If it contains active styles, they are not being loaded.

---

### 14. `bin/version` may fail silently if WordPress version does not exist

**File:** `bin/version`

`curl -fL {URL} | tar -xz` pipes curl into tar. If `curl` fails (HTTP 404 for a non-existent version), the pipe exit code depends on `$PIPESTATUS`, not `$?`. The current `if ! curl -fL | tar` check may not catch curl failures correctly on all shells.

**Fix:** Use `set -o pipefail` at the top of the script or split the download and extraction into two steps with explicit error checks.

---

### 15. WordPress version `7.0` in `sample.env` may not exist

**File:** `sample.env`

```dotenv
WORDPRESS_VERSION=7.0
```

As of May 2026, it is unclear whether WordPress 7.0 exists. If it does not, `./bin/version` will fail when trying to download `wordpress-7.0.tar.gz`. Update `WORDPRESS_VERSION` to the latest stable release before running `./bin/install`.

