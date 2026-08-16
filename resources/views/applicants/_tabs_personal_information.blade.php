@php
    /**
     * LANDAS "Personal Information" — 6-tab layout (TDD).
     * Grouped from existing sub-entities. Reuses applicants.sub-forms.* and
     * applicants.sub-lists.* partials already covered by passing tests.
     */
    $pi = $applicant->load([
        'passport', 'education', 'certificates', 'requirements',
        'nbi', 'contract', 'tickets', 'visa', 'oec',
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
                                <legend class="fieldset-legend">👩 Mother's Name</legend>
                                <input type="text" name="mother_name" value="{{ old('mother_name', $applicant->mother_name ?? '') }}" class="input w-full" placeholder="Mother's name">
                            </fieldset>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">👩‍💼 Mother's Occupation</legend>
                                <input type="text" name="mother_occupation" value="{{ old('mother_occupation', $applicant->mother_occupation ?? '') }}" class="input w-full" placeholder="Mother's occupation">
                            </fieldset>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">👨 Father's Name</legend>
                                <input type="text" name="father_name" value="{{ old('father_name', $applicant->father_name ?? '') }}" class="input w-full" placeholder="Father's name">
                            </fieldset>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">👨‍🔧 Father's Occupation</legend>
                                <input type="text" name="father_occupation" value="{{ old('father_occupation', $applicant->father_occupation ?? '') }}" class="input w-full" placeholder="Father's occupation">
                            </fieldset>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">👨‍👩‍👧 Number of Siblings</legend>
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
                    </div>
                    <p class="text-sm opacity-60 mb-3">Requirement documents are uploaded under the <strong>Upload Files</strong> tab.</p>
                    @php $r = $pi->requirements ?? collect(); @endphp
                    @if ($r->count() > 0) @include('applicants.sub-lists.requirements', ['records' => $r, 'routeKey' => 'requirements']) @else <p class="text-sm opacity-50 py-2">No requirements recorded yet.</p> @endif
                </div>

                {{-- Resume / CV --}}
                <div class="mt-6 pt-4 border-t border-base-200">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold">📄 Resume/CV</h4>
                        <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('form-resume').classList.toggle('hidden')">➕ Add</button>
                    </div>
                    <div id="form-resume" class="hidden border rounded-lg p-4 bg-base-200 mb-4">
                        <form action="{{ route('applicants.sub.store', [$applicant, 'requirements']) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="type" value="resume">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">@include('applicants.sub-forms.requirements', ['record' => null])</div>
                            <div class="flex items-center gap-2 mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Save Resume</button>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('form-resume').classList.add('hidden')">❌ Cancel</button>
                            </div>
                        </form>
                    </div>
                    @php $resume = $pi->requirements->where('type', 'resume') ?? collect(); @endphp
                    @if ($resume->count() > 0) @include('applicants.sub-lists.requirements', ['records' => $resume, 'routeKey' => 'requirements']) @else <p class="text-sm opacity-50 py-2">No resume uploaded yet.</p> @endif
                </div>

                {{-- NBI --}}
                <div class="mt-6 pt-4 border-t border-base-200">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold">🎫 NBI</h4>
                        <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('form-nbi').classList.toggle('hidden')">➕ Add</button>
                    </div>
                    <div id="form-nbi" class="hidden border rounded-lg p-4 bg-base-200 mb-4">
                        <form action="{{ route('applicants.sub.store', [$applicant, 'nbi']) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">@include('applicants.sub-forms.nbi', ['record' => null])</div>
                            <div class="flex items-center gap-2 mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Save</button>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('form-nbi').classList.add('hidden')">❌ Cancel</button>
                            </div>
                        </form>
                    </div>
                    @php $nbiRecs = $pi->nbi ?? collect(); @endphp
                    @if ($nbiRecs->count() > 0) @include('applicants.sub-lists.nbi', ['records' => $nbiRecs, 'routeKey' => 'nbi']) @else <p class="text-sm opacity-50 py-2">No NBI record yet.</p> @endif
                </div>

                {{-- Requirement checklists + Save Requirements --}}
                <div class="mt-6 pt-4 border-t border-base-200">
                    <h4 class="font-semibold mb-3">✅ Requirement Checklists</h4>
                    <form action="{{ route('applicants.requirements.update', $applicant) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 border rounded-lg p-3 bg-base-200/50 cursor-pointer">
                                <input type="checkbox" name="e_reg" value="1" @checked($applicant->e_reg) class="checkbox checkbox-sm"> E-REG
                            </label>
                            <label class="flex items-center gap-2 border rounded-lg p-3 bg-base-200/50 cursor-pointer">
                                <input type="checkbox" name="peos" value="1" @checked($applicant->peos) class="checkbox checkbox-sm"> PEOS
                            </label>
                            <label class="flex items-center gap-2 border rounded-lg p-3 bg-base-200/50 cursor-pointer">
                                <input type="checkbox" name="info_sheet" value="1" @checked($applicant->info_sheet) class="checkbox checkbox-sm"> Info sheet
                            </label>
                            <label class="flex items-center gap-2 border rounded-lg p-3 bg-base-200/50 cursor-pointer">
                                <input type="checkbox" name="birth_certificate" value="1" @checked($applicant->birth_certificate) class="checkbox checkbox-sm"> Birth Certificate
                            </label>
                            <label class="flex items-center gap-2 border rounded-lg p-3 bg-base-200/50 cursor-pointer">
                                <input type="checkbox" name="marriage_certificate" value="1" @checked($applicant->marriage_certificate) class="checkbox checkbox-sm"> Marriage Certificate
                            </label>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">💾 Save Requirements</button>
                        </div>
                    </form>
                </div>
            </section>

            {{-- ============ TAB: Certifications (PI: 3) ============ --}}
            <section data-pi-panel="cert" class="hidden">
                <h4 class="font-semibold mb-3">📜 Certifications</h4>

                {{-- OMA --}}
                <div class="mt-2 pt-4 border-t border-base-200">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold">🛢️ OMA Certification</h4>
                        <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('form-oma').classList.toggle('hidden')">➕ Add</button>
                    </div>
                    <div id="form-oma" class="hidden border rounded-lg p-4 bg-base-200 mb-4">
                        <form action="{{ route('applicants.sub.store', [$applicant, 'oma']) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">@include('applicants.sub-forms.oma', ['record' => null])</div>
                            <div class="flex items-center gap-2 mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Save OMA</button>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('form-oma').classList.add('hidden')">❌ Cancel</button>
                            </div>
                        </form>
                    </div>
                    @php $omaRecs = $applicant->oma()->get(); @endphp
                    @if ($omaRecs->count() > 0) @include('applicants.sub-lists.oma', ['records' => $omaRecs, 'routeKey' => 'oma']) @else <p class="text-sm opacity-50 py-2">No OMA record yet.</p> @endif
                </div>

                {{-- OWWA --}}
                <div class="mt-6 pt-4 border-t border-base-200">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold">🤝 OWWA Certification</h4>
                        <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('form-owwa').classList.toggle('hidden')">➕ Add</button>
                    </div>
                    <div id="form-owwa" class="hidden border rounded-lg p-4 bg-base-200 mb-4">
                        <form action="{{ route('applicants.sub.store', [$applicant, 'owwa']) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">@include('applicants.sub-forms.owwa', ['record' => null])</div>
                            <div class="flex items-center gap-2 mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Save OWWA</button>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('form-owwa').classList.add('hidden')">❌ Cancel</button>
                            </div>
                        </form>
                    </div>
                    @php $owwaRecs = $applicant->owwa()->get(); @endphp
                    @if ($owwaRecs->count() > 0) @include('applicants.sub-lists.owwa', ['records' => $owwaRecs, 'routeKey' => 'owwa']) @else <p class="text-sm opacity-50 py-2">No OWWA record yet.</p> @endif
                </div>
            </section>

            {{-- ============ TAB: Documents (PI: 4) ============ --}}
            <section data-pi-panel="docs" class="hidden">
                <h4 class="font-semibold mb-3">📋 Documents</h4>

                {{-- OEC --}}
                <div class="mt-2 pt-4 border-t border-base-200">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold">🛂 OEC</h4>
                        <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('form-oec').classList.toggle('hidden')">➕ Add</button>
                    </div>
                    <div id="form-oec" class="hidden border rounded-lg p-4 bg-base-200 mb-4">
                        <form action="{{ route('applicants.sub.store', [$applicant, 'oec']) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">@include('applicants.sub-forms.oec', ['record' => null])</div>
                            <div class="flex items-center gap-2 mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Save OEC</button>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('form-oec').classList.add('hidden')">❌ Cancel</button>
                            </div>
                        </form>
                    </div>
                    @php $oecRecs = $pi->oec ?? collect(); @endphp
                    @if ($oecRecs->count() > 0) @include('applicants.sub-lists.oec', ['records' => $oecRecs, 'routeKey' => 'oec']) @else <p class="text-sm opacity-50 py-2">No OEC record yet.</p> @endif
                </div>

                {{-- VISA --}}
                <div class="mt-6 pt-4 border-t border-base-200">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold">✈️ VISA</h4>
                        <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('form-visa').classList.toggle('hidden')">➕ Add</button>
                    </div>
                    <div id="form-visa" class="hidden border rounded-lg p-4 bg-base-200 mb-4">
                        <form action="{{ route('applicants.sub.store', [$applicant, 'visa']) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">@include('applicants.sub-forms.visa', ['record' => null])</div>
                            <div class="flex items-center gap-2 mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Save VISA</button>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('form-visa').classList.add('hidden')">❌ Cancel</button>
                            </div>
                        </form>
                    </div>
                    @php $visaRecs = $pi->visa ?? collect(); @endphp
                    @if ($visaRecs->count() > 0) @include('applicants.sub-lists.visa', ['records' => $visaRecs, 'routeKey' => 'visa']) @else <p class="text-sm opacity-50 py-2">No VISA record yet.</p> @endif
                </div>

                {{-- Contract --}}
                <div class="mt-6 pt-4 border-t border-base-200">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold">📄 Contract</h4>
                        <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('form-contract').classList.toggle('hidden')">➕ Add</button>
                    </div>
                    <div id="form-contract" class="hidden border rounded-lg p-4 bg-base-200 mb-4">
                        <form action="{{ route('applicants.sub.store', [$applicant, 'contract']) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">@include('applicants.sub-forms.contract', ['record' => null])</div>
                            <div class="flex items-center gap-2 mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Save Contract</button>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('form-contract').classList.add('hidden')">❌ Cancel</button>
                            </div>
                        </form>
                    </div>
                    @php $contractRecs = $applicant->contract()->get(); @endphp
                    @if ($contractRecs->count() > 0) @include('applicants.sub-lists.contract', ['records' => $contractRecs, 'routeKey' => 'contract']) @else <p class="text-sm opacity-50 py-2">No contract record yet.</p> @endif
                </div>

                {{-- Ticket --}}
                <div class="mt-6 pt-4 border-t border-base-200">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold">🎟️ Ticket</h4>
                        <button type="button" class="btn btn-primary btn-xs" onclick="document.getElementById('form-ticket').classList.toggle('hidden')">➕ Add</button>
                    </div>
                    <div id="form-ticket" class="hidden border rounded-lg p-4 bg-base-200 mb-4">
                        <form action="{{ route('applicants.sub.store', [$applicant, 'ticket']) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">@include('applicants.sub-forms.ticket', ['record' => null])</div>
                            <div class="flex items-center gap-2 mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Save Ticket</button>
                                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('form-ticket').classList.add('hidden')">❌ Cancel</button>
                            </div>
                        </form>
                    </div>
                    @php $ticketRecs = $pi->tickets ?? collect(); @endphp
                    @if ($ticketRecs->count() > 0) @include('applicants.sub-lists.ticket', ['records' => $ticketRecs, 'routeKey' => 'ticket']) @else <p class="text-sm opacity-50 py-2">No ticket record yet.</p> @endif
                </div>
            </section>

            {{-- ============ TAB: Upload Files (PI: 5) ============ --}}
            <section data-pi-panel="uploads" class="hidden">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-semibold">📁 Upload Files</h4>
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

            {{-- ============ TAB: Status ============ --}}
            <section data-pi-panel="status" class="hidden">
                <form action="{{ route('applicants.status', $applicant) }}" method="POST" class="border rounded-lg p-4 bg-base-200/40">
                    @csrf
                    @method('PATCH')

                    <h4 class="font-semibold mb-3">📊 Applicant Status</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @include('applicants.sub-forms.status')
                    </div>

                    <div class="flex items-center gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-sm">💾 Save Status</button>
                    </div>
                </form>

                @if (isset($statusHistory) && $statusHistory->count() > 0)
                <div class="mt-6">
                    <h4 class="font-semibold mb-3">📜 Status History</h4>
                    <div class="overflow-x-auto">
                        <table class="table table-sm w-full">
                            <thead>
                                <tr>
                                    <th>Created</th>
                                    <th>Status</th>
                                    <th>Sub Status</th>
                                    <th>Agency/Employer</th>
                                    <th>Country</th>
                                    <th>Remarks</th>
                                    <th>Handled By</th>
                                    <th>Status Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($statusHistory as $entry)
                                @php
                                    $meta = $entry->metadata ?? [];
                                    $newCode = $meta['new_status'] ?? null;
                                    $st = $newCode !== null ? ($statusCodeMap[$newCode] ?? null) : null;
                                    $subStatus = $meta['sub_status'] ?? $applicant->fra;
                                    $agencyName = $meta['agency'] ?? $applicant->agency?->name;
                                    $employerName = $meta['employer'] ?? $applicant->employer?->name;
                                    $countryName = $meta['country'] ?? $applicant->country?->name;
                                    $remarks = $meta['remarks'] ?? $applicant->remarks;
                                    $statusDate = $meta['status_date'] ?? $applicant->status_date?->toDateString();
                                    $color = $st?->color ?? '#6b7280';
                                    $label = $st?->label ?? ($newCode !== null ? "Status {$newCode}" : '—');
                                    $subLabel = app_fra_options()[$subStatus] ?? $subStatus ?? '—';
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap text-sm">{{ $entry->created_at?->format('M d, Y h:i A') ?? '—' }}</td>
                                    <td>
                                        <span class="badge badge-sm font-medium whitespace-nowrap" style="background-color: {{ $color }}20; color: {{ $color }}">{{ $label }}</span>
                                    </td>
                                    <td class="text-sm">{{ $subLabel }}</td>
                                    <td class="text-sm">
                                        {{ $agencyName ?? '—' }}@if ($employerName) <span class="opacity-50">/</span> {{ $employerName }}@endif
                                    </td>
                                    <td class="text-sm">{{ $countryName ?? '—' }}</td>
                                    <td class="text-sm">{{ $remarks ?? '—' }}</td>
                                    <td class="font-medium text-sm">{{ $entry->user?->name ?? '—' }}</td>
                                    <td class="whitespace-nowrap text-sm">{{ $statusDate ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                <div class="mt-6">
                    <h4 class="font-semibold mb-3">📜 Status History</h4>
                    <p class="text-sm opacity-50 py-2">No status changes yet.</p>
                </div>
                @endif
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
