<?php

use App\Exceptions\ValidationAppException;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('paginate')) {
    /**
     * Build a paginated result array.
     *
     * @param  array<mixed>  $items
     * @return array{data: array<mixed>, meta: array{total: int, per_page: int, current_page: int, last_page: int, has_prev: bool, has_next: bool, page_numbers: int[], from: int, to: int}}
     */
    function paginate(array $items, int $total, int $perPage, int $page): array
    {
        $totalPage = $perPage > 0 ? (int) ceil($total / $perPage) : 1;
        $totalPage = max(1, $totalPage);

        $from = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $to = min($page * $perPage, $total);
        $pageNumbers = range(max(1, $page - 2), min($totalPage, $page + 2));

        return [
            'data' => $items,
            'datas' => $items,  // compat alias
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $totalPage,
                'has_prev' => $page > 1,
                'has_next' => $page < $totalPage,
                'page_numbers' => $pageNumbers,
                'from' => $from,
                'to' => $to,
            ],
        ];
    }
}

if (! function_exists('ci_like')) {
    /**
     * Build a case-insensitive LIKE clause descriptor for Eloquent whereRaw.
     *
     * Usage: $q->whereRaw("LOWER($column) LIKE ?", ['%'.strtolower($value).'%'])
     *
     * @return array{column: string, operator: string, value: string}
     */
    function ci_like(string $column, string $value): array
    {
        return [
            'column' => "LOWER({$column})",
            'operator' => 'LIKE',
            'value' => '%'.strtolower($value).'%',
        ];
    }
}

if (! function_exists('clean_rich_text')) {
    /**
     * Sanitize rich-text HTML, keeping only safe tags.
     */
    function clean_rich_text(string $html): string
    {
        $allowed = '<p><br><strong><em><u><s><ul><ol><li>'
            .'<h1><h2><h3><h4><h5><h6>'
            .'<a><img><div><span>'
            .'<table><thead><tbody><tr><th><td>'
            .'<blockquote><pre><code>';

        return strip_tags($html, $allowed);
    }
}

if (! function_exists('generate_otp')) {
    /**
     * Generate a cryptographically random 6-digit OTP string.
     */
    function generate_otp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}

if (! function_exists('hash_otp')) {
    function hash_otp(string $otp): string
    {
        $cost = (int) config('laraveladmin.bcrypt_rounds', 10);

        return password_hash($otp, PASSWORD_BCRYPT, ['cost' => $cost]);
    }
}

if (! function_exists('verify_otp')) {
    /**
     * Verify a plain OTP against a bcrypt hash.
     */
    function verify_otp(string $otp, string $hashed): bool
    {
        return password_verify($otp, $hashed);
    }
}

if (! function_exists('auth_user')) {
    /**
     * Get the currently authenticated user from session, with roles+permissions eager-loaded.
     * Uses a static per-request cache keyed by user_id.
     */
    function auth_user(): ?User
    {
        $userId = session('user_id');
        if (! $userId) {
            return null;
        }
        static $cache = [];
        if (! isset($cache[$userId])) {
            $cache[$userId] = User::with('roles.permissions')->find($userId);
        }

        return $cache[$userId];
    }
}

if (! function_exists('hasAccess')) {
    /**
     * Check whether the currently authenticated user has a given permission.
     *
     * Administrator role bypasses permission checks — parity with NodeAdmin
     * (res.locals.hasAccess), GoAdmin (User.HasAccess) and the Authorize
     * middleware, so the sidebar shows the full menu for admins even when no
     * explicit route permissions are seeded.
     */
    function hasAccess(string $name, string $method = 'GET'): bool
    {
        $user = auth_user();
        if ($user === null) {
            return false;
        }
        if ($user->hasRole('Administrator')) {
            return true;
        }

        return $user->hasPermission($name, $method);
    }
}

if (! function_exists('hasRole')) {
    /**
     * Check whether the currently authenticated user has a given role.
     */
    function hasRole(string $roleName): bool
    {
        return auth_user()?->hasRole($roleName) ?? false;
    }
}

if (! function_exists('getFile')) {
    /**
     * Resolve a stored file KEY to a browser URL — driver-aware (paritas NodeAdmin
     * fileService.getFile): local → /storage/<key>; oss/s3 → presigned URL (6 jam).
     * Key avatar default di-special-case ke aset statis avatar.svg.
     */
    function getFile(?string $name): string
    {
        if (! $name) {
            return '';
        }
        if ($name === 'modules/access/user/user.png') {
            return asset('be/default/img/avatar.svg');
        }
        if (Str::startsWith($name, ['http://', 'https://', '//'])) {
            return $name;
        }
        $driver = config('filesystems.storage_driver', 'local');
        if (in_array($driver, ['oss', 's3'], true)) {
            try {
                return Storage::disk($driver)
                    ->temporaryUrl($name, now()->addHours(6));
            } catch (Throwable) {
                return Storage::disk($driver)->url($name);
            }
        }

        return asset('storage/'.ltrim($name, '/'));
    }
}

if (! function_exists('storeImage')) {
    /**
     * Simpan gambar upload dengan kunci modul (paritas NodeAdmin fileService.uploadFile):
     * validasi konten via getimagesizefromstring (magic byte, bukan sekadar mime),
     * konversi jpg/jpeg/png/bmp → webp quality 80 (webp/gif apa adanya), lalu put ke
     * disk sesuai STORAGE_DRIVER. Mengembalikan KEY yang disimpan di DB.
     */
    function storeImage(UploadedFile $file, string $keyStem): string
    {
        $buffer = file_get_contents($file->getRealPath());
        if ($buffer === false || @getimagesizefromstring($buffer) === false) {
            throw new ValidationAppException('File bukan gambar yang valid');
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'png');
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'bmp'], true) && function_exists('imagewebp')) {
            $img = @imagecreatefromstring($buffer);
            if ($img !== false) {
                imagepalettetotruecolor($img);
                imagealphablending($img, true);
                imagesavealpha($img, true);
                ob_start();
                $encoded = imagewebp($img, null, 80);
                $converted = (string) ob_get_clean();
                imagedestroy($img);
                if ($encoded && $converted !== '') {
                    $buffer = $converted;
                    $ext = 'webp';
                }
            }
        }

        $key = $keyStem.'.'.$ext;
        $driver = config('filesystems.storage_driver', 'local');
        $disk = in_array($driver, ['oss', 's3'], true) ? $driver : 'public';
        Storage::disk($disk)->put($key, $buffer);

        return $key;
    }
}

if (! function_exists('getError')) {
    /**
     * Return the first validation error message for a given key from the session.
     */
    function getError(string $key): string
    {
        return session('errors')?->first($key) ?? '';
    }
}

if (! function_exists('getOld')) {
    /**
     * Return the old input value for a given key, with an optional default.
     */
    function getOld(string $key, mixed $default = ''): mixed
    {
        return old($key, $default);
    }
}
