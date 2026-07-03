<?php

namespace Modules\Setting\app\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Modules\Setting\app\Interfaces\IFeCatalogService;

class FeCatalogService implements IFeCatalogService
{
    private const RAW_BASE_URL = 'https://raw.githubusercontent.com/lindoai/opentailwind/main';

    /**
     * Hardcoded fallback catalog of 5 FE templates.
     */
    private const FE_TEMPLATES = [
        [
            'slug' => 'agency-consulting-002-creative-agency',
            'name' => 'Creative Agency',
            'category' => 'agency-consulting',
            'description' => 'Modern creative agency landing page',
        ],
        [
            'slug' => 'agency-consulting-001-digital-agency',
            'name' => 'Digital Agency',
            'category' => 'agency-consulting',
            'description' => 'Professional digital agency template',
        ],
        [
            'slug' => 'portfolio-001-personal-portfolio',
            'name' => 'Personal Portfolio',
            'category' => 'portfolio',
            'description' => 'Clean personal portfolio showcase',
        ],
        [
            'slug' => 'landing-001-saas-landing',
            'name' => 'SaaS Landing',
            'category' => 'landing',
            'description' => 'SaaS product landing page',
        ],
        [
            'slug' => 'corporate-001-business-profile',
            'name' => 'Business Profile',
            'category' => 'corporate',
            'description' => 'Corporate business profile page',
        ],
    ];

    private string $catalogDiskPath;

    private string $feCacheDir;

    public function __construct()
    {
        $this->catalogDiskPath = storage_path('app/fe_catalog/_catalog.json');
        $this->feCacheDir = storage_path('app/fe_cache');
    }

    public function getCatalog(array $filter): array
    {
        $activeSetting = Setting::getCurrent();
        $activeSlug = $activeSetting !== null ? $activeSetting->fe_template : 'agency-consulting-002-creative-agency';

        $items = $this->loadCatalog();
        $search = $filter['q_name'] ?? '';
        $category = $filter['q_category'] ?? '';

        // Filter by search term
        if ($search !== '') {
            $lower = strtolower($search);
            $items = array_values(array_filter($items, function ($item) use ($lower) {
                return str_contains(strtolower($item['name']), $lower)
                    || str_contains(strtolower($item['slug']), $lower)
                    || str_contains(strtolower($item['description'] ?? ''), $lower);
            }));
        }

        // Filter by category
        if ($category !== '') {
            $items = array_values(array_filter($items, fn ($item) => ($item['category'] ?? '') === $category));
        }

        // Pin active slug to page 1 (move it to front)
        $activeIdx = null;
        foreach ($items as $i => $item) {
            if ($item['slug'] === $activeSlug) {
                $activeIdx = $i;
                break;
            }
        }
        if ($activeIdx !== null && $activeIdx > 0) {
            $activeItem = array_splice($items, $activeIdx, 1);
            array_unshift($items, $activeItem[0]);
        }

        // Paginate (12 per page)
        $perPage = 12;
        $page = max(1, (int) ($filter['fe_page'] ?? 1));
        $total = count($items);
        $offset = ($page - 1) * $perPage;
        $paged = array_slice($items, $offset, $perPage);

        // Collect categories for filter UI
        $allItems = $this->loadCatalog();
        $categories = array_values(array_unique(array_filter(array_column($allItems, 'category'))));
        sort($categories);

        return [
            'items' => $paged,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage) ?: 1,
            'active_slug' => $activeSlug,
            'categories' => $categories,
        ];
    }

    public function previewHtml(string $slug): string
    {
        if (! $this->isValidSlug($slug)) {
            throw new \InvalidArgumentException("Invalid slug: {$slug}");
        }

        $catalog = $this->loadCatalog();
        $slugs = array_column($catalog, 'slug');
        if (! in_array($slug, $slugs, true)) {
            throw new \InvalidArgumentException("Slug not found in catalog: {$slug}");
        }

        $cachePath = $this->feCacheDir."/{$slug}.html";

        if (file_exists($cachePath)) {
            return file_get_contents($cachePath);
        }

        // Fetch from GitHub
        $url = self::RAW_BASE_URL."/{$slug}.html";
        $response = Http::timeout(5)->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException("Failed to fetch template preview for: {$slug}");
        }

        $html = $response->body();
        $this->saveToCache($cachePath, $html);

        return $html;
    }

    public function ensure(string $slug): void
    {
        if (! $this->isValidSlug($slug)) {
            return;
        }

        $cachePath = $this->feCacheDir."/{$slug}.html";

        if (file_exists($cachePath)) {
            return;
        }

        try {
            $url = self::RAW_BASE_URL."/{$slug}.html";
            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                $this->saveToCache($cachePath, $response->body());
            }
        } catch (\Throwable) {
            // Best-effort: silently fail
        }
    }

    /**
     * Load catalog from disk cache or fall back to hardcoded list.
     */
    private function loadCatalog(): array
    {
        if (file_exists($this->catalogDiskPath)) {
            $json = file_get_contents($this->catalogDiskPath);
            $data = json_decode($json, true);
            if (is_array($data) && count($data) > 0) {
                return $data;
            }
        }

        return self::FE_TEMPLATES;
    }

    private function isValidSlug(string $slug): bool
    {
        return (bool) preg_match('/^([a-z]+(?:-[a-z]+)*)-(\d{3})-([a-z0-9-]+)$/', $slug);
    }

    private function saveToCache(string $path, string $content): void
    {
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($path, $content);
    }
}
