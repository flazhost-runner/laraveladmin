<!-- jQuery (required for Trumbowyg + select-all) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Trumbowyg (+ plugin File Manager → endpoint /admin/v1/media, paritas NodeAdmin) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/ui/trumbowyg.min.css">
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/trumbowyg.min.js"></script>
<script src="{{ asset('be/default/vendor/trumbowyg/filemanager.js') }}"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
(function() {
    /* ── Trumbowyg init ── */
    if (window.jQuery && jQuery.fn.trumbowyg) {
        $(".trumbowyg").trumbowyg();
        $(".trumbowyg-editor").trumbowyg({
            btns: [
                ['viewHTML'],
                ['formatting'],
                ['strong', 'em', 'del'],
                ['superscript', 'subscript'],
                ['link'],
                ['filemanager'],
                ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                ['unorderedList', 'orderedList'],
                ['horizontalRule'],
                ['removeformat'],
                ['fullscreen'],
            ],
            plugins: { filemanager: true },
            semantic: { div: 'div' },
            autogrow: true,
            removeformatPasted: true,
        });
        $("form").on("submit", function() {
            $(this).find(".trumbowyg, .trumbowyg-editor").each(function() {
                if ($(this).data('trumbowyg')) $(this).val($(this).trumbowyg('html'));
            });
        });
    }

    /* ── Sidebar toggle ── */
    var sb  = document.getElementById('tw-sidebar');
    var ov  = document.getElementById('tw-sidebar-overlay');
    var btn = document.getElementById('tw-sidebar-toggle');
    function openSb()  { sb.classList.remove('-translate-x-full'); ov.classList.remove('hidden'); }
    function closeSb() { sb.classList.add('-translate-x-full');    ov.classList.add('hidden'); }
    if (btn) btn.addEventListener('click', openSb);
    if (ov)  ov.addEventListener('click', closeSb);

    /* ── Dropdown toggle ── */
    document.querySelectorAll('[data-toggle-dd]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var id = btn.getAttribute('data-toggle-dd');
            var dd = document.getElementById(id);
            if (dd) dd.classList.toggle('show');
        });
    });
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu.show').forEach(function(d) { d.classList.remove('show'); });
    });

    /* ── confirmDialog: themed modal Promise (not window.confirm) ── */
    window.confirmDialog = function(msg) {
        return new Promise(function(resolve) {
            var overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:flex;align-items:center;justify-content:center';
            overlay.innerHTML = '<div style="background:white;border-radius:.5rem;padding:1.5rem;max-width:400px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.3)">'
                + '<p style="margin:0 0 1.25rem;font-size:.95rem;color:#374151">' + msg + '</p>'
                + '<div style="display:flex;gap:.5rem;justify-content:flex-end">'
                + '<button class="btn btn-secondary" id="_cdCancel">Cancel</button>'
                + '<button class="btn btn-danger" id="_cdOk">OK</button>'
                + '</div></div>';
            document.body.appendChild(overlay);
            overlay.querySelector('#_cdOk').onclick = function() { document.body.removeChild(overlay); resolve(true); };
            overlay.querySelector('#_cdCancel').onclick = function() { document.body.removeChild(overlay); resolve(false); };
        });
    };
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault(); e.stopPropagation();
            var msg = el.getAttribute('data-confirm') || 'Are you sure?';
            confirmDialog(msg).then(function(ok) {
                if (ok) {
                    var form = el.closest('form');
                    if (form) { form.submit(); }
                    else if (el.href) { window.location.href = el.href; }
                }
            });
        });
    });

    /* ── Select-all checkboxes (#checkall) ── */
    if (window.jQuery) {
        $(document).on('click', '#checkall', function() {
            $('input[type="checkbox"][name="selected[]"]').prop('checked', this.checked);
        });
    }

    /* ── window.Toast(message, type) — auto-dismiss 3500ms ── */
    window.Toast = function(message, type) {
        type = type || 'info';
        var colors = { success: '#10b981', error: '#ef4444', info: '#3b82f6' };
        var t = document.createElement('div');
        t.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;padding:.75rem 1.25rem;border-radius:.375rem;color:white;font-size:.875rem;box-shadow:0 4px 12px rgba(0,0,0,.15);background:' + (colors[type] || colors.info);
        t.textContent = message;
        document.body.appendChild(t);
        setTimeout(function() { t.parentNode && t.parentNode.removeChild(t); }, 3500);
    };
    /* ── Auto-dismiss flash toasts after 3500ms ── */
    setTimeout(function() {
        ['toast-success', 'toast-error'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
    }, 3500);

    /* ── Global image fallback ── */
    document.addEventListener('error', function(e) {
        var img = e.target;
        if (img.tagName !== 'IMG') return;
        var alt = (img.alt || '').toLowerCase();
        var cls = (img.className || '').toLowerCase();
        var isAvatar = /user|picture|avatar|rounded-full/.test(alt + cls);
        var icon = isAvatar ? 'fa-user' : 'fa-image';
        var w = img.width || img.naturalWidth || 48;
        var h = img.height || img.naturalHeight || 48;
        img.style.display = 'none';
        var span = document.createElement('span');
        span.style.cssText = 'display:inline-flex;align-items:center;justify-content:center;background:#e5e7eb;border-radius:0.25rem;width:' + w + 'px;height:' + h + 'px;color:#9ca3af;';
        span.innerHTML = '<i class="fas ' + icon + '"></i>';
        img.parentNode && img.parentNode.insertBefore(span, img.nextSibling);
    }, true);

    /* ── previewImage: file input → inline preview ── */
    window.previewImage = function(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = document.getElementById('picture-img');
                var wrap = document.getElementById('picture-preview');
                if (img) img.src = e.target.result;
                if (wrap) wrap.style.display = '';
            };
            reader.readAsDataURL(input.files[0]);
        }
    };
})();
</script>

@stack('trumbowyg-scripts')
@stack('foot-scripts')
