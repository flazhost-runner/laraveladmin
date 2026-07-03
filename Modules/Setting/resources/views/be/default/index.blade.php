@extends('layouts.be.default.main')
@section('title', 'Settings')
@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Settings</h1>
</div>

{{-- ============================================================
     SECTION 1: Admin Theme
     ============================================================ --}}
<div class="tw-card mb-6">
    <h2 class="text-lg font-semibold text-gray-700 mb-4">
        <i class="fas fa-palette fa-fw text-indigo-500"></i> Admin Theme
    </h2>
    <p class="text-sm text-gray-500 mb-4">Choose the color theme for the admin panel. The selected theme will be applied immediately after saving.</p>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3" id="theme-picker">
        @foreach($themes as $themeName => $swatches)
        <label class="cursor-pointer group">
            <input type="radio" name="_theme_picker" value="{{ $themeName }}"
                   {{ strtolower($setting->theme ?? 'blue') === $themeName ? 'checked' : '' }}
                   class="sr-only theme-radio"
                   onchange="document.getElementById('theme_input').value = this.value">
            <div class="theme-swatch border-2 rounded-lg p-3 transition-all
                        {{ strtolower($setting->theme ?? 'blue') === $themeName
                            ? 'border-gray-800 ring-2 ring-gray-400 bg-gray-50'
                            : 'border-gray-200 hover:border-gray-400' }}"
                 data-theme="{{ $themeName }}">
                <div class="flex gap-1 mb-2">
                    @foreach($swatches as $hex)
                    <div class="w-5 h-5 rounded-full border border-white shadow-sm" style="background:{{ $hex }}"></div>
                    @endforeach
                </div>
                <div class="text-xs font-medium text-gray-700 capitalize">{{ $themeName }}</div>
                @if(strtolower($setting->theme ?? 'blue') === $themeName)
                <div class="text-xs text-gray-500 mt-0.5">
                    <i class="fas fa-check-circle text-green-500"></i> Active
                </div>
                @endif
            </div>
        </label>
        @endforeach
    </div>
</div>

{{-- ============================================================
     SECTION 2: Frontend Template Catalog
     ============================================================ --}}
<div class="tw-card mb-6">
    <h2 class="text-lg font-semibold text-gray-700 mb-4">
        <i class="fas fa-window-maximize fa-fw" style="color:var(--primary)"></i> Frontend Template
    </h2>
    <p class="text-sm text-gray-500 mb-4">
        Choose the public-facing template. The active template is: <strong>{{ $setting->fe_template ?? 'none' }}</strong>
    </p>

    {{-- Search / Filter Form --}}
    <form method="GET" action="{{ route('admin.v1.setting.index') }}" id="fe_search" class="flex flex-wrap gap-2 mb-4">
        <input type="text" name="q_name" value="{{ $filter['q_name'] ?? '' }}"
               placeholder="Search templates..." class="form-control w-auto flex-1 min-w-[180px]">

        <select name="q_category" class="form-control w-auto">
            <option value="">All Categories</option>
            @foreach($catalog['categories'] as $cat)
            <option value="{{ $cat }}" {{ ($filter['q_category'] ?? '') === $cat ? 'selected' : '' }}>
                {{ ucwords(str_replace('-', ' ', $cat)) }}
            </option>
            @endforeach
        </select>

        <input type="hidden" name="fe_page" value="1">
        <button type="submit" class="btn btn-secondary">
            <i class="fas fa-search fa-fw"></i> Search
        </button>
        @if(!empty($filter['q_name']) || !empty($filter['q_category']))
        <a href="{{ route('admin.v1.setting.index') }}" class="btn btn-secondary">
            <i class="fas fa-times fa-fw"></i> Clear
        </a>
        @endif
    </form>

    {{-- Template Grid --}}
    @if(count($catalog['items']) === 0)
    <div class="text-center py-10 text-gray-500">
        <i class="fas fa-folder-open fa-2x mb-2"></i>
        <p>No templates found.</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 mb-4">
        @foreach($catalog['items'] as $tpl)
        @php $isActive = ($tpl['slug'] === ($setting->fe_template ?? '')); @endphp
        <div class="fe-card border-2 rounded-lg overflow-hidden transition-all
                    {{ $isActive ? 'border-indigo-500 ring-2 ring-indigo-300' : 'border-gray-200 hover:border-gray-400' }}"
             data-slug="{{ $tpl['slug'] }}">
            {{-- Thumbnail iframe --}}
            <div class="relative w-full bg-gray-100" style="height:160px">
                <iframe class="fe-preview-iframe absolute inset-0 w-full h-full border-0 pointer-events-none"
                        data-slug="{{ $tpl['slug'] }}"
                        data-preview-url="{{ route('admin.v1.setting.fe_preview', $tpl['slug']) }}"
                        sandbox="allow-same-origin"
                        style="transform:scale(0.5);transform-origin:top left;width:200%;height:200%;"></iframe>
                <div class="fe-preview-loading absolute inset-0 flex items-center justify-center bg-gray-100 text-gray-400 text-xs">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                {{-- Full Preview Button --}}
                <button type="button"
                        class="fe-full-preview-btn absolute top-1 right-1 btn btn-xs bg-white/80 text-gray-700 border border-gray-300 text-xs px-1.5 py-0.5 rounded"
                        data-slug="{{ $tpl['slug'] }}"
                        data-preview-url="{{ route('admin.v1.setting.fe_preview', $tpl['slug']) }}">
                    <i class="fas fa-expand fa-fw"></i>
                </button>
            </div>
            <div class="p-3">
                <div class="text-sm font-medium text-gray-800 mb-0.5">{{ $tpl['name'] }}</div>
                @if(!empty($tpl['category']))
                <div class="text-xs text-gray-400 mb-2 capitalize">{{ str_replace('-', ' ', $tpl['category']) }}</div>
                @endif
                @if($isActive)
                <button type="button"
                        class="btn btn-xs w-full bg-indigo-600 text-white cursor-default" disabled>
                    <i class="fas fa-check fa-fw"></i> CHOSEN
                </button>
                @else
                <button type="button"
                        class="btn btn-xs w-full btn-secondary fe-choose-btn"
                        data-slug="{{ $tpl['slug'] }}">
                    CHOOSE
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($catalog['last_page'] > 1)
    <div class="flex items-center gap-1 flex-wrap">
        @for($p = 1; $p <= $catalog['last_page']; $p++)
        <a href="{{ route('admin.v1.setting.index', array_merge($filter, ['fe_page' => $p])) }}"
           class="btn btn-xs {{ $p === $catalog['current_page'] ? 'btn-primary' : 'btn-secondary' }}">
            {{ $p }}
        </a>
        @endfor
        <span class="text-xs text-gray-500 ml-2">
            Page {{ $catalog['current_page'] }} of {{ $catalog['last_page'] }} ({{ $catalog['total'] }} templates)
        </span>
    </div>
    @endif
    @endif
