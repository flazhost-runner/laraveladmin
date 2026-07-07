# LaravelAdmin

Bootstrap admin panel untuk Laravel 13 — port 1:1 konseptual dari NodeAdmin menggunakan idiom native Laravel.

## Stack

- Laravel 13.x + PHP 8.3+
- nwidart/laravel-modules (modular per fitur)
- spatie/laravel-permission (RBAC base)
- firebase/php-jwt (JWT API auth)
- predis/predis (Redis session + JWT blacklist)
- PHPUnit 12 (testing)
- Larastan + Pint (static analysis + formatting)
- Tailwind CSS CDN (admin UI, themeable)

## Quick Start

```bash
composer install
cp .env.example .env
php artisan key:generate
# Set JWT_SECRET, DB credentials in .env
php artisan migrate:fresh --seed
php artisan serve
# Open http://localhost:8000
# Admin: admin@laraveladmin.test / Admin@1234
```

## Features

- Multi-module: Auth, Access (User/Role/Permission), Dashboard, Setting, Profile, Components, Home
- RBAC route-driven (permissions auto-synced from route registry)
- Theme switcher (9 themes, DB-driven, no rebuild)
- Frontend template catalog (opentailwind, 640 templates)
- JWT API + Web session dual auth, JWT blacklist on logout
- FormRequest validation (anti mass-assignment)
- Method override (PUT/DELETE from HTML forms)
- APP_MODE=full|api (runtime variant)

## Testing

```bash
php artisan test
composer check  # pint + phpstan + test
```

## API

REST API di-prefix `/api/v1` dengan auth JWT (`Authorization: Bearer <token>`).

Postman collection: [`docs/postman/LaravelAdmin.postman_collection.json`](docs/postman/LaravelAdmin.postman_collection.json).

- Import ke Postman, lalu set variable `base_url` (default `http://localhost:8000`, sesuai `php artisan serve`).
- Isi `access_token` dari response login untuk request yang butuh auth.

Catatan: endpoint "get one" di port ini adalah `GET /api/v1/access/{resource}/{id}` (idiomatik Laravel), sedikit berbeda dari path `/:id/edit` bawaan NodeAdmin pada collection.

## Creating a new module

```bash
php artisan admin:make-module {Name}
composer dump-autoload
php artisan config:clear
```

## Sync permissions from routes

```bash
php artisan permissions:sync
```

## Convention check (CI gate)

```bash
php artisan conventions:check
```
