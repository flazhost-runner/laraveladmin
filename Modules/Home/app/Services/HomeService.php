<?php

namespace Modules\Home\app\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Modules\Home\app\Interfaces\IHomeService;

class HomeService implements IHomeService
{
    /**
     * Get the active landing page HTML.
     *
     * If fe_template = 'agency-consulting-002-creative-agency', renders the
     * built-in Blade view (fe/default/index) with setting data.
     * Otherwise, serves the cached HTML from storage/app/fe_cache/{slug}.html.
     */
    public function getActiveLanding(): string
    {
        $setting = Setting::getCurrent();
        $slug = $setting !== null ? $setting->fe_template : 'agency-consulting-002-creative-agency';

        if ($slug === 'agency-consulting-002-creative-agency') {
            return View::make('home-module::fe.default.index', compact('setting'))->render();
        }

        $cachePath = storage_path("app/fe_cache/{$slug}.html");

        if (file_exists($cachePath)) {
            return file_get_contents($cachePath);
        }

        // Fallback: render default Blade view
        return View::make('home-module::fe.default.index', compact('setting'))->render();
    }
}