</div>

{{-- ============================================================
     SECTION 3: Settings Form
     ============================================================ --}}
<div class="tw-card">
    <h2 class="text-lg font-semibold text-gray-700 mb-4">
        <i class="fas fa-cog fa-fw text-gray-500"></i> Setting Form
    </h2>

    <form method="POST" action="{{ route('admin.v1.setting.update') }}?_method=PUT"
          enctype="multipart/form-data">
        @csrf

        {{-- Hidden inputs for theme + fe_template (set by pickers above) --}}
        <input type="hidden" id="theme_input" name="theme" value="{{ old('theme', $setting->theme ?? 'blue') }}">
        <input type="hidden" id="fe_template_input" name="fe_template" value="{{ old('fe_template', $setting->fe_template ?? '') }}">

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
                <label class="form-label" for="name">Site Name</label>
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
                       value="{{ old('copyright', $setting->copyright) }}" placeholder="2024 My Company">
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
                <label class="form-label" for="logo">Logo</label>
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

        {{-- Selected Template Display --}}
        <div class="mt-4 p-3 bg-indigo-50 rounded border border-indigo-200 text-sm text-indigo-700">
            <i class="fas fa-info-circle fa-fw"></i>
            Selected Frontend Template: <strong id="fe-template-display">{{ old('fe_template', $setting->fe_template ?? 'none') }}</strong>
        </div>

        <div class="mt-6 flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save fa-fw"></i> Save Settings
            </button>
        </div>
    </form>
</div>

{{-- ============================================================
     Preview Modal
     ============================================================ --}}
<div id="fe-preview-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/70 p-4">
    <div class="bg-white rounded-lg shadow-2xl flex flex-col" style="width:90vw;height:85vh;max-width:1200px">
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <span class="font-semibold text-gray-700" id="fe-modal-title">Preview</span>
            <button type="button" id="fe-modal-close" class="text-gray-400 hover:text-gray-700 text-xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="flex-1 relative overflow-hidden">
            <iframe id="fe-modal-iframe" class="absolute inset-0 w-full h-full border-0"
                    sandbox="allow-same-origin"></iframe>
            <div id="fe-modal-loading" class="absolute inset-0 flex items-center justify-center bg-white text-gray-400">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
            </div>
        </div>
        <div class="px-4 py-3 border-t flex justify-between items-center">
            <span id="fe-modal-slug" class="text-xs text-gray-400 font-mono"></span>
            <button type="button" id="fe-modal-choose" class="btn btn-primary btn-sm">
                <i class="fas fa-check fa-fw"></i> Choose This Template
            </button>
        </div>
    </div>
</div>

