<?php

namespace Modules\Media\app\Interfaces;

use Illuminate\Http\UploadedFile;

interface IMediaService
{
    public function list(): array;

    public function upload(UploadedFile $file): array;

    public function delete(string $key): void;
}
