@extends('layouts.app')

@section('title', 'Edit '.($tabs[$activeTab]['label'] ?? 'Company Profile'))

@push('head')
<link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}">
<style>
    /* Constrain editor toolbar + area to the card */
    #company-profile-editor { min-height: 420px; }
    .quill-toolbar.ql-toolbar { border-radius: 0.5rem 0.5rem 0 0; border-color: var(--color-base-300, #e5e7eb); }
    .quill-container.ql-container { border-radius: 0 0 0.5rem 0.5rem; border-color: var(--color-base-300, #e5e7eb); font-size: 0.95rem; }
    .quill-container.ql-container .ql-editor { min-height: 420px; }
    #upload-status { display: none; }
    #upload-status.is-on { display: inline-flex; }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('company-profile.show', ['tab' => $activeTab]) }}" class="link link-secondary text-sm">← Back to Company Profile</a>
    </div>

    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6 p-4">
        <h2 class="text-2xl font-bold">✏️ Edit {{ $tabs[$activeTab]['label'] ?? 'Company Profile' }}</h2>
        <p class="opacity-60 text-sm mt-1">
            {{ $tabs[$activeTab]['description'] ?? '' }}
            Everything is saved per-agency.
        </p>
    </div>

    {{-- Tabs: switch which block you are editing (unsaved changes prompt first) --}}
    <div role="tablist" class="tabs tabs-boxed flex flex-wrap gap-1 mb-4 bg-base-200 p-1 rounded-xl">
        @foreach($tabs as $slug => $meta)
            <a role="tab"
               href="{{ route('company-profile.edit', ['tab' => $slug]) }}"
               data-tab-link="{{ $slug }}"
               class="tab tab-lg {{ $activeTab === $slug ? 'tab-active font-semibold' : '' }}">
                <span class="mr-1">{{ $meta['icon'] }}</span>{{ $meta['label'] }}
            </a>
        @endforeach
    </div>

    @if(session('success'))
        <div role="alert" class="alert alert-success mb-6 shadow-sm"><span>✅</span> {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="alert alert-error mb-6 shadow-sm">
            <span>❌</span>
            <ul class="list-disc pl-4 text-sm">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @php
        $updateAction = $activeTab === \App\Support\CompanyProfileTabs::COMPANY_PROFILE
            ? route('company-profile.update')
            : route('company-profile.tab.update', ['tab' => $activeTab]);
    @endphp
    <form method="POST" action="{{ $updateAction }}" class="card bg-base-100 shadow-sm" id="page-form">
        <div class="card-body space-y-4">
            @csrf
            {{--
                A <textarea> (not an input value=""): Blade escapes the HTML exactly ONCE
                and the browser hands the raw HTML back to Quill. The old
                value="{{ e($content) }}" double-escaped it, so the second save stored
                literal &lt;p&gt; text and wiped images.
            --}}
            <textarea name="content" id="content-input" class="hidden" style="display:none">{{ $content }}</textarea>

            <div id="company-profile-editor" class="quill-container"></div>

            <p class="text-xs opacity-60">
                <strong>Tip:</strong> headings, lists, tables, images and links are supported.
                Images are uploaded and stored as files (not pasted as giant blobs).
                Content is sanitized on save.
            </p>

            <div class="flex items-center justify-between gap-3 pt-2">
                <span id="upload-status" class="text-xs opacity-70 items-center gap-2">
                    <span class="loading loading-spinner loading-xs"></span> Uploading image…
                </span>
                <div class="flex items-center gap-3">
                    <a href="{{ route('company-profile.show', ['tab' => $activeTab]) }}" class="btn btn-ghost" id="cancel-link">Cancel</a>
                    <button type="submit" class="btn btn-primary">💾 Save {{ $tabs[$activeTab]['label'] ?? 'page' }}</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/quill/quill.js') }}"></script>
<script>
    (function () {
        var field = document.getElementById('content-input');
        var initial = field.value;
        var uploadUrl = @json(route('company-profile.image'));
        var csrf = document.querySelector('#page-form input[name="_token"]').value;
        var statusEl = document.getElementById('upload-status');

        var quill = new Quill('#company-profile-editor', {
            theme: 'snow',
            placeholder: 'Write here…',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ color: [] }, { background: [] }],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ align: [] }],
                    ['blockquote', 'code-block'],
                    ['link', 'image'],
                    [{ table: 'container' }],
                    ['clean']
                ]
            }
        });

        // Load saved content once (raw HTML — the textarea already un-escaped it).
        if (initial) {
            try {
                quill.clipboard.dangerouslyPasteHTML(initial);
            } catch (e) {
                quill.root.innerHTML = initial;
            }
        }

        // Upload an image file and insert it as a URL (no base64 blobs in the form).
        function uploadFile(file, index) {
            if (!file || !/^image\//.test(file.type)) return;
            statusEl.classList.add('is-on');
            var fd = new FormData();
            fd.append('image', file);
            fd.append('_token', csrf);
            fetch(uploadUrl, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
                .then(function (res) {
                    statusEl.classList.remove('is-on');
                    if (!res.ok || !res.body.url) {
                        alert('Image upload failed. Use JPG, PNG, GIF or WEBP under 5 MB.');
                        return;
                    }
                    var at = (typeof index === 'number') ? index : quill.getSelection(true).index;
                    quill.insertEmbed(at, 'image', res.body.url, 'user');
                    quill.setSelection(at + 1);
                })
                .catch(function () {
                    statusEl.classList.remove('is-on');
                    alert('Image upload failed. Please try again.');
                });
        }

        // Toolbar image button -> file picker -> upload
        quill.getModule('toolbar').addHandler('image', function () {
            var picker = document.createElement('input');
            picker.type = 'file';
            picker.accept = 'image/jpeg,image/png,image/gif,image/webp';
            picker.onchange = function () { if (picker.files && picker.files[0]) uploadFile(picker.files[0]); };
            picker.click();
        });

        // Pasted/dropped images arrive as base64 — replace them with uploaded files
        // so the saved HTML (and the POST body) stays small.
        quill.root.addEventListener('drop', function (e) {
            var f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (f && /^image\//.test(f.type)) { e.preventDefault(); uploadFile(f); }
        });
        // Pasted images land as inline base64; offload them and swap in the URL.
        var offloading = false;
        function offloadInlineImages() {
            if (offloading) return;
            var imgs = Array.prototype.slice.call(quill.root.querySelectorAll('img[src^="data:"]'));
            if (!imgs.length) return;
            offloading = true;
            statusEl.classList.add('is-on');
            Promise.all(imgs.map(function (img) {
                return fetch(img.src).then(function (r) { return r.blob(); }).then(function (blob) {
                    var fd = new FormData();
                    fd.append('image', new File([blob], 'pasted.' + ((blob.type.split('/')[1] || 'png').replace('jpeg', 'jpg')), { type: blob.type }));
                    fd.append('_token', csrf);
                    return fetch(uploadUrl, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (j) { if (j.url) img.setAttribute('src', j.url); });
                }).catch(function () {});
            })).then(function () {
                offloading = false;
                statusEl.classList.remove('is-on');
            });
        }
        quill.on('text-change', function () { setTimeout(offloadInlineImages, 400); });

        var form = document.getElementById('page-form');
        var dirty = false;

        form.addEventListener('submit', function () {
            dirty = false; // this submit IS the save
            field.value = quill.getSemanticHTML ? quill.getSemanticHTML() : quill.root.innerHTML;
        });

        quill.on('text-change', function () { dirty = true; });
        window.addEventListener('beforeunload', function (e) {
            if (!dirty) return;
            e.preventDefault();
            e.returnValue = '';
        });

        // Warn before switching tabs with unsaved edits.
        Array.prototype.forEach.call(document.querySelectorAll('[data-tab-link]'), function (link) {
            link.addEventListener('click', function (e) {
                if (!dirty) return;
                if (!confirm('You have unsaved changes on this tab. Leave anyway?')) e.preventDefault();
            });
        });
    })();
</script>
@endpush
