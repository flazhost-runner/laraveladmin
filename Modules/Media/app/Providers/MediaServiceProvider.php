<?php

namespace Modules\Media\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Media\app\Interfaces\IMediaService;
use Modules\Media\app\Services\MediaService;

class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IMediaService::class, MediaService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
    }
}
