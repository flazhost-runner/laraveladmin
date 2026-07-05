<?php

/*
|--------------------------------------------------------------------------
| Frontend (landing) template catalog — curated from opentailwind
|--------------------------------------------------------------------------
| (https://github.com/lindoai/opentailwind, MIT). Mirrors NodeAdmin's
| src/config/feTemplates.ts. Each template is self-contained (HTML +
| Tailwind v4 CDN) and downloaded on-demand when the admin selects it
| (see Modules\Home\app\Services\FeTemplateService).
|
| Special slug 'default' renders the local Blade landing view (fe/default,
| landing v6) instead of a downloaded raw HTML file.
*/

return [

    // Raw GitHub base URL for on-demand template downloads.
    'base_url' => 'https://raw.githubusercontent.com/lindoai/opentailwind/master/landings',

    // GitHub API tree (recursive) listing all 640 landings.
    'tree_url' => 'https://api.github.com/repos/lindoai/opentailwind/git/trees/master?recursive=1',

    // Local cache folder (relative to storage/app). Lives under the runtime
    // writable dir — NOT under public/, so builds never touch the cache.
    'dir' => 'fe/templates',

    // Catalog disk cache (the 640 list) from the GitHub tree fetch.
    'catalog_file' => 'fe/templates/_catalog.json',

    // opentailwind slug pattern: `{category}-{NNN}-{name}` (category may
    // contain hyphens, e.g. `agency-consulting`). Used by the validator
    // (anti-SSRF: fixed charset a-z0-9- + fixed structure) and to derive
    // display metadata.
    'slug_re' => '/^([a-z]+(?:-[a-z]+)*)-(\d{3})-([a-z0-9-]+)$/',

    // Special slug: render the bundled Blade landing view (fe/default,
    // landing v6) instead of serving a cached opentailwind HTML file.
    'default_view' => 'default',

    // Default active template (matches NodeAdmin DEFAULT_FE_TEMPLATE).
    'default' => 'agency-consulting-002-creative-agency',

    // Curated catalog (~15 of 640 opentailwind landings) — offline fallback.
    'templates' => [
        ['slug' => 'agency-consulting-002-creative-agency', 'name' => 'Creative Agency', 'category' => 'Agency'],
        ['slug' => 'agency-consulting-001-digital-marketing-agency', 'name' => 'Digital Marketing Agency', 'category' => 'Agency'],
        ['slug' => 'technology-saas-001-hero-focused-conversion-page', 'name' => 'SaaS — Hero Focused', 'category' => 'Technology'],
        ['slug' => 'technology-saas-002-feature-rich-multi-section', 'name' => 'SaaS — Feature Rich', 'category' => 'Technology'],
        ['slug' => 'ecommerce-retail-001-fashion-boutique', 'name' => 'Fashion Boutique', 'category' => 'E-commerce'],
        ['slug' => 'ecommerce-retail-002-luxury-fashion-brand', 'name' => 'Luxury Fashion', 'category' => 'E-commerce'],
        ['slug' => 'portfolio-creative-001-creative-portfolio', 'name' => 'Creative Portfolio', 'category' => 'Portfolio'],
        ['slug' => 'portfolio-creative-002-minimal-portfolio', 'name' => 'Minimal Portfolio', 'category' => 'Portfolio'],
        ['slug' => 'professional-services-001-law-firm', 'name' => 'Law Firm', 'category' => 'Professional'],
        ['slug' => 'real-estate-property-001-real-estate-agency', 'name' => 'Real Estate Agency', 'category' => 'Real Estate'],
        ['slug' => 'food-hospitality-001-fine-dining-restaurant', 'name' => 'Fine Dining', 'category' => 'Food'],
        ['slug' => 'healthcare-wellness-001-family-doctor-clinic', 'name' => 'Family Clinic', 'category' => 'Healthcare'],
        ['slug' => 'education-training-001-private-school', 'name' => 'Private School', 'category' => 'Education'],
        ['slug' => 'fitness-sports-001-fitness-center', 'name' => 'Fitness Center', 'category' => 'Fitness'],
        ['slug' => 'travel-tourism-001-travel-agency', 'name' => 'Travel Agency', 'category' => 'Travel'],
    ],
];
