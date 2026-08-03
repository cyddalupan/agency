@php
    /**
     * LANDAS "Personal Information" — 6-tab layout (TDD).
     * Grouped from existing sub-entities. Reuses applicants.sub-forms.* and
     * applicants.sub-lists.* partials already covered by passing tests.
     */
    $pi = $applicant->load([
        'passport', 'education', 'certificates', 'requirements',
        'workExperiences', 'skills', 'references', 'salaryRecords', 'documents',
        'languages', 'contacts',
        'spouse', 'family', 'emergencyContacts',
    ]);
    $hidePassport = $applicant->has_passport === 'without';
@endphp

<div class="card bg-base-100 shadow-sm mb-6 card-lift">
    <div class="card-body">
        <h3 class="card-title flex items-center gap-2 mb-4">
            <span>📋</span> Personal Information
        </h3>

        {{-- Tab bar (bookmark-style) --}}
        <div class="flex flex-wrap gap-1 border-b border-base-300 pb-0 mb-0" role="tablist">
            @foreach ([
                ['basic',   'Basic Information'],
                ['req',     'Requirements'],
                ['cert',    'Certifications'],
                ['docs',    'Documents'],
                ['uploads', 'Upload Files'],
                ['status',  'Status'],
            ] as [$key, $label])
            <button type="button" role="tab" data-pi-tab="{{ $key }}"
                onclick="piSwitchTab('{{ $key }}')"
                class="relative px-4 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 transition-all {{ $key === 'basic' ? 'tab-active bg-primary text-primary-content border-primary shadow-md' : 'text-base-content/70 hover:bg-base-200 border-transparent' }}">{{ $label }}</button>
            @endforeach
        </div>

        <div class="border border-t-0 border-base-300 rounded-b-lg p-6 bg-base-100">
            {{-- ============ TAB: Basic Information (default) ============ --}}
            <section data-pi-panel="basic">
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                    <div class="p-3 rounded-lg bg-base-200/50"><dt class="text-xs opacity-60 uppercase tracking-wider">Full Name</dt><dd class="font-medium mt-1">{{ $applicant->full_name }}@if($applicant->suffix) <span class="text-xs opacity-60">({{ $applicant->suffix }})</span>@endif</dd></div>
                    <div class="p-3 rounded-lg bg-base-200/50"><dt class="text-xs opacity-60 uppercase tracking-wider">💍 Civil Status</dt><dd class="font-medium mt-1">{{ $applicant->civilStatus?->name ?? '—' }}</dd></div>
                    <div class="p-3 rounded-lg bg-base-200/50"><dt class="text-xs opacity-60 uppercase tracking-wider">🌏 Nationality</dt><dd class="font-medium mt-1">{{ $applicant->nationality?->name ?? '—' }}</dd></div>
                    <div class="p-3 rounded-lg bg-base-200/50"><dt class="text-xs opacity-60 uppercase tracking-wider">⛪ Religion</dt><dd class="font-medium mt-1">{{ $applicant->religion?->name ?? '—' }}</dd></div>
                    <div class="p-3 rounded-lg bg-base-200/50"><dt class="text-xs opacity-60 uppercase tracking-wider">⚤ Gender</dt><dd class="font-medium mt-1">{{ $applicant->gender ?? '—' }}</dd></div>
                    <div class="p-3 rounded-lg bg-base-200/50"><dt class="text-xs opacity-60 uppercase tracking-wider">🎂 Birthdate</dt><dd class="font-medium mt-1">{{ $applicant->birthdate?->format('M d, Y') ?? '—' }}</dd></div>
                    <div class="p-3 rounded-lg bg-base-200/50"><dt class="text-xs opacity-60 uppercase tracking-wider">🎂 Age</dt><dd class="font-medium mt-1">{{ $applicant->birthdate ? $applicant->birthdate->age . ' years old' : '—' }}</dd></div>
                    <div class="p-3 rounded-lg bg-base-200/50"><dt class="text-xs opacity-60 uppercase tracking-wider">🌍 Preferred Country</dt><dd class="font-medium mt-1">{{ $applicant->country?->name ?? '—' }}</dd></div>
                    <div class="p-3 rounded-lg bg-base-200/50"><dt class="text-xs opacity-60 uppercase tracking-wider">💼 Preferred Position</dt><dd class="font-medium mt-1">{{ $applicant->position?->name ?? '—' }}</dd></div>
                    <div class="p-3 rounded-lg bg-base-200/50"><dt class="text-xs opacity-60 uppercase tracking-wider">📱 Source</dt><dd class="font-medium mt-1">{{ $applicant->source ?? '—' }}</dd></div>
                    <div class="p-3 rounded-lg bg-base-200/50 md:col-span-2"><dt class="text-xs opacity-60 uppercase tracking-wider">🏠 Address</dt><dd class="font-medium mt-1">{{ $applicant->address ?? '—' }}</dd></div>
                </dl>

                {{-- Contact Number(s) — multiple --}}
                @php $contactRecs = $pi->contacts ?? collect(); @endphp
                <div class="mt-6 pt-4 border-t border-base-200">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold">📞 Contact Number(s)</h4>
                        <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('form-contacts').classList.toggle('hidden')">➕ Add</button>
                    </div>
                    <div id="form-contacts" class="hidden border rounded-lg p-4 bg-base-200 mb-4">
                        <form action="{{ route('applicants.sub.store', [$applicant, 'contacts']) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">@include('applicants.sub-forms.contacts', ['record' => null])</div>
                            <div class="flex items-center gap-2 mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Save</button>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('form-contacts').classList.add('hidden')">❌ Cancel</button>
                            </div>
                        </form>
                    </div>
                    @if ($contactRecs->count() > 0) @include('applicants.sub-lists.contacts', ['records' => $contactRecs, 'routeKey' => 'contacts']) @else <p class="text-sm opacity-50 py-2">No contact numbers yet.</p> @endif
                </div>

                @if($applicant->remarks)
                <div class="mt-4 pt-4 border-t border-base-200"><dt class="text-sm opacity-60">📝 Remarks</dt><dd class="mt-1">{{ $applicant->remarks }}</dd></div>
                @endif

                @if($applicant->has_passport === 'without')
                <div class="mt-4 pt-4 border-t border-base-200"><span class="badge badge-error gap-1">❌ Without Passport</span></div>
                @endif

                {{-- Reused Basic-Info sub-entities --}}
                @foreach ([
                    ['education',        '🎓 Education',        'education'],
                    ['work-experiences', '💼 Work Experience',  'workExperiences'],
                    ['skills',           '🛠️ Skills',           'skills'],
                    ['languages',        '🗣️ Language',          'languages'],
                    ['references',       '👥 References',       'references'],
                    ['salary-records',   '💰 Salary Records',   'salaryRecords'],
                ] as [$routeKey, $label, $rel])
                @php $related = $pi->$rel ?? null; $records = collect($related) ?? collect(); @endphp
                <div class="mt-6 pt-4 border-t border-base-200">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold">{{ $label }}</h4>
                        <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('form-{{ $routeKey }}').classList.toggle('hidden')">➕ Add</button>
                    </div>
                    <div id="form-{{ $routeKey }}" class="hidden border rounded-lg p-4 bg-base-200 mb-4">
                        <form action="{{ route('applicants.sub.store', [$applicant, $routeKey]) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">@include("applicants.sub-forms.{$routeKey}", ['record' => null])</div>
                            <div class="flex items-center gap-2 mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Save</button>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('form-{{ $routeKey }}').classList.add('hidden')">❌ Cancel</button>
                            </div>
                        </form>
                    </div>
                    @if ($records->count() > 0) @include("applicants.sub-lists.{$routeKey}", ['records' => $records, 'routeKey' => $routeKey]) @else <p class="text-sm opacity-50 py-2">No records yet.</p> @endif
                </div>
                @endforeach

                {{-- Family Information (Number of Siblings + Mother/Father) --}}
                <div class="mt-6 pt-4 border-t border-base-200">
                    <h4 class="font-semibold mb-2">👨‍👩‍👧 Family Information</h4>
                    <p class="text-sm opacity-60 mb-3">Mother's Name / Mother's Occupation / Father's Name / Father's Occupation / Number of Siblings — add each family member below.</p>
                    <form action="{{ route('applicants.basic.update', $applicant) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">Number of Siblings</legend>
                                <input type="number" name="number_of_siblings" min="0" value="{{ old('number_of_siblings', $applicant->number_of_siblings ?? '') }}" class="input w-full" placeholder="e.g. 3">
                            </fieldset>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">💾 Save Update</button>
                        </div>
                    </form>
                    @php $fam = $pi->family ?? collect(); @endphp
                    @if ($fam->count() > 0) @include('applicants.sub-lists.family', ['records' => $fam, 'routeKey' => 'family']) @else <p class="text-sm opacity-50 py-2">No family members yet.</p> @endif
                </div>

                {{-- Spouse Information --}}
                <div class="mt-6 pt-4 border-t border-base-200">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold">💍 Spouse Information</h4>
                        <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('form-spouse').classList.toggle('hidden')">➕ Add</button>
                    </div>
                    <div id="form-spouse" class="hidden border rounded-lg p-4 bg-base-200 mb-4">
                        <form action="{{ route('applicants.sub.store', [$applicant, 'spouse']) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">@include('applicants.sub-forms.spouse', ['record' => null])</div>
                            <div class="flex items-center gap-2 mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Save</button>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('form-spouse').classList.add('hidden')">❌ Cancel</button>
                            </div>
                        </form>
                    </div>
                    @php $sp = $pi->spouse ?? collect(); @endphp
                    @if ($sp->count() > 0) @include('applicants.sub-lists.spouse', ['records' => $sp, 'routeKey' => 'spouse']) @else <p class="text-sm opacity-50 py-2">No spouse record yet.</p> @endif
                </div>

                {{-- In Case of Emergency (multiple contacts) --}}
                <div class="mt-6 pt-4 border-t border-base-200">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold">🚨 In Case of Emergency</h4>
                        <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('form-emergency').classList.toggle('hidden')">➕ Add Contact</button>
                    </div>
                    <div id="form-emergency" class="hidden border rounded-lg p-4 bg-base-200 mb-4">
                        <form action="{{ route('applicants.sub.store', [$applicant, 'emergency']) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">@include('applicants.sub-forms.emergency', ['record' => null])</div>
                            <div class="flex items-center gap-2 mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Save</button>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('form-emergency').classList.add('hidden')">❌ Cancel</button>
                            </div>
                        </form>
                    </div>
                    @php $em = $pi->emergencyContacts ?? collect(); @endphp
                    @if ($em->count() > 0) @include('applicants.sub-lists.emergency', ['records' => $em, 'routeKey' => 'emergency']) @else <p class="text-sm opacity-50 py-2">No emergency contacts yet.</p> @endif
                </div>
            </section>

            {{-- ============ TAB: Requirements ============ --}}
            <section data-pi-panel="req" class="hidden">
                @unless ($hidePassport)
                <div class="mb-6">
                    <h4 class="font-semibold mb-3">🛂 Passport
                        @if ($applicant->has_passport === 'with')
                            <span class="badge badge-success badge-sm text-xs ml-1">With Passport</span>
                        @endif
                    </h4>
                    <div id="form-passport" class="hidden border rounded-lg p-4 bg-base-200 mb-4">
                        <form action="{{ route('applicants.sub.store', [$applicant, 'passport']) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">@include('applicants.sub-forms.passport', ['record' => null])</div>
                            <div class="flex items-center gap-2 mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Save</button>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('form-passport').classList.add('hidden')">❌ Cancel</button>
                            </div>
                        </form>
                    </div>
                    @php $p = $pi->passport; $pRecs = collect($p ? [$p] : []); @endphp
                    @if ($pRecs->count() > 0) @include('applicants.sub-lists.passport', ['records' => $pRecs, 'routeKey' => 'passport']) @else <p class="text-sm opacity-50 py-2">No passport record yet.</p> @endif
                </div>
                @endunless

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold">📄 Requirements</h4>
                        <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('form-requirements').classList.toggle('hidden')">➕ Add</button>
                    </div>
                    <div id="form-requirements" class="hidden border rounded-lg p-4 bg-base-200 mb-4">
                        <form action="{{ route('applicants.sub.store', [$applicant, 'requirements']) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">@include('applicants.sub-forms.requirements', ['record' => null])</div>
                            <div class="flex items-center gap-2 mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Save</button>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('form-requirements').classList.add('hidden')">❌ Cancel</button>
                            </div>
                        </form>
                    </div>
                    @php $r = $pi->requirements ?? collect(); @endphp
                    @if ($r->count() > 0) @include('applicants.sub-lists.requirements', ['records' => $r, 'routeKey' => 'requirements']) @else <p class="text-sm opacity-50 py-2">No records yet.</p> @endif
                </div>
            </section>

            {{-- ============ TAB: Certifications ============ --}}
            <section data-pi-panel="cert" class="hidden">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-semibold">📜 Certifications</h4>
                    <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('form-certificates').classList.toggle('hidden')">➕ Add</button>
                </div>
                <div id="form-certificates" class="hidden border rounded-lg p-4 bg-base-200 mb-4">
                    <form action="{{ route('applicants.sub.store', [$applicant, 'certificates']) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">@include('applicants.sub-forms.certificates', ['record' => null])</div>
                        <div class="flex items-center gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">💾 Save</button>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('form-certificates').classList.add('hidden')">❌ Cancel</button>
                        </div>
                    </form>
                </div>
                @php $c = $pi->certificates ?? collect(); @endphp
                @if ($c->count() > 0) @include('applicants.sub-lists.certificates', ['records' => $c, 'routeKey' => 'certificates']) @else <p class="text-sm opacity-50 py-2">No records yet.</p> @endif
            </section>

            {{-- ============ TAB: Documents ============ --}}
            <section data-pi-panel="docs" class="hidden">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-semibold">📁 Documents</h4>
                    <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('form-documents').classList.toggle('hidden')">➕ Add</button>
                </div>
                <div id="form-documents" class="hidden border rounded-lg p-4 bg-base-200 mb-4">
                    <form action="{{ route('applicants.documents.store', $applicant) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">@include('applicants.sub-forms.documents', ['record' => null])</div>
                        <div class="flex items-center gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">💾 Save</button>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('form-documents').classList.add('hidden')">❌ Cancel</button>
                        </div>
                    </form>
                </div>
                @php $d = $pi->documents ?? collect(); @endphp
                @if ($d->count() > 0) @include('applicants.sub-lists.documents', ['records' => $d, 'routeKey' => 'documents']) @else <p class="text-sm opacity-50 py-2">No records yet.</p> @endif
            </section>

            {{-- ============ TAB: Upload Files ============ --}}
            <section data-pi-panel="uploads" class="hidden">
                <p class="text-sm opacity-60 py-4">Upload Files module — coming in a later step (Type of Document, upload, encoder + date list).</p>
            </section>

            {{-- ============ TAB: Status ============ --}}
            <section data-pi-panel="status" class="hidden">
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">📊 Status</dt>
                        <dd class="font-medium mt-1">{{ $applicant->statusCode?->label ?? $applicant->status ?? '—' }}</dd>
                    </div>
                </dl>
                <p class="text-sm opacity-60 py-4">FRA / Repat / Status Date details coming with the Status module.</p>
            </section>
        </div>
    </div>
</div>

@push('scripts')
<script>
function piSwitchTab(key) {
    // Reset all tab buttons to the muted inactive style
    document.querySelectorAll('[data-pi-tab]').forEach(b => {
        b.classList.remove('tab-active', 'bg-primary', 'text-primary-content', 'border-primary', 'shadow-md');
        b.classList.add('text-base-content/70', 'hover:bg-base-200', 'border-transparent');
    });
    // Style the clicked tab as the active bookmark
    const btn = document.querySelector('[data-pi-tab="' + key + '"]');
    if (btn) {
        btn.classList.add('tab-active', 'bg-primary', 'text-primary-content', 'border-primary', 'shadow-md');
        btn.classList.remove('text-base-content/70', 'hover:bg-base-200', 'border-transparent');
    }
    // Toggle panels
    document.querySelectorAll('[data-pi-panel]').forEach(p => p.classList.add('hidden'));
    document.querySelector('[data-pi-panel="' + key + '"]')?.classList.remove('hidden');
}
</script>
@endpush
