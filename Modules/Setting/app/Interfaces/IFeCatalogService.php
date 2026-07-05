<?php

namespace Modules\Setting\app\Interfaces;

interface IFeCatalogService
{
    /** Full catalog (640 opentailwind landings; curated fallback offline). */
    public function list(): array;

    /** Sorted unique categories for the filter UI. */
    public function categories(): array;

    /**
     * Server-side filter (q_name / q_category) + pagination (q_page /
     * q_page_size). $pinSlug pins the active template to page 1.
     * Returns ['datas' => [...], 'paginate_data' => [...]].
     */
    public function paginate(array $filter, ?string $pinSlug = null): array;

    /** True when the slug exists in the catalog (anti-SSRF whitelist). */
    public function has(string $slug): bool;

    /** Raw HTML of one template (for thumbnails/preview modal). */
    public function previewHtml(string $slug): string;
}
