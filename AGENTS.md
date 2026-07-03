# AGENTS.md — LaravelAdmin Development Rules

> Sumber kebenaran tunggal. Setiap AI (Claude Code, Cursor, Copilot) dan developer WAJIB mengikuti dokumen ini.

LaravelAdmin adalah bootstrap admin panel Laravel 13 yang merupakan port 1:1 konseptual dari NodeAdmin menggunakan idiom native Laravel.

## Alur Request (Lifecycle)

```
Route (named, method-aware)
  → Middleware: web → EnsureAuthenticated → Authorize → validator
  → Controller (thin: parse request, call service, return response)
  → Service (business logic, throw AppException)
  → Eloquent Model / DB
  ↘ error → withExceptions() handler (terpusat)
```

## Prinsip Wajib

1. **DI (Service Container)**: bind `IXxxService::class → XxxService::class` di ServiceProvider module; inject via constructor auto-resolve. JANGAN `new XxxService()` di controller/route.
2. **Service implements interface**: setiap service punya `I*Service` interface.
3. **Error terpusat**: service `throw AppException` (atau subclass), JANGAN return error. Handler di `bootstrap/app.php ->withExceptions()`.
4. **Controller tipis**: hanya parse request, panggil service, return view/JSON. Tanpa logika bisnis.
5. **RBAC route-driven**: permission = (nama-route, method, guard_name). Sync otomatis via `php artisan permissions:sync`. JANGAN hardcode permission list.
6. **Named routes WAJIB**: ikuti pola `admin.v1.{modul}.{aksi}` (web) dan `api.v1.{modul}.{aksi}` (API), method-aware.
7. **Method override**: PUT/DELETE dari form HTML via `@method('PUT')` / `@method('DELETE')`. MethodOverride middleware menangani.
8. **Validasi = FormRequest**: gunakan `$request->validated()` — anti mass-assignment otomatis. Untuk scaffolded stubs boleh inline `$request->validate([])` lalu ganti dengan FormRequest.
9. **DB portabel**: tipe kolom abstrak (`string/integer/text/timestamp`), JANGAN `longtext/datetime/enum/collation` hardcoded.
10. **Env via config**: akses env HANYA via `config('laraveladmin.*')` di module code. JANGAN `env()` langsung di `Modules/`.
11. **Test wajib**: tiap modul harus punya test. Run: `php artisan test`.

## Matriks Kebutuhan Artefak

| Jenis Fitur          | Interface | Service | Controller (Web) | Controller (API) | FormRequest | Views | Routes | Test |
|----------------------|:---------:|:-------:|:----------------:|:----------------:|:-----------:|:-----:|:------:|:----:|
| CRUD penuh (web+api) | Y         | Y       | Y                | Y                | Y           | Y     | Y      | Y    |
| CRUD web-only        | Y         | Y       | Y                | -                | Y           | Y     | Y      | Y    |
| API-only             | Y         | Y       | -                | Y                | Y           | -     | Y      | Y    |
| Read-only / showcase | -         | -       | Y                | -                | -           | Y     | Y      | Y    |

## Sebelum Coding: Sajikan Rencana Artefak

Saat membuat fitur/modul baru, sajikan dulu matriks artefak yang dibutuhkan ke user. Tanya bila ambigu (UI vs API-only, read-only vs CRUD, perlu API?). Lalu jalankan `php artisan admin:make-module {Name}`.

## Struktur Module Standar

```
Modules/{Name}/
  app/
    Http/
      Controllers/
        Web/V1/{Name}Controller.php
        Api/V1/{Name}Controller.php
      Requests/
        Store{Name}Request.php
        Update{Name}Request.php
    Interfaces/
      I{Name}Service.php
    Services/
      {Name}Service.php
    Providers/
      {Name}ServiceProvider.php
  database/
    migrations/
  resources/
    views/be/default/{lower}/
      index.blade.php
      create.blade.php
      edit.blade.php
  routes/
    web.php
    api.php
  tests/
    Feature/
      {Name}Test.php
  module.json
```

## DO NOT (akan ditolak checker)

- `new XxxService()` di route/controller
- `return $e` atau `instanceof Exception` di service
- `env()` langsung di `Modules/` (pakai `config()`)
- Module tanpa test
- Route tanpa nama
- Service tanpa interface
- Module tanpa entry di `module.json['providers']`

## Checklist Modul Baru

1. `php artisan admin:make-module {Name}` — scaffold struktur lengkap
2. Isi `I{Name}Service` interface dengan signature nyata
3. Implementasi `{Name}Service` (inject repository/model, throw `AppException`)
4. Daftarkan binding di `{Name}ServiceProvider` (sudah ada dari scaffold)
5. Buat `FormRequest` validator(s) di `app/Http/Requests/`
6. Isi `Controller` (web + api bila perlu) — panggil service, return view/JSON
7. Pastikan routes terisi dan named dengan benar
8. Isi Views (canonical table structure, lihat `Modules/Access` sebagai acuan)
9. Tulis Tests (minimal: index + store + edit + update + delete)
10. `php artisan permissions:sync` — sync permission baru
11. `php artisan conventions:check` — harus hijau
12. Update `README.md` + `docs/API.md`

## Perintah Penting

```bash
php artisan admin:make-module {Name}   # scaffold modul baru
php artisan permissions:sync           # sync permission dari routes
php artisan conventions:check          # cek kepatuhan konvensi (CI gate)
php artisan migrate:fresh --seed       # reset DB + seed
php artisan test                       # jalankan semua tests
composer check                         # pint + phpstan + test
```

## Definition of Done (per modul/fitur)

- [ ] `php artisan conventions:check` hijau
- [ ] `php artisan test` hijau
- [ ] `./vendor/bin/pint --test` hijau (tidak ada format error)
- [ ] `./vendor/bin/phpstan analyse` hijau
- [ ] `README.md` diperbarui
- [ ] `docs/API.md` diperbarui (bila ada endpoint baru)

## Pola Acuan Termutakhir

- Module terlengkap: `Modules/Access` (CRUD users, roles, permissions + role-permission)
- Module sederhana: `Modules/Setting`
- Module static (tanpa service/interface): `Modules/Components`

## Catatan Khusus

- **Static module** (Components): tidak memerlukan Interface & Service. Tambahkan ke array `$staticModules` di `CheckConventions` command bila ada static module baru.
- **Guard**: route web pakai `guard_name = 'web'`, route API pakai `guard_name = 'api'`. `permissions:sync` mendeteksi otomatis dari prefix nama route.
- **View namespace**: `'{lower}-module::be.default.{lower}.index'` — didaftarkan di ServiceProvider via `loadViewsFrom(..., '{lower}-module')`.
