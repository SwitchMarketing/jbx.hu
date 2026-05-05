# AGENTS

## Purpose
Quick-start guidance for AI coding agents working on this repository.

## Read First
- Project context and architecture: [CLAUDE.md](CLAUDE.md)
- Secondary summary: [GEMINI.md](GEMINI.md)
- Routes and admin API registration: [app/Config/Routes.php](app/Config/Routes.php)
- Admin response contract: [app/Controllers/Admin/BaseResourceController.php](app/Controllers/Admin/BaseResourceController.php)

Use linked docs for detail. Do not duplicate them in code changes.

## Day-to-Day Commands
- Install: `composer install` and `npm install`
- Run app: `php spark serve`
- DB migrations: `php spark migrate`
- Tests: `composer test`
- PHP syntax check for touched files: `php -l <file>`

Admin frontend workflow:
- Dev/watch mode is the default: `npm run dev` (runs Sencha watch in `public/admin`)
- Do not run production build automatically.
- Only run `sencha app build production` when explicitly requested.

## Repository Conventions
- Public-facing text, routes, and most admin labels are Hungarian. Match existing language.
- Commit messages are Hungarian.
- For admin endpoints, keep the existing JSON envelope via `setSuccess`, `setMessage`, `setData`, `setTotal`, and `setResponse`.
- Preserve the logical DB group distinction (`default` and `shop`) from [app/Config/Database.php](app/Config/Database.php).

## High-Impact Pitfalls
- Category/blog routes are cached. If slug/category behavior is changed, verify route cache generation paths in:
  - [app/Helpers/CategoryRouteCache.php](app/Helpers/CategoryRouteCache.php)
  - [app/Helpers/BlogRouteCache.php](app/Helpers/BlogRouteCache.php)
- Avoid broad reformatting or style churn in generated admin build output.
- Prefer targeted patches for UTF-8 Hungarian PHP files.

## Change Checklist
- Keep changes minimal and scoped.
- Validate edited PHP files with `php -l`.
- If API shape changes, verify corresponding ExtJS store/controller usage in `public/admin/app/`.
