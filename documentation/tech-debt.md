# Technical Debt and Known Issues

_Last reviewed: 2026-06-10_

This register lists concrete issues found during repository inspection and local runtime checks. It is documentation only; no code was modified.

Severity guide:

- **Critical:** likely runtime failure, data/security risk, or broken local/release workflow.
- **High:** likely broken feature, fragile deployment, or important modernization item.
- **Medium:** maintainability, portability, or consistency issue.
- **Low:** cleanup/documentation/cosmetic issue.

## Resolved / recently fixed

- **`pnpm` script references:** The `bin/pnpm` script was mentioned in `01-architecture.md` but has been removed. All references have been updated to instruct users to run `pnpm` directly.
- **Outdated `src` directory structure:** The directory map in `01-architecture.md` showed an outdated structure for the `src/` directory. It has been updated to reflect the current `src/theme` and `src/plugins` structure.
- **Inconsistent `bari-cli` description:** The `bari-cli` plugin was described as "not yet implemented" in `01-architecture.md`, but is fully implemented. The documentation has been unified.
- **Inconsistent service names:** The service names in `03-environment.md` were inconsistent with `compose.yml`. This has been corrected.
- **Confusing frontend entry example:** The `plugin-development.md` guide provided a confusing example for adding a new frontend entry. This has been clarified with two distinct options for developers.

## Critical

_Nothing at the moment._

## High

- **`pnpm` script references:** The `bin/pnpm` script is mentioned in `01-architecture.md` but was removed. All references should be updated to instruct users to run `pnpm` directly.
- **Outdated `src` directory structure:** The directory map in `01-architecture.md` shows an outdated structure for the `src/` directory. It needs to be updated to reflect the current `src/theme` and `src/plugins` structure described in `08-frontend-build.md`.

## Medium

- **Hardcoded MariaDB version:** The MariaDB version `11.4.7` is hardcoded in `02-infrastructure.md`. This should be updated to reflect the version specified in `compose.yml` or generalized.
- **`omitsis-*` plugin prefix:** The `coding-standards.md` file still references a legacy `omitsis-*` plugin prefix. This should be removed or replaced with the current `bari-*` prefix.

## Low / cleanup

- **`bin/to` script reference:** The `04-quick-start.md` file mentions a `bin/to` script for opening a shell inside a container, but notes that it doesn't exist. This reference should be removed to avoid confusion.
- **Inconsistent `wp-cli.yml` alias explanation:** The explanation for the `@wp` alias in `04-quick-start.md` is confusing. It should be clarified that the alias name is static and does not need to be changed if `SITE_SLUG` is updated.

## Modernization roadmap

Suggested order:

1. **Stabilize local runtime:** Keep the project covered by smoke tests.
2. **Fix operational scripts:** Update any scripts for portability and correctness.
3. **Normalize URL/path generation:** Remove hardcoded paths from custom code.
4. **Security pass:** Review REST/AJAX permission callbacks, nonces, public endpoints, salts, secrets, uploads/downloads.
5. **Plugin metadata pass:** Normalize headers, versions, text domains, and Requires fields.
6. **Schema governance:** Move custom table changes into documented reversible migrations.
7. **Build modernization:** Choose a standard asset build workflow and update scripts/docs.
8. **Namespacing/refactor:** Gradually replace generic global functions in high-change plugins.
9. **Block foundation:** Add project-owned FSE patterns and native dynamic Gutenberg block examples for plugin-owned data.
10. **Compatibility matrix:** Document supported WordPress/PHP/plugin versions and upgrade test checklist.

## Validation commands for future fixes

After addressing items in this document, run at minimum:

```bash
./bin/wp core version
./bin/wp plugin list
./bin/wp theme list
./bin/wp site list
tail -n 100 app/logs/xdebug.log
```

For asset/script fixes, also run relevant plugin/theme builds and confirm no `Query Monitor` fatal output appears in page responses.
