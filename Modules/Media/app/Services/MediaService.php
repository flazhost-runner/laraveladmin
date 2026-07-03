<?php

namespace Modules\Media\app\Services;

use App\Exceptions\ValidationAppException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Media\app\Interfaces\IMediaService;

class MediaService implements IMediaService
{
    private const MAX_SIZE_BYTES = 2 * 1024 * 1024; // 2MB

    private const ALLOWED_MIME = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];

    private string $disk;

    public function __construct()
    {
        $driver = config('filesystems.storage_driver', 'local');
        $this->disk = match ($driver) {
            'oss', 's3' => $driver,
            default => 'public',
        };
    }

    public function list(): array
    {
        try {
            $files = Storage::disk($this->disk)->files('media');
        } catch (\Throwable) {
            $files = Storage::disk('public')->files('media');
        }

        return array_map(function ($path) {
            return [
                'key' => $path,
                'url' => Storage::disk($this->disk)->url($path),
                'name' => basename($path),
            ];
        }, $files);
    }

    public function upload(UploadedFile $file): array
    {
        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            throw new ValidationAppException('File size must not exceed 2MB.');
        }
        if (! in_array($file->getMimeType(), self::ALLOWED_MIME)) {
            throw new ValidationAppException('Only image files are allowed.');
        }

        $filename = uniqid('media_', true).'.'.$file->getClientOriginalExtension();
        $path = 'media/'.$filename;

        try {
            Storage::disk($this->disk)->putFileAs('media', $file, $filename);
            $url = Storage::disk($this->disk)->url($path);
        } catch (\Throwable) {
            Storage::disk('public')->putFileAs('media', $file, $filename);
            $url = Storage::disk('public')->url($path);
            $path = 'media/'.$filename;
        }

        return ['url' => $url, 'key' => $path];
    }

    public function delete(string $key): void
    {
        try {
            Storage::disk($this->disk)->delete($key);
        } catch (\Throwable) {
            Storage::disk('public')->delete($key);
        }
    }
}
