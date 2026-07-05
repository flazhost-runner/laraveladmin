@extends('layouts.be.default.main')
@section('title', 'Settings')
@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Setting Management</h1>
</div>

{{-- FE catalog search form (GET, separate from the POST setting form).
     Inputs inside the Frontend Template card associate via form="fe_search". --}}
<form id="fe_search" method="GET" action="{{ route('admin.v1.setting.index') }}"></form>

<form method="POST" action="{{ route('admin.v1.setting.update') }}?_method=PUT" enctype="multipart/form-data">
@csrf

    {{-- ===== Admin Theme Switcher ===== --}}
    <div class="tw-card p-6 mb-6">
        <div class="flex items-center gap-2 mb-1">
            <i class="fas fa-palette" style="color:var(--primary)"></i>
            <h2 class="text-lg font-bold" style="color:var(--primary)">Admin Theme</h2>
        </div>
        <p class="text-sm text-gray-500 mb-4">Choose a theme — admin appearance will update after saving.</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            @foreach($themes as $themeKey => $colors)
            @php $isTheme = strtolower($setting->theme ?? 'blue') === $themeKey; @endphp
            <label class="cursor-pointer block">
                <input type="radio" name="theme" value="{{ $themeKey }}" class="sr-only theme-radio" {{ $isTheme ? 'checked' : '' }}>
                <div class="theme-swatch rounded-xl overflow-hidden border-2 transition {{ $isTheme ? 'border-gray-800' : 'border-transparent' }}" style="box-shadow:0 4px 10px rgba(0,0,0,.08)">
                    <div class="h-16 flex">
                        <div class="flex-1" style="background:{{ $colors['dark'] }}"></div>
                        <div class="flex-1" style="background:{{ $colors['primary'] }}"></div>
                        <div class="flex-1" style="background:{{ $colors['secondary'] }}"></div>
                        <div class="flex-1" style="background:{{ $colors['light'] }}"></div>
                    </div>
                    <div class="bg-white py-2 px-3 flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-700 capitalize">{{ $themeKey }}</span>
                        <i class="fas fa-check-circle check-icon {{ $isTheme ? '' : 'hidden' }}" style="color:{{ $colors['primary'] }}"></i>
                    </div>
                </div>
            </label>
            @endforeach
        </div>
    </div>

    {{-- ===== Frontend Template Switcher (catalog 640, paginated + search) ===== --}}
    <div class="tw-card p-6 mb-6">
        <div class="flex items-center gap-2 mb-1">
            <i class="fas fa-window-maximize" style="color:var(--primary)"></i>
            <h2 class="text-lg font-bold" style="color:var(--primary)">Frontend Template</h2>
        </div>
        <p class="text-sm text-gray-500 mb-4">
            Pilih desain halaman depan (landing) publik dari
            <a href="https://github.com/lindoai/opentailwind" target="_blank" class="underline">opentailwind</a>
            ({{ $paginateData['total'] }} template). Klik <b>Preview</b> untuk lihat penuh.
            Template terpilih diunduh &amp; di-cache saat <b>Save</b>. Lihat hasilnya di
            <a href="/" target="_blank" class="underline" style="color:var(--primary)">halaman depan ↗</a>.
        </p>

        {{-- Selected slug (submitted with the POST setting form). Persisted via
             localStorage so it survives catalog page changes. --}}
        <input type="hidden" id="fe_template_input" name="fe_template" value="{{ $feActive }}">

        {{-- Search + category filter (GET, server-side) --}}
        <div class="flex flex-wrap items-end gap-2 mb-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Cari nama</label>
                <input form="fe_search" type="text" name="q_name" value="{{ $filter['q_name'] ?? '' }}"
                       placeholder="mis. agency, saas…" class="form-control" style="min-width:220px">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Kategori</label>
                <select form="fe_search" name="q_category" class="form-control" style="min-width:200px">
                    <option value="">Semua kategori</option>
                    @foreach($feCategories as $cat)
                    <option value="{{ $cat }}" {{ ($filter['q_category'] ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <input form="fe_search" type="hidden" name="q_page_size" value="{{ $paginateData['page_size'] }}">
            <button form="fe_search" type="submit" class="btn btn-success btn-sm" style="height:38px">
                <i class="fas fa-search me-1"></i> Cari
            </button>
            <a href="{{ route('admin.v1.setting.index') }}" class="btn btn-danger btn-sm" style="height:38px">
                <i class="fas fa-times me-1"></i> Reset
            </a>
        </div>

        @if(count($feTemplates) === 0)
        <div class="text-center text-gray-400 py-10">
            <i class="fas fa-search fa-2x mb-2"></i>
            <p>Tidak ada template yang cocok dengan pencarian.</p>
        </div>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @foreach($feTemplates as $tpl)
            <div class="fe-card block" data-slug="{{ $tpl['slug'] }}">
                <div class="fe-swatch rounded-xl overflow-hidden border-2 transition {{ $feActive === $tpl['slug'] ? 'border-gray-900' : 'border-gray-300' }}"
                     style="box-shadow:0 2px 8px rgba(0,0,0,.12)">
                    {{-- Thumbnail: click → full preview (localStorage cache, lazy via IntersectionObserver) --}}
                    <div class="fe-thumb fe-preview-trigger relative bg-gray-100 cursor-pointer group" data-slug="{{ $tpl['slug'] }}"
                         data-name="{{ $tpl['name'] }}"
                         style="height:140px;overflow:hidden;border-bottom:1px solid #d1d5db;border-top-left-radius:.7rem;border-top-right-radius:.7rem;transform:translateZ(0)"
                         data-preview-url="{{ route('admin.v1.setting.fe_preview', $tpl['slug']) }}">
                        <div class="fe-thumb-placeholder absolute inset-0 flex items-center justify-center text-gray-300">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        {{-- Preview hint overlay on hover --}}
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition"
                             style="background:rgba(0,0,0,.45);pointer-events:none">
                            <span class="text-white text-sm font-semibold"><i class="fas fa-eye me-1"></i> Preview</span>
                        </div>
                    </div>
                    <div class="bg-white py-2 px-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-800 truncate" title="{{ $tpl['name'] }}">{{ $tpl['name'] }}</span>
                            <i class="fas fa-check-circle fe-check {{ $feActive === $tpl['slug'] ? '' : 'hidden' }}" style="color:var(--primary)"></i>
                        </div>
                        <span class="text-xs text-gray-400">{{ $tpl['category'] }}</span>
                        <button type="button" class="fe-select btn btn-sm w-100 mt-2 fw-bold {{ $feActive === $tpl['slug'] ? 'btn-primary-tw' : 'btn-outline-dark' }}" style="font-size:13px;letter-spacing:.3px">
                            @if($feActive === $tpl['slug'])<i class="fas fa-check me-1"></i> TERPILIH @else <i class="fas fa-hand-pointer me-1"></i> PILIH @endif
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Catalog pagination --}}
        @if($paginateData['total_page'] > 1)
        @php
            $pageUrl = fn (int $p) => route('admin.v1.setting.index', array_merge(array_filter($filter, fn ($v) => $v !== null && $v !== ''), ['q_page' => $p]));
            $firstPage = $paginateData['pages'][0];
            $lastPage = end($paginateData['pages']);
        @endphp
        <div class="d-flex justify-content-center mt-5">
            <nav>
                <ul class="pagination">
                    @if($paginateData['current_page'] > 1)
                    <li class="page-item"><a class="page-link" href="{{ $pageUrl($paginateData['current_page'] - 1) }}">Previous</a></li>
                    @endif
                    @if($firstPage > 1)
                    <li class="page-item"><a class="page-link" href="{{ $pageUrl(1) }}">1</a></li>
                    @if($firstPage > 2)<li class="page-item disabled"><span class="page-link">…</span></li>@endif
                    @endif
                    @foreach($paginateData['pages'] as $page)
                    <li class="page-item {{ $page === $paginateData['current_page'] ? 'active' : '' }}"><a class="page-link" href="{{ $pageUrl($page) }}">{{ $page }}</a></li>
                    @endforeach
                    @if($lastPage < $paginateData['total_page'])
                    @if($lastPage < $paginateData['total_page'] - 1)<li class="page-item disabled"><span class="page-link">…</span></li>@endif
                    <li class="page-item"><a class="page-link" href="{{ $pageUrl($paginateData['total_page']) }}">{{ $paginateData['total_page'] }}</a></li>
                    @endif
                    @if($paginateData['current_page'] < $paginateData['total_page'])
                    <li class="page-item"><a class="page-link" href="{{ $pageUrl($paginateData['current_page'] + 1) }}">Next</a></li>
                    @endif
                </ul>
            </nav>
        </div>
        @endif
    </div>

    {{-- ===== Setting Form ===== --}}
    <div class="tw-card p-6">
        <h2 class="text-lg font-bold mb-4" style="color:var(--primary)">Setting Form</h2>

        <div class="grid md:grid-cols-2 gap-4">
            {{-- Initial --}}
            <div>
                <label class="form-label" for="initial">Initial</label>
                <input type="text" id="initial" name="initial" maxlength="10"
                       class="form-control @error('initial') is-invalid @enderror"
                       value="{{ old('initial', $setting->initial) }}" placeholder="e.g. LA">
                @error('initial')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Name --}}
            <div>
                <label class="form-label" for="name">App Name</label>
                <input type="text" id="name" name="name" maxlength="200"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $setting->name) }}" placeholder="My Site">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Phone --}}
            <div>
                <label class="form-label" for="phone">Phone</label>
                <input type="text" id="phone" name="phone" maxlength="50"
                       class="form-control @error('phone') is-invalid @enderror"
                       value="{{ old('phone', $setting->phone) }}" placeholder="+62 ...">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" maxlength="200"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $setting->email) }}" placeholder="info@example.com">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Copyright --}}
            <div>
                <label class="form-label" for="copyright">Copyright</label>
                <input type="text" id="copyright" name="copyright" maxlength="200"
                       class="form-control @error('copyright') is-invalid @enderror"
                       value="{{ old('copyright', $setting->copyright) }}" placeholder="2026 My Company">
                @error('copyright')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Address --}}
            <div>
                <label class="form-label" for="address">Address</label>
                <input type="text" id="address" name="address" maxlength="500"
                       class="form-control @error('address') is-invalid @enderror"
                       value="{{ old('address', $setting->address) }}" placeholder="Jl. ...">
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Description --}}
        <div class="mt-4">
            <label class="form-label" for="description">Description</label>
            <textarea id="description" name="description"
                      class="form-control trumbowyg-editor @error('description') is-invalid @enderror"
                      rows="4" placeholder="Short description of your site..."
            >{{ old('description', $setting->description) }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- File Uploads --}}
        <div class="grid md:grid-cols-3 gap-4 mt-4">
            {{-- Icon --}}
            <div>
                <label class="form-label" for="icon">Icon (favicon)</label>
                <input type="file" id="icon" name="icon" accept="image/*"
                       class="form-control @error('icon') is-invalid @enderror"
                       onchange="previewFile(this, 'icon-preview')">
                @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="mt-2">
                    @if($setting->icon)
                    <img id="icon-preview"
                         src="{{ Str::startsWith($setting->icon, 'http') ? $setting->icon : asset('storage/' . $setting->icon) }}"
                         alt="icon preview" class="h-10 w-10 object-contain border rounded">
                    @else
                    <img id="icon-preview" src="" alt="icon preview" class="h-10 w-10 object-contain border rounded hidden">
                    @endif
                </div>
            </div>

            {{-- Logo --}}
            <div>
                <label class="form-label" for="logo">Company Logo</label>
                <input type="file" id="logo" name="logo" accept="image/*"
                       class="form-control @error('logo') is-invalid @enderror"
                       onchange="previewFile(this, 'logo-preview')">
                @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="mt-2">
                    @if($setting->logo)
                    <img id="logo-preview"
                         src="{{ Str::startsWith($setting->logo, 'http') ? $setting->logo : asset('storage/' . $setting->logo) }}"
                         alt="logo preview" class="h-10 object-contain border rounded">
                    @else
                    <img id="logo-preview" src="" alt="logo preview" class="h-10 object-contain border rounded hidden">
                    @endif
                </div>
            </div>

            {{-- Login Image --}}
            <div>
                <label class="form-label" for="login_image">Login Image</label>
                <input type="file" id="login_image" name="login_image" accept="image/*"
                       class="form-control @error('login_image') is-invalid @enderror"
                       onchange="previewFile(this, 'login-image-preview')">
                @error('login_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="mt-2">
                    @if($setting->login_image)
                    <img id="login-image-preview"
                         src="{{ Str::startsWith($setting->login_image, 'http') ? $setting->login_image : asset('storage/' . $setting->login_image) }}"
                         alt="login image preview" class="h-16 object-contain border rounded">
                    @else
                    <img id="login-image-preview" src="" alt="login image preview" class="h-16 object-contain border rounded hidden">
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="btn btn-primary px-4 py-2">
                <i class="fas fa-save me-1"></i> Save Setting
            </button>
        </div>
    </div>
</form>

{{-- Full preview modal for FE templates --}}
<div id="fe-preview-modal" class="hidden fixed inset-0 z-50 items-center justify-center" style="background:rgba(0,0,0,.6)">
    <div class="bg-white rounded-xl overflow-hidden shadow-2xl" style="width:92vw;height:90vh;display:flex;flex-direction:column">
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <h3 id="fe-preview-title" class="font-bold text-gray-800">Preview</h3>
            <button id="fe-preview-close" type="button" class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Tutup</button>
        </div>
        <iframe id="fe-preview-frame" class="flex-1 w-full" style="border:0"></iframe>
    </div>
</div>

@push('foot-scripts')
<script>
(function () {
    var THEMES = {
        @foreach($themes as $themeKey => $colors)
        "{{ $themeKey }}": { primary: "{{ $colors['primary'] }}", secondary: "{{ $colors['secondary'] }}", light: "{{ $colors['light'] }}", dark: "{{ $colors['dark'] }}" }{{ $loop->last ? '' : ',' }}
        @endforeach
    };
    function applyTheme(name) {
        var t = THEMES[name];
        if (!t) return;
        var root = document.documentElement;
        root.style.setProperty('--primary', t.primary);
        root.style.setProperty('--secondary', t.secondary);
        root.style.setProperty('--theme-light', t.light);
        root.style.setProperty('--theme-dark', t.dark);
        document.querySelectorAll('.theme-swatch').forEach(function (sw) {
            sw.classList.remove('border-gray-800'); sw.classList.add('border-transparent');
        });
        document.querySelectorAll('.check-icon').forEach(function (ic) { ic.classList.add('hidden'); });
        document.querySelectorAll('.theme-radio').forEach(function (r) {
            if (r.value === name) {
                var sw = r.parentElement.querySelector('.theme-swatch');
                var ic = r.parentElement.querySelector('.check-icon');
                if (sw) { sw.classList.remove('border-transparent'); sw.classList.add('border-gray-800'); }
                if (ic) ic.classList.remove('hidden');
            }
        });
    }
    document.querySelectorAll('.theme-radio').forEach(function (r) {
        r.addEventListener('change', function () { applyTheme(r.value); });
    });

    // ===== Frontend Template catalog: HTML cached in localStorage =====
    var LS_PREFIX = 'fe_tpl_html:';   // per-slug HTML cache
    var LS_SEL = 'fe_tpl_selected';   // selected slug (persists across pages)
    var input = document.getElementById('fe_template_input');

    // Restore the selection from localStorage (e.g. after a catalog page change)
    var savedSel = localStorage.getItem(LS_SEL);
    if (savedSel && input) input.value = savedSel;

    // opentailwind templates use the Tailwind v4 `dark:` variant which follows
    // prefers-color-scheme by default. Force light in previews: (1) redefine the
    // `dark` variant as class-based via <style type="text/tailwindcss"> — the
    // iframe has no .dark so it stays light; (2) override color-scheme so native
    // form controls/scrollbars do not go dark either.
    function forceLight(html) {
        var inject =
            '<meta name="color-scheme" content="light">' +
            '<style type="text/tailwindcss">@custom-variant dark (&:where(.dark, .dark *));</style>' +
            '<style>:root{color-scheme:light !important}' +
            '@media (prefers-color-scheme: dark){:root{color-scheme:light !important}}</style>';
        if (/<head[^>]*>/i.test(html)) {
            return html.replace(/<head[^>]*>/i, function (m) { return m + inject; });
        }
        return inject + html;
    }

    // Get one template's HTML: from localStorage or fetch from server then cache.
    function getHtml(slug, url) {
        var cached = null;
        try { cached = localStorage.getItem(LS_PREFIX + slug); } catch (e) {}
        if (cached) return Promise.resolve(cached);
        return fetch(url, { credentials: 'same-origin' })
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.text(); })
            .then(function (html) {
                try { localStorage.setItem(LS_PREFIX + slug, html); } catch (e) { /* quota full: skip */ }
                return html;
            });
    }

    // Render a thumbnail iframe (scaled down so it looks like a screenshot).
    function renderThumb(box) {
        var slug = box.getAttribute('data-slug');
        var url = box.getAttribute('data-preview-url');
        getHtml(slug, url).then(function (html) {
            var ph = box.querySelector('.fe-thumb-placeholder');
            if (ph) ph.remove();
            var ifr = document.createElement('iframe');
            ifr.setAttribute('scrolling', 'no');
            ifr.setAttribute('loading', 'lazy');
            // Dynamic scale = card width / 1280 → thumbnail fills the card width
            // edge-to-edge; the 1280px-wide render is clipped to 140px height.
            var DESIGN_W = 1280;
            var scale = (box.clientWidth || 280) / DESIGN_W;
            ifr.style.cssText = 'width:' + DESIGN_W + 'px;height:' + Math.ceil(140 / scale) +
                'px;border:0;transform:scale(' + scale + ');transform-origin:top left;pointer-events:none';
            ifr.srcdoc = forceLight(html);
            box.appendChild(ifr);
        }).catch(function () {
            var ph = box.querySelector('.fe-thumb-placeholder');
            if (ph) ph.innerHTML = '<i class="fas fa-image fa-2x"></i>';
        });
    }

    // Lazy-load thumbnails as cards become visible (saves bandwidth & CPU).
    var thumbs = document.querySelectorAll('.fe-thumb');
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) { renderThumb(en.target); io.unobserve(en.target); }
            });
        }, { rootMargin: '200px' });
        thumbs.forEach(function (t) { io.observe(t); });
    } else {
        thumbs.forEach(renderThumb);
    }

    // Select a template → set hidden input + persist to localStorage + update UI.
    function selectSlug(slug) {
        if (input) input.value = slug;
        try { localStorage.setItem(LS_SEL, slug); } catch (e) {}
        document.querySelectorAll('.fe-card').forEach(function (card) {
            var active = card.getAttribute('data-slug') === slug;
            var swatch = card.querySelector('.fe-swatch');
            var check = card.querySelector('.fe-check');
            var btn = card.querySelector('.fe-select');
            swatch.classList.toggle('border-gray-900', active);
            swatch.classList.toggle('border-gray-300', !active);
            if (check) check.classList.toggle('hidden', !active);
            if (btn) {
                btn.innerHTML = active
                    ? '<i class="fas fa-check me-1"></i> TERPILIH'
                    : '<i class="fas fa-hand-pointer me-1"></i> PILIH';
                btn.classList.toggle('btn-primary-tw', active);
                btn.classList.toggle('btn-outline-dark', !active);
            }
        });
    }

    document.querySelectorAll('.fe-select').forEach(function (b) {
        b.addEventListener('click', function () {
            selectSlug(this.closest('.fe-card').getAttribute('data-slug'));
        });
    });
    // Sync the initial view with the stored selection.
    if (input && input.value) selectSlug(input.value);

    // On Save, the submitted selection becomes the server-side truth — drop the
    // sticky localStorage selection so future visits reflect the DB value.
    var settingForm = input ? input.closest('form') : null;
    if (settingForm) {
        settingForm.addEventListener('submit', function () {
            try { localStorage.removeItem(LS_SEL); } catch (e) {}
        });
    }

    // ===== Full preview modal =====
    var modal = document.getElementById('fe-preview-modal');
    var frame = document.getElementById('fe-preview-frame');
    var title = document.getElementById('fe-preview-title');
    function openModal(slug, name, url) {
        title.textContent = name;
        frame.srcdoc = '<div style="font-family:sans-serif;padding:40px">Memuat…</div>';
        modal.classList.remove('hidden'); modal.classList.add('flex');
        getHtml(slug, url).then(function (html) { frame.srcdoc = forceLight(html); })
            .catch(function () { frame.srcdoc = '<p style="padding:40px;font-family:sans-serif">Gagal memuat preview.</p>'; });
    }
    function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); frame.srcdoc = ''; }
    document.querySelectorAll('.fe-preview-trigger').forEach(function (b) {
        b.addEventListener('click', function () {
            openModal(this.getAttribute('data-slug'), this.getAttribute('data-name'), this.getAttribute('data-preview-url'));
        });
    });
    document.getElementById('fe-preview-close').addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });
})();

// ── File preview helper ──────────────────────────────────────────────────────
function previewFile(input, previewId) {
    var preview = document.getElementById(previewId);
    if (!preview) return;
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush

@endsection
