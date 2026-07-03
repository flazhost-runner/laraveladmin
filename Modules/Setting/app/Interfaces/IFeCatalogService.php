<?php

namespace Modules\Setting\app\Interfaces;

interface IFeCatalogService
{
    public function getCatalog(array $filter): array; // paginated list with meta

    public function previewHtml(string $slug): string; // proxy + cache

    public function ensure(string $slug): void;       // download + cache locally
}
