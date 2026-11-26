{{--
    Advanced search panel for the Applicants module (card 6b07df0b).
    Shared by applicants/index.blade.php and applicants/withdrawn.blade.php so
    the two lists can never offer different filters.

    Filtering is server-side. Alpine only drives the open/closed state and the
    multi-select checkboxes; nothing is filtered in the browser.
--}}
<div x-data="{ open: {{ $filtersActive ? 'true' : 'false' }} }" class="mb-3">
    <button type="button"
            @click="open = !open"
            class="btn btn-sm btn-outline gap-1"
            :aria-expanded="open.toString()"
            aria-controls="advanced-search-panel">
        <span x-text="open ? '▾' : '▸'">▸</span>
        <span>Advanced search</span>
        @if($filtersActive)
            <span class="badge badge-primary badge-sm">{{ $activeFilterCount }}</span>
        @endif
    </button>

    <div id="advanced-search-panel" x-show="open" x-cloak class="card bg-base-100 shadow-sm mt-2">
        <div class="card-body p-4 gap-3">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                {{-- Applicant number: exact or prefix --}}
                <div class="form-control w-full">
                    <label class="label py-1"><span class="label-text text-xs">Applicant No.</span></label>
                    <input type="text" name="applicant_no" class="input input-bordered input-sm"
                           placeholder="e.g. 2024-00123" value="{{ request('applicant_no') }}">
                </div>

                {{-- Age range, derived from birthdate --}}
                <div class="form-control w-full">
                    <label class="label py-1"><span class="label-text text-xs">Age range</span></label>
                    <div class="flex gap-1 items-center">
                        <input type="number" name="age_min" min="16" max="99" class="input input-bordered input-sm w-full"
                               placeholder="Min" value="{{ request('age_min') }}">
                        <span class="text-xs opacity-60">to</span>
                        <input type="number" name="age_max" min="16" max="99" class="input input-bordered input-sm w-full"
                               placeholder="Max" value="{{ request('age_max') }}">
                    </div>
                </div>

                {{-- Position --}}
                <div class="form-control w-full">
                    <label class="label py-1"><span class="label-text text-xs">Position</span></label>
                    <select name="position" class="select select-bordered select-sm">
                        <option value="">Any position</option>
                        @foreach($positions ?? [] as $position)
                            <option value="{{ $position->id }}" @selected(request('position') == $position->id)>
                                {{ $position->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Registered between --}}
                <div class="form-control w-full">
                    <label class="label py-1"><span class="label-text text-xs">Registered between</span></label>
                    <div class="flex gap-1 items-center">
                        <input type="date" name="created_from" class="input input-bordered input-sm w-full"
                               value="{{ request('created_from') }}">
                        <span class="text-xs opacity-60">to</span>
                        <input type="date" name="created_to" class="input input-bordered input-sm w-full"
                               value="{{ request('created_to') }}">
                    </div>
                </div>
            </div>

            {{-- Multiple statuses --}}
            <div class="form-control w-full">
                <label class="label py-1">
                    <span class="label-text text-xs">Status (pick any number)</span>
                </label>
                <div class="flex flex-wrap gap-x-4 gap-y-1 max-h-32 overflow-y-auto border rounded-box p-2">
                    @foreach($statusCodes as $sc)
                        <label class="label cursor-pointer gap-2 py-0">
                            <input type="checkbox" name="status[]" value="{{ $sc->code }}" class="checkbox checkbox-xs"
                                   @checked(in_array((string) $sc->code, array_map('strval', (array) request('status', [])), true))>
                            <span class="label-text text-xs">{{ $sc->label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Missing documents --}}
            <div class="form-control w-full">
                <label class="label py-1">
                    <span class="label-text text-xs">Missing documents</span>
                    <span class="label-text-alt text-xs opacity-60">Matches applicants whose record shows the document as absent or not recorded</span>
                </label>
                <div class="flex flex-wrap gap-x-4 gap-y-1">
                    @foreach([
                        'passport' => 'Passport',
                        'e_reg' => 'E-Registration',
                        'peos' => 'PEOS',
                        'info_sheet' => 'Info Sheet',
                        'birth_certificate' => 'Birth Certificate',
                        'marriage_certificate' => 'Marriage Certificate',
                    ] as $value => $label)
                        <label class="label cursor-pointer gap-2 py-0">
                            <input type="checkbox" name="missing_docs[]" value="{{ $value }}" class="checkbox checkbox-xs"
                                   @checked(in_array($value, (array) request('missing_docs', []), true))>
                            <span class="label-text text-xs">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-2 justify-end">
                <a href="{{ $clearUrl }}" class="btn btn-ghost btn-sm">Reset</a>
                <button type="submit" class="btn btn-primary btn-sm">Apply filters</button>
            </div>
        </div>
    </div>
</div>
