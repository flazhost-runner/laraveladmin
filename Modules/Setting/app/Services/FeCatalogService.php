<?php

namespace Modules\Setting\app\Services;

use App\Exceptions\AppException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\Setting\app\Interfaces\IFeCatalogService;

/**
 * FeCatalogService — frontend template catalog (640 opentailwind landings).
 * Mirrors NodeAdmin's home/http/services/v1/FeCatalogService.ts.
 *
 * Source of truth = GitHub tree API, fetched ONCE then cached (process memory
 * + disk file) to avoid hammering GitHub. Search & pagination are server-side.
 */
class FeCatalogService implements IFeCatalogService
{
    /** In-memory catalog cache TTL (seconds). Disk persists across restarts. */
    private const CATALOG_TTL_SECONDS = 6 * 60 * 60; // 6 jam

    /** Timeout fetching one preview HTML file (seconds) — tight, single light file. */
    private const FETCH_TIMEOUT_SECONDS = 8;

    /**
     * Timeout fetching the catalog tree (seconds) — looser than preview: the
     * recursive tree response covers 640 entries and only runs ONCE before
     * being cached (memory + disk). Loose so a network blip does not degrade
     * to the curated fallback (15 items) that makes the catalog look nearly
     * empty.
     */
    private const TREE_FETCH_TIMEOUT_SECONDS = 20;

    /** @var array{at: float, data: array}|null Per-process memo cache. */
    private static ?array $memo = null;

    private function cacheFile(): string
    {
        return storage_path('app/'.config('fe_templates.catalog_file'));
    }

    /** Path of a locally downloaded template HTML (used as preview fallback). */
    private function localHtmlFile(string $slug): string
    {
        return storage_path('app/'.config('fe_templates.dir')."/{$slug}.html");
    }

    /** Title-case hyphen segments: `digital-marketing` -> `Digital Marketing`. */
    private function titleize(string $value): string
    {
        return implode(' ', array_map(
            fn (string $w) => Str::ucfirst($w),
            array_filter(explode('-', $value), fn (string $w) => $w !== '')
        ));
    }

    /**
     * Derive display metadata from an opentailwind slug. If the slug does not
     * match the pattern, use the slug as-is with category 'Other'.
     */
    private function deriveFeTemplate(string $slug): array
    {
        if (! preg_match(config('fe_templates.slug_re'), $slug, $m)) {
            return ['slug' => $slug, 'name' => $this->titleize($slug), 'category' => 'Other'];
        }

        return ['slug' => $slug, 'name' => $this->titleize($m[3]), 'category' => $this->titleize($m[1])];
    }

    /** Parse tree paths -> landing slugs (strip `landings/` prefix & `.html`). */
    private function parseTree(array $tree): array
    {
        $items = [];
        foreach ($tree as $node) {
            if (! is_array($node)
                || ($node['type'] ?? null) !== 'blob'
                || ! is_string($node['path'] ?? null)
                || ! str_starts_with($node['path'], 'landings/')
                || ! str_ends_with($node['path'], '.html')) {
                continue;
            }
            $items[] = $this->deriveFeTemplate(substr($node['path'], strlen('landings/'), -strlen('.html')));
        }

        // Stable order: category then name.
        usort($items, fn (array $a, array $b) => [$a['category'], $a['name']] <=> [$b['category'], $b['name']]);

        return $items;
    }

