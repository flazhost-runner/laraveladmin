<?php

namespace Modules\Home\app\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Modules\Home\app\Interfaces\IFeTemplateService;
use Modules\Home\app\Interfaces\IHomeService;

/**
 * Public landing page (frontend).
 *
 * - Active slug 'default' → render the local Blade landing view (fe/default,
 *   landing v6 — header/footer partials + assets under public/fe/default,
 *   mirroring NodeAdmin's EJS fe/default layout).
 * - Any other slug → serve the cached self-contained opentailwind HTML raw
 *   (downloaded on-demand & cached under storage/app/fe/templates). When no
 *   HTML is available (offline, download failed) → fall back to the local
 *   default landing so the page always renders.
 */
class HomeService implements IHomeService
{
    public function __construct(private IFeTemplateService $feTemplateService) {}

    public function getActiveLanding(): string
    {
        $slug = $this->feTemplateService->getActiveSlug();

        if (! $this->feTemplateService->isDefaultView($slug)) {
            $html = $this->feTemplateService->getActiveHtml();
            if ($html !== null) {
                return $html;
            }
        }

        $setting = Setting::getCurrent();

        return View::make('home-module::fe.default.index', compact('setting'))->render();
    }
}
