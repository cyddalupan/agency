@extends('layouts.app')

@section('title', 'Agency Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('settings.index') }}" class="link link-secondary text-sm">← Back to Settings</a>
    </div>

    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6 p-4">
        <h2 class="text-2xl font-bold">🏢 Agency Settings</h2>
        <p class="opacity-60 text-sm mt-1">
            Agency-wide options for <strong>{{ $agency->name }}</strong>. Everyone who logs in
            under this agency is affected.
        </p>
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

    <form method="POST" action="{{ route('settings.agency.update') }}" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-6">
            @csrf

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Sidebar</legend>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="show_company_profile" value="1"
                           class="toggle toggle-primary mt-1"
                           @checked($showCompanyProfile)>
                    <span class="text-sm">
                        <span class="font-semibold">Show the Company Profile page in the sidebar.</span>
                        <span class="block opacity-60">
                            Turn this off if this agency does not want a Company Profile.
                            The sidebar link is hidden for everyone under this agency.
                            The page itself stays available by direct link.
                        </span>
                    </span>
                </label>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Quality Management (QMS)</legend>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="qms_enabled" value="1"
                           class="toggle toggle-primary mt-1"
                           id="toggle-qms-enabled"
                           @checked($qmsEnabled)>
                    <span class="text-sm">
                        <span class="font-semibold">Turn on the QMS features for this agency.</span>
                        <span class="block opacity-60">
                            Controls Audit Trail, Controlled Documents, Risk Register,
                            Complaints, Non-Conformities, Internal Audits, the Compliance
                            Calendar and the QMS Dashboard as one switch. With this off,
                            nobody under this agency can open them, admins included, and
                            their sidebar links are hidden. The records themselves are kept.
                        </span>
                    </span>
                </label>
            </fieldset>

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">💾 Save Agency Settings</button>
            </div>
        </div>
    </form>
</div>
@endsection
