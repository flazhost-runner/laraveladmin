<?php

namespace Modules\Home\app\Interfaces;

interface IFeTemplateService
{
    /** True when the template HTML is already cached locally. */
    public function isCached(string $slug): bool;

    /** Validate a slug: the special 'default' view or the opentailwind pattern (anti-SSRF). */
    public function isValidSlug(?string $slug): bool;

    /** Active template slug from Setting (fallback to the default template). */
    public function getActiveSlug(): string;

    /** True when the slug is 'default' — rendered via the local Blade landing view. */
    public function isDefaultView(string $slug): bool;

    /** Make sure the template exists locally (download on-demand). Throws AppException on failure. */
    public function ensure(string $slug): void;

    /** Raw HTML of the active landing, or null when the local Blade view should render. */
    public function getActiveHtml(): ?string;
}