    private function readDiskCache(): ?array
    {
        try {
            if (! file_exists($this->cacheFile())) {
                return null;
            }
            $data = json_decode((string) file_get_contents($this->cacheFile()), true);

            return is_array($data) && count($data) > 0 ? $data : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function writeDiskCache(array $data): void
    {
        try {
            $dir = dirname($this->cacheFile());
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($this->cacheFile(), json_encode($data));
        } catch (\Throwable) {
            // Best-effort disk cache — write failure must not break list().
        }
    }

    public function list(): array
    {
        if (self::$memo !== null && microtime(true) - self::$memo['at'] < self::CATALOG_TTL_SECONDS) {
            return self::$memo['data'];
        }

        $disk = $this->readDiskCache();
        if ($disk !== null) {
            self::$memo = ['at' => microtime(true), 'data' => $disk];

            return $disk;
        }

        // No cache yet → fetch the GitHub tree once.
        try {
            $response = Http::timeout(self::TREE_FETCH_TIMEOUT_SECONDS)
                ->withHeaders(['Accept' => 'application/vnd.github+json'])
                ->get(config('fe_templates.tree_url'));
            if (! $response->successful()) {
                throw new AppException("HTTP {$response->status()}", 502);
            }
            $data = $this->parseTree($response->json('tree') ?? []);
            if (count($data) === 0) {
                throw new AppException('katalog kosong', 502);
            }
            self::$memo = ['at' => microtime(true), 'data' => $data];
            $this->writeDiskCache($data);

            return $data;
        } catch (\Throwable $e) {
            // Fallback to the curated catalog so the UI keeps working offline.
            logger()->error("Fetch katalog opentailwind gagal, pakai fallback kurasi: {$e->getMessage()}");
            $fallback = config('fe_templates.templates');
            self::$memo = ['at' => microtime(true), 'data' => $fallback];

            return $fallback;
        }
    }

    public function categories(): array
    {
        $categories = array_values(array_unique(array_column($this->list(), 'category')));
        sort($categories);

        return $categories;
    }

    public function paginate(array $filter, ?string $pinSlug = null): array
    {
        $qName = strtolower(trim((string) ($filter['q_name'] ?? '')));
        $qCategory = trim((string) ($filter['q_category'] ?? ''));

        $filtered = array_values(array_filter($this->list(), function (array $t) use ($qName, $qCategory) {
            $okName = $qName === ''
                || str_contains(strtolower($t['name']), $qName)
                || str_contains(strtolower($t['slug']), $qName);
            $okCat = $qCategory === '' || $t['category'] === $qCategory;

            return $okName && $okCat;
        }));

        // Pin the active template to the front (when it passes the filter) so
        // the admin sees the current choice on the first page.
        if ($pinSlug !== null && $pinSlug !== '') {
            foreach ($filtered as $i => $t) {
                if ($t['slug'] === $pinSlug) {
                    if ($i > 0) {
                        array_splice($filtered, $i, 1);
                        array_unshift($filtered, $t);
                    }
                    break;
                }
            }
        }

        $pageSize = (int) ($filter['q_page_size'] ?? 12);
        $pageSize = $pageSize > 0 ? $pageSize : 12;
        $page = (int) ($filter['q_page'] ?? 1);
        $page = $page > 0 ? $page : 1;

        $total = count($filtered);
        $totalPage = max(1, (int) ceil($total / $pageSize));
        $page = min($page, $totalPage);
        $offset = ($page - 1) * $pageSize;
        $datas = array_slice($filtered, $offset, $pageSize);

        $window = 2;
        $pages = range(max(1, $page - $window), min($totalPage, $page + $window));

        return [
            'datas' => $datas,
            'paginate_data' => [
                'current_page' => $page,
                'total_page' => $totalPage,
                'page_size' => $pageSize,
                'total' => $total,
                'pages' => $pages,
                'offset' => $offset,
            ],
        ];
    }

    public function has(string $slug): bool
    {
        foreach ($this->list() as $t) {
            if ($t['slug'] === $slug) {
                return true;
            }
        }

        return false;
    }

    /** Read template HTML from the local cache when present & valid. */
    private function readLocalHtml(string $slug): ?string
    {
        try {
            $file = $this->localHtmlFile($slug);
            if (! file_exists($file)) {
                return null;
            }
            $html = (string) file_get_contents($file);

            return stripos($html, '</html>') !== false ? $html : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function previewHtml(string $slug): string
    {
        if (! $this->has($slug)) {
            throw new AppException('Template tidak dikenali', 400);
        }

        // 1) Local cache first — instant, no network/rate-limit dependency.
        $local = $this->readLocalHtml($slug);
        if ($local !== null) {
            return $local;
        }

        // 2) Fetch upstream with a timeout so slow GitHub does not hang us.
        $url = config('fe_templates.base_url')."/{$slug}.html";
        try {
            $response = Http::timeout(self::FETCH_TIMEOUT_SECONDS)->get($url);
            if (! $response->successful()) {
                throw new AppException("HTTP {$response->status()}", 502);
            }
            $html = $response->body();
            if (stripos($html, '</html>') === false) {
                throw new AppException('HTML tidak valid', 502);
            }

            return $html;
        } catch (\Throwable $e) {
            // 3) Last fallback: local cache (in case it appeared meanwhile).
            $fallback = $this->readLocalHtml($slug);
            if ($fallback !== null) {
                return $fallback;
            }
            throw new AppException("Gagal mengambil preview: {$e->getMessage()}", 502);
        }
    }
}
