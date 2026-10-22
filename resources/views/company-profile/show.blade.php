@extends('layouts.app')

@section('title', 'Company Profile')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6 p-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold">🏢 Company Profile</h2>
            <p class="opacity-60 text-sm mt-1">
                {{ $agency->name }}'s company page — what the agency wants to show about
                how it works and its process.
            </p>
        </div>
        @if(in_array(auth()->user()->user_type, ['admin', 'super_admin']))
            <a href="{{ route('company-profile.tab.edit', ['tab' => $activeTab]) }}" class="btn btn-primary btn-sm" data-edit-tab="{{ $activeTab }}">
                ✏️ Edit this tab
            </a>
        @endif
    </div>

    @if(session('success'))
        <div role="alert" class="alert alert-success mb-6 shadow-sm"><span>✅</span> {{ session('success') }}</div>
    @endif

    {{-- Four tabs: Company Profile, Organizational Chart, Recruitment Process, System User Guide --}}
    <div role="tablist" class="tabs tabs-boxed flex flex-wrap gap-1 mb-4 bg-base-200 p-1 rounded-xl">
        @foreach($tabs as $slug => $meta)
            <a role="tab"
               href="{{ route('company-profile.tab', ['tab' => $slug]) }}"
               class="tab tab-lg {{ $activeTab === $slug ? 'tab-active font-semibold' : '' }}">
                <span class="mr-1">{{ $meta['icon'] }}</span>{{ $meta['label'] }}
            </a>
        @endforeach
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            @if($content)
                <div class="company-profile-content max-w-none">
                    {!! $content !!}
                </div>
            @else
                <div class="text-center py-14 opacity-60">
                    <div class="text-5xl mb-3">{{ $tabs[$activeTab]['icon'] ?? '📄' }}</div>
                    <p class="font-semibold">Nothing here yet.</p>
                    <p class="text-sm mt-1">{{ $tabs[$activeTab]['description'] ?? '' }}</p>
                    @if(in_array(auth()->user()->user_type, ['admin', 'super_admin']))
                        <p class="text-sm mt-2">
                            Use the <em>Edit this tab</em> button above to fill it in.
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* Quill semantic HTML rendered outside the editor needs explicit typography:
       Tailwind preflight flattens headings/lists, and the typography plugin
       (.prose) is not loaded — so style everything ourselves. */
    .company-profile-content {
        font-size: 0.95rem;
        line-height: 1.75;
        color: inherit;
        overflow-wrap: anywhere;   /* long words/URLs never punch out of the card */
        min-width: 0;
        max-width: 100%;
    }
    .company-profile-content > *:first-child { margin-top: 0; }
    .company-profile-content h1 { font-size: 2em;    font-weight: 700; margin: 0.9em 0 0.4em; line-height: 1.25; }
    .company-profile-content h2 { font-size: 1.5em;  font-weight: 700; margin: 0.9em 0 0.4em; line-height: 1.3; }
    .company-profile-content h3 { font-size: 1.17em; font-weight: 600; margin: 0.8em 0 0.4em; line-height: 1.35; }
    .company-profile-content h4 { font-size: 1em;    font-weight: 600; margin: 0.8em 0 0.4em; }
    .company-profile-content h5 { font-size: 0.85em; font-weight: 600; margin: 0.8em 0 0.4em; text-transform: uppercase; letter-spacing: 0.03em; }
    .company-profile-content h6 { font-size: 0.75em; font-weight: 600; margin: 0.8em 0 0.4em; text-transform: uppercase; letter-spacing: 0.03em; }
    .company-profile-content p { margin: 0.6em 0; }
    .company-profile-content blockquote {
        border-left: 4px solid #d1d5db; padding: 0.2em 0 0.2em 1rem;
        margin: 0.8em 0; color: #4b5563; font-style: italic;
    }
    .company-profile-content ul { list-style: disc;   padding-left: 1.7em; margin: 0.6em 0; }
    .company-profile-content ol { list-style: decimal; padding-left: 1.7em; margin: 0.6em 0; }
    .company-profile-content li { margin: 0.25em 0; }
    .company-profile-content li[data-list="bullet"] { list-style: disc; }
    .company-profile-content pre {
        background: #f3f4f6; border-radius: 0.5rem; padding: 0.9rem 1rem;
        overflow-x: auto; font-size: 0.85em; margin: 0.8em 0;
    }
    .company-profile-content code { background: #f3f4f6; border-radius: 0.3rem; padding: 0.1em 0.35em; font-size: 0.9em; }
    .company-profile-content a { color: #1d4ed8; text-decoration: underline; }
    .company-profile-content img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 0.5em 0; }
    .company-profile-content table {
        display: block; max-width: 100%; overflow-x: auto; border-collapse: collapse;
        margin: 1rem 0; border: 1px solid #d1d5db;
    }
    .company-profile-content th, .company-profile-content td {
        border: 1px solid #d1d5db; padding: 0.5rem 0.75rem; text-align: left;
    }
    .company-profile-content hr { border: none; border-top: 1px solid #d1d5db; margin: 1.25rem 0; }
    /* Quill alignment classes */
    .company-profile-content .ql-align-center  { text-align: center; }
    .company-profile-content .ql-align-right   { text-align: right; }
    .company-profile-content .ql-align-justify { text-align: justify; }
    .company-profile-content .ql-indent-1 { margin-left: 2.5em; }
    .company-profile-content .ql-indent-2 { margin-left: 5em; }
    .company-profile-content .ql-indent-3 { margin-left: 7.5em; }
    .company-profile-content .ql-indent-4 { margin-left: 10em; }
</style>
@endsection
