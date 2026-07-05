<?php

namespace Modules\Home\app\Services;

use App\Exceptions\AppException;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Modules\Home\app\Interfaces\IFeTemplateService;

/**
 * FeTemplateService — active frontend (landing) template resolution +
 * on-demand download/cache. Mirrors NodeAdmin's
 * home/http/services/v1/FeTemplateService.ts.
 */
class FeTemplateService implements IFeTemplateService
{
    /** Timeout for downloading a single template HTML file (seconds). */
    private const FETCH_TIMEOUT_SECONDS = 15;

    private function dir(): string
    {
        return storage_path('app/'.config('fe_templates.dir'));
    }

    private function file(string $slug): string
    {
        return $this->dir()."/{$slug}.html";
    }

    public function isCached(string $slug): bool
    {
        return file_exists($this->file($slug));
    }

    /**
     * A slug is valid when it is the special 'default' (local Blade view) or
     * matches the opentailwind pattern `{category}-{NNN}-{name}` — covering
     * all 640 landings without binding to the static curated catalog.
     * (Anti-SSRF: the pattern restricts charset to a-z0-9- + fixed structure.)
     */
    public function isValidSlug(?string $slug): bool
    {
        if ($slug === null || $slug === '') {
            return false;
        }

        return $slug === config('fe_templates.default_view')
            || (bool) preg_match(config('fe_templates.slug_re'), $slug);
    }

    public function getActiveSlug(): string
    {
        $setting = Setting::getCurrent();
        $slug = trim((string) ($setting->fe_template ?? ''));

        return $this->isValidSlug($slug) ? $slug : config('fe_templates.default');
    }

    /**
     * True when the slug is 'default' — rendered via the local Blade landing
     * view (fe/default, landing v6), not raw cached HTML.
     */
    public function isDefaultView(string $slug): bool
    {
        return $slug === config('fe_templates.default_view');
    }

    /**
     * Make sure the template exists locally. If not cached yet, download the
     * HTML from opentailwind (GitHub raw) into the cache folder. Only slugs
     * matching the opentailwind pattern are allowed (anti-SSRF).
     */
    public function ensure(string $slug): void
    {
        if (! $this->isValidSlug($slug)) {
            throw new AppException('Template tidak dikenali', 400);
        }
        if ($this->isDefaultView($slug) || $this->isCached($slug)) {
            return;
        }

        $url = config('fe_templates.base_url')."/{$slug}.html";

        try {
            $response = Http::timeout(self::FETCH_TIMEOUT_SECONDS)->get($url);
        } catch (\Throwable $e) {
            throw new AppException("Gagal mengunduh template: {$e->getMessage()}", 502);
        }

        if (! $response->successful()) {
            throw new AppException("Gagal mengunduh template: HTTP {$response->status()}", 502);
        }

        $html = $response->body();
        if (stripos($html, '</html>') === false) {
            throw new AppException('Template terunduh tidak valid', 502);
        }

        if (! is_dir($this->dir())) {
            mkdir($this->dir(), 0755, true);
        }
        file_put_contents($this->file($slug), $html);
    }

    /**
     * Raw HTML of the active landing (null when the active template is the
     * local 'default' view or no cached/downloadable HTML is available — the
     * caller then renders the bundled fe/default landing as a safe fallback).
     * Downloads on first use (best-effort) so a fresh install serves the
     * default opentailwind template as soon as the network allows.
     */
    public function getActiveHtml(): ?string
    {
        $slug = $this->getActiveSlug();
        if ($this->isDefaultView($slug)) {
            return null;
        }

        if (! $this->isCached($slug)) {
            try {
                $this->ensure($slug);
            } catch (AppException) {
                // offline/failed download → fall through to fallback
            }
        }

        $target = $this->isCached($slug) ? $slug : config('fe_templates.default');
        $file = $this->file($target);
        if (! file_exists($file)) {
            return null;
        }

        return file_get_contents($file);
    }
}
