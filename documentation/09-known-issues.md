# Known Issues

All previously catalogued issues have been resolved as of **May 2026**. The table below summarises what was fixed and how.

---

## Resolved Issues

| # | Area | Issue | Resolution |
|---|---|---|---|
| 1 | `functions.php` | `use App\Hooks\Acf` import caused a PHP fatal error (class did not exist) | Removed the unused `use` statement |
| 2 | `single.twig` | Included non-existent `comment.twig` / `comment-form.twig` partials | Removed the comment section entirely |
| 3 | `bari-cli` plugin | Plugin was a 5-line stub with no implementation | Fully implemented — see [`docs/06-php-wordpress.md`](./06-php-wordpress.md) |
| 4 | `bin/to` | Script referenced in docs but did not exist on disk | Script removed from documentation; use `docker exec` directly |
| 5 | `bin/pnpm` | Wrong working directory (`app/wp-content` instead of project root) | Script removed; run `pnpm` directly from the project root |
| 6 | `bin/pnpm` | Broken empty-string check (`[ PNPM = "" ]` instead of `[ -z "$PNPM" ]`) | Script removed |
| 7 | `bin/db` | Hardcoded project-specific database aliases (`ei`, `ti`, `ci`, `cti`) | Script removed |
| 8 | `functions.php` | Timber check used `!\Timber::class` (always `false`) instead of `!class_exists()` | Outer condition corrected to `! class_exists('Timber\Timber')` |
| 9 | `single.twig` | `<img>` missing `alt`, `loading`, `width`, and `height` attributes | Attributes added |
| 10 | `bin/pnpm` | Error message referenced non-existent `.env.example` (correct file is `sample.env`) | Script removed |
| 11 | `Theme.php` | Scaffold set `page_template` to `home.php` (template not registered) | `page_template` key removed from scaffold |
| 12 | `Views.php` | Timber check used `\Timber::class` (always truthy) | Corrected to `class_exists('Timber\Timber')` |
| 13 | Frontend assets | `theme/assets/styles/main.css` existed outside the webpack pipeline | All custom styles and scripts are now centralised in `src/` at the project root and compiled into `app/dist/` — see [`docs/08-frontend-build.md`](./08-frontend-build.md) |
| 14 | `bin/version` | `curl \| tar` pipe hid curl failures from `$?` | Added `set -o pipefail` at the top of the script |
| 15 | `sample.env` | `WORDPRESS_VERSION=7.0` was flagged as potentially non-existent | WordPress 7.0 is the current stable release; no change needed |
