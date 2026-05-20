# Bagirata — Agent Guide

Laravel 12 bill-splitting app ("patungan") with OCR receipt scanning.

## Quick start

```bash
cp .env.example .env
composer setup                           # install, .env, key:generate, migrate, npm install & build
composer dev                             # dev server + queue + logs + Vite concurrently
composer test                            # config:clear + php artisan test
```

## Project structure

- `routes/web.php` — main app routes (dashboard, activities CRUD, friends, OCR)
- `routes/auth.php` — Breeze auth routes (login, register, password reset, email verify)
- `app/Models/` — `User`, `Activity` (session), `Member`, `Item`
- `app/Http/Controllers/ActivityController.php` — dashboard, CRUD, OCR scan endpoint
- `resources/views/` — Blade templates (master layout, dashboard, activities/*, profile/*, auth/*)

## Key stack

- PHP 8.2+, Laravel 12, SQLite
- Tailwind CSS v3 + Alpine.js via Vite
- `ladumor/laravel-pwa` for PWA support
- `laravel/breeze` for auth scaffolding
- `laravel/pint` for linting

## DB & env quirks

- SQLite by default (no MySQL/PostgreSQL dependency)
- Session, cache, and queue all use `database` driver — run migrations first
- OCR requires `OCR_SPACE_API_KEY` in `.env` (free tier available at ocr.space)
- `.env.example` has sensible defaults; only `APP_KEY` and `OCR_SPACE_API_KEY` are required

## Testing

```bash
composer test                            # runs all tests
php artisan test --filter=ExampleTest    # single test
```

Tests use in-memory SQLite (`:memory:`). No external services needed.

## Linting

```bash
./vendor/bin/pint                        # format PHP code (PSR-12)
```

## Commands

- `php artisan make:model Foo -mf` — model + migration + factory
- `php artisan migrate:fresh --seed` — reset DB with seeders
- `php artisan queue:work` — process queue jobs
