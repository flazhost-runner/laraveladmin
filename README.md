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
php artisan storage:link   # symlink public/storage -> storage/app/public (local uploads)
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

## Storage & switching backends

Upload media (modul `Media`) memakai adapter generik `STORAGE_DRIVER` — **ganti backend cukup dengan edit `.env` + restart, tanpa ubah kode/view.** `STORAGE_DRIVER` dipetakan ke disk Laravel di `config/filesystems.php` (`storage_driver`), lalu `MediaService` memilih disk berdasarkan nilainya:

| `STORAGE_DRIVER` | Disk Laravel | URL render yang dihasilkan |
|---|---|---|
| `local` (default) | `public` (`storage/app/public`) | `APP_URL/storage/<key>` — dilayani lewat symlink `public/storage` |
| `oss` | `oss` (S3-compatible, path-style) | URL absolut dari endpoint OSS |
| `s3` | `s3` (AWS S3 / kompatibel) | URL absolut dari endpoint/bucket S3 |

DB (konten editor / setting) menyimpan **URL absolut** yang dihitung server saat upload lewat `Storage::disk(...)->url(key)`; `key` (object path, mis. `media/xxx.png`) dipakai untuk hapus. `<img>` merender sama untuk semua driver karena URL selalu absolut & benar per-driver.

### Local

File tersimpan di `storage/app/public/media/`. Agar bisa diakses web di `APP_URL/storage/...`, jalankan sekali:

```bash
php artisan storage:link   # membuat symlink public/storage -> storage/app/public
```

Sudah termasuk di `composer setup`. **Pada tiap deploy** (server baru / rilis `public/` yang bersih), symlink harus dibuat ulang — jalankan `php artisan storage:link` di langkah deploy.

### OSS / S3

```dotenv
STORAGE_DRIVER=s3            # atau: oss
STORAGE_ACCESS_KEY_ID=...
STORAGE_SECRET_ACCESS_KEY=...
STORAGE_BUCKET=...
STORAGE_ENDPOINT=https://...
STORAGE_REGION=...
```

Restart aplikasi (dan `php artisan config:clear` bila config di-cache). Upload berikutnya langsung mengembalikan URL absolut objek — tidak perlu `storage:link`.

### Ganti backend / migrasi data

Ganti backend **tidak** memindahkan file yang sudah ada — URL lama di DB tetap menunjuk lokasi lama. Untuk memindahkan objek yang sudah ada:

```bash
# ke S3
aws s3 sync storage/app/public/media s3://<bucket>/media
# ke Alibaba OSS
ossutil cp -r storage/app/public/media oss://<bucket>/media
```

Lalu (opsional) perbarui URL lama yang tersimpan di DB agar menunjuk endpoint baru.

### Catatan operasional

- Upload di-`.gitignore` (lihat `storage/app/public/.gitignore` = `*` kecuali `.gitignore`) — folder tetap ada di repo, isinya tidak. `public/storage` juga gitignored (artefak deploy).
- **Local di produksi bersifat ephemeral**: pada container/PaaS, `storage/app/public` hilang tiap redeploy. Mount **persistent volume** ke `storage/app/public` (dan pastikan `storage:link` dijalankan setelah deploy), atau gunakan `oss`/`s3` untuk produksi.

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
