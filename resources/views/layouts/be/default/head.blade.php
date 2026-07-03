<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $setting?->name ?? 'LaravelAdmin' }} — @yield('title', 'Admin')</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary:       'var(--primary)',
                        secondary:     'var(--secondary)',
                        'theme-light': 'var(--theme-light)',
                        'theme-dark':  'var(--theme-dark)',
                    }
                }
            }
        }
    </script>

    <!-- Theme CSS Variables (driven by $theme from DB) -->
    <style>
        :root {
            --primary:     {{ $theme['primary']   ?? '#3B82F6' }};
            --secondary:   {{ $theme['secondary'] ?? '#60A5FA' }};
            --theme-light: {{ $theme['light']     ?? '#DBEAFE' }};
            --theme-dark:  {{ $theme['dark']      ?? '#1E40AF' }};
        }
        body { background: linear-gradient(135deg, var(--theme-light) 0%, #f8fafc 100%); min-height: 100vh; }
        .sidebar-gradient { background: linear-gradient(180deg, var(--theme-dark) 0%, color-mix(in srgb, var(--theme-dark) 80%, black) 100%); }
    </style>
    <style type="text/tailwindcss">
        @layer components {
            .tw-card { background: white; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; }
            .btn { display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; cursor: pointer; border: 1px solid transparent; transition: all 0.15s; text-decoration: none; }
            .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
            .btn-xs { padding: 0.125rem 0.375rem; font-size: 0.7rem; }
            .btn-primary-tw { background: var(--primary); color: white; border-color: var(--primary); }
            .btn-primary-tw:hover { opacity: 0.9; color: white; }
            .btn-primary { background: var(--primary); color: white; border-color: var(--primary); }
            .btn-primary:hover { opacity: 0.9; }
            .btn-info { background: #0ea5e9; color: white; border-color: #0ea5e9; }
            .btn-info:hover { background: #0284c7; }
            .btn-outline-dark { background: transparent; color: #374151; border-color: #374151; }
            .btn-outline-dark:hover { background: #374151; color: white; }
            .text-primary-tw { color: var(--primary); }
            .btn-success { background: #10b981; color: white; border-color: #10b981; }
            .btn-success:hover { background: #059669; }
            .btn-danger { background: #ef4444; color: white; border-color: #ef4444; }
            .btn-danger:hover { background: #dc2626; }
            .btn-warning { background: #f59e0b; color: white; border-color: #f59e0b; }
            .btn-warning:hover { background: #d97706; }
            .btn-secondary { background: #6b7280; color: white; border-color: #6b7280; }
            .btn-secondary:hover { background: #4b5563; }
            .btn-group { display: inline-flex; gap: 0; }
            .btn-group .btn { border-radius: 0; }
            .btn-group .btn:first-child { border-radius: 0.375rem 0 0 0.375rem; }
            .btn-group .btn:last-child { border-radius: 0 0.375rem 0.375rem 0; }
            .form-control { display: block; width: 100%; padding: 0.375rem 0.75rem; font-size: 0.875rem; border: 1px solid #d1d5db; border-radius: 0.375rem; background: white; transition: border-color 0.15s; }
            .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 20%, transparent); }
            .form-label { display: block; margin-bottom: 0.25rem; font-size: 0.875rem; font-weight: 500; color: #374151; }
            .is-invalid { border-color: #ef4444 !important; }
            .invalid-feedback { color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; }
            .form-check-input { width: 1rem; height: 1rem; margin-top: 0.125rem; }
            .table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
            .table th, .table td { padding: 0.5rem 0.75rem; text-align: left; }
            .table-bordered th, .table-bordered td { border: 1px solid #e5e7eb; }
            .table-hover tbody tr:hover { background: #f9fafb; }
            .table thead th { background: #f3f4f6; font-weight: 600; color: #374151; }
            .align-middle td, .align-middle th { vertical-align: middle; }
            .badge { display: inline-flex; align-items: center; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
            .text-bg-primary { background: var(--primary); color: white; }
            .text-bg-success { background: #10b981; color: white; }
            .text-bg-danger { background: #ef4444; color: white; }
            .text-bg-warning { background: #f59e0b; color: white; }
            .text-bg-secondary { background: #6b7280; color: white; }
            .pagination { display: flex; gap: 0.25rem; align-items: center; }
            .page-item {}
            .page-link { padding: 0.25rem 0.5rem; border: 1px solid #e5e7eb; border-radius: 0.25rem; color: var(--primary); text-decoration: none; font-size: 0.8rem; }
            .page-link:hover { background: #f3f4f6; }
            .page-item.active .page-link { background: var(--primary); color: white; border-color: var(--primary); }
            .page-item.disabled .page-link { color: #9ca3af; cursor: not-allowed; }
            .dropdown { position: relative; display: inline-block; }
            .dropdown-menu { position: absolute; right: 0; z-index: 50; min-width: 10rem; background: white; border: 1px solid #e5e7eb; border-radius: 0.375rem; box-shadow: 0 10px 15px rgba(0,0,0,0.1); padding: 0.25rem 0; display: none; }
            .dropdown-menu.show { display: block; }
            .dropdown-item { display: block; padding: 0.375rem 1rem; font-size: 0.875rem; color: #374151; text-decoration: none; cursor: pointer; border: none; background: none; width: 100%; text-align: left; }
            .dropdown-item:hover { background: #f3f4f6; }
            .dropdown-item.text-danger, .dropdown-item.danger { color: #ef4444; }
            .dropdown-item.danger:hover { background: #fef2f2; color: #dc2626; }
            .dropdown-divider { height: 1px; background: #e5e7eb; margin: 0.25rem 0; }
            .alert { padding: 0.75rem 1rem; border-radius: 0.375rem; margin-bottom: 1rem; border: 1px solid transparent; }
            .alert-danger { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
            .alert-success { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
            .alert-info { background: #eff6ff; color: #1e40af; border-color: #bfdbfe; }
            .alert-warning { background: #fffbeb; color: #92400e; border-color: #fde68a; }
            .alert-primary { background: color-mix(in srgb, var(--primary) 10%, white); color: var(--primary); border-color: color-mix(in srgb, var(--primary) 30%, white); }
            .nav-link-tw { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; color: rgba(255,255,255,0.8); text-decoration: none; border-radius: 0.375rem; transition: all 0.15s; font-size: 0.875rem; }
            .nav-link-tw:hover, .nav-link-tw.active { background: rgba(255,255,255,0.1); color: white; }
            .d-flex { display: flex; }
            .align-items-center { align-items: center; }
            .justify-content-between { justify-content: space-between; }
            .me-2 { margin-right: 0.5rem; }
            .ms-2 { margin-left: 0.5rem; }
            .mb-3 { margin-bottom: 0.75rem; }
            .gap-2 { gap: 0.5rem; }
            .row { display: flex; flex-wrap: wrap; gap: 1rem; }
            .col { flex: 1; min-width: 0; }
            .col-md-6 { flex: 0 0 calc(50% - 0.5rem); max-width: calc(50% - 0.5rem); }
            .col-md-4 { flex: 0 0 calc(33.333% - 0.667rem); max-width: calc(33.333% - 0.667rem); }
            .col-md-3 { flex: 0 0 calc(25% - 0.75rem); max-width: calc(25% - 0.75rem); }
            @media (max-width: 768px) {
                .col-md-6, .col-md-4, .col-md-3 { flex: 0 0 100%; max-width: 100%; }
            }
            /* tb-fm-* File Manager classes */
            .tb-fm-main { display: grid; grid-template-columns: 200px 1fr; gap: 0; height: 400px; border: 1px solid #e5e7eb; border-radius: 0.375rem; overflow: hidden; }
            .tb-fm-nav { background: #f9fafb; border-right: 1px solid #e5e7eb; padding: 0.5rem; overflow-y: auto; }
            .tb-fm-nav-item { display: block; padding: 0.375rem 0.75rem; border-radius: 0.25rem; font-size: 0.875rem; cursor: pointer; color: #374151; text-decoration: none; }
            .tb-fm-nav-item:hover, .tb-fm-nav-item.active { background: var(--primary); color: white; }
            .tb-fm-content { padding: 1rem; overflow-y: auto; }
            .tb-fm-media-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 0.5rem; }
            .tb-fm-media-item { position: relative; aspect-ratio: 1; border: 2px solid transparent; border-radius: 0.375rem; overflow: hidden; cursor: pointer; }
            .tb-fm-media-item:hover { border-color: var(--primary); }
            .tb-fm-media-item.selected { border-color: var(--primary); }
            .tb-fm-media-item img { width: 100%; height: 100%; object-fit: cover; }
            .tb-fm-upload-btn { margin-bottom: 1rem; }
            .tb-fm-footer { padding: 0.75rem 1rem; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 0.5rem; }
        }
    </style>

    <!-- Font Awesome (local) -->
    <link rel="stylesheet" href="{{ asset('be/default/vendor/fontawesome-free/css/all.min.css') }}">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @stack('trumbowyg-styles')
    @stack('head-styles')
</head>