@push('foot-scripts')
<script>
(function () {
    var STORAGE_PREFIX = 'fe_preview_v1_';
    var previewRoute = '{{ rtrim(route('admin.v1.setting.index'), '/') }}/../setting/fe-preview/';

    // ── Theme picker ──────────────────────────────────────────────────────────
    document.querySelectorAll('.theme-radio').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('[data-theme]').forEach(function (card) {
                card.classList.remove('border-gray-800', 'ring-2', 'ring-gray-400', 'bg-gray-50');
                card.classList.add('border-gray-200');
            });
            var card = document.querySelector('[data-theme="' + this.value + '"]');
            if (card) {
                card.classList.remove('border-gray-200');
                card.classList.add('border-gray-800', 'ring-2', 'ring-gray-400', 'bg-gray-50');
            }
        });
    });

    // ── Load iframe previews with localStorage cache ─────────────────────────
    function loadIframePreviews() {
        document.querySelectorAll('.fe-preview-iframe').forEach(function (iframe) {
            var slug = iframe.dataset.slug;
            var previewUrl = iframe.dataset.previewUrl;
            var cached = null;
            try { cached = localStorage.getItem(STORAGE_PREFIX + slug); } catch(e){}

            var loading = iframe.closest('.relative')?.querySelector('.fe-preview-loading');

            function fillIframe(html) {
                iframe.srcdoc = html;
                if (loading) loading.style.display = 'none';
            }

            if (cached) {
                fillIframe(cached);
            } else {
                fetch(previewUrl)
                    .then(function(r) { return r.ok ? r.text() : Promise.reject(); })
                    .then(function(html) {
                        try { localStorage.setItem(STORAGE_PREFIX + slug, html); } catch(e){}
                        fillIframe(html);
                    })
                    .catch(function() {
                        if (loading) loading.innerHTML = '<span class="text-xs text-red-400">Preview unavailable</span>';
                    });
            }
        });
    }
    loadIframePreviews();

    // ── Choose template button ────────────────────────────────────────────────
    function chooseTemplate(slug) {
        document.getElementById('fe_template_input').value = slug;
        document.getElementById('fe-template-display').textContent = slug;

        // Update button states in grid
        document.querySelectorAll('.fe-choose-btn').forEach(function (btn) {
            btn.textContent = 'CHOOSE';
            btn.disabled = false;
            btn.classList.remove('bg-indigo-600', 'text-white');
        });
        var card = document.querySelector('[data-slug="' + slug + '"]');
        if (card) {
            var chooseBtn = card.querySelector('.fe-choose-btn');
            if (chooseBtn) {
                chooseBtn.textContent = 'CHOSEN';
                chooseBtn.disabled = true;
                chooseBtn.classList.add('bg-indigo-600', 'text-white');
            }
        }
    }

    document.querySelectorAll('.fe-choose-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            chooseTemplate(this.dataset.slug);
        });
    });

    // ── Full Preview Modal ────────────────────────────────────────────────────
    var modal       = document.getElementById('fe-preview-modal');
    var modalIframe = document.getElementById('fe-modal-iframe');
    var modalTitle  = document.getElementById('fe-modal-title');
    var modalSlug   = document.getElementById('fe-modal-slug');
    var modalLoad   = document.getElementById('fe-modal-loading');
    var modalChoose = document.getElementById('fe-modal-choose');
    var currentModalSlug = '';

    function openModal(slug, previewUrl) {
        currentModalSlug = slug;
        modalTitle.textContent = 'Preview: ' + slug;
        modalSlug.textContent  = slug;
        modalLoad.style.display = 'flex';
        modalIframe.srcdoc = '';
        modal.classList.remove('hidden');

        var cached = null;
        try { cached = localStorage.getItem(STORAGE_PREFIX + slug); } catch(e){}

        function setContent(html) {
            modalIframe.srcdoc = html;
            modalLoad.style.display = 'none';
        }

        if (cached) {
            setContent(cached);
        } else {
            fetch(previewUrl)
                .then(function(r) { return r.ok ? r.text() : Promise.reject(); })
                .then(function(html) {
                    try { localStorage.setItem(STORAGE_PREFIX + slug, html); } catch(e){}
                    setContent(html);
                })
                .catch(function() {
                    modalLoad.innerHTML = '<span class="text-red-500">Preview unavailable</span>';
                });
        }
    }

    document.querySelectorAll('.fe-full-preview-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            openModal(this.dataset.slug, this.dataset.previewUrl);
        });
    });

    document.getElementById('fe-modal-close').addEventListener('click', function () {
        modal.classList.add('hidden');
        modalIframe.srcdoc = '';
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
            modalIframe.srcdoc = '';
        }
    });

    modalChoose.addEventListener('click', function () {
        if (currentModalSlug) {
            chooseTemplate(currentModalSlug);
            modal.classList.add('hidden');
            modalIframe.srcdoc = '';
        }
    });
})();

// ── File preview helper ────────────────────────────────────────────────────────
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
