{{--
    Card 6b0383d516e52b9c418a9136 — the filter form shared by both tabs.

    One form, one definition. `tab` decides which table the action posts to
    (it is a GET) and which export link is offered, so a copied URL reproduces
    the screen exactly. `page` is never carried: a filter change must land on
    page 1, not on a stale page number of the old result set.

    No `@csrf`, no POST, no method spoofing: this is a read-only screen.
--}}
@php
    $target = $sensitive ? route('godmode.audit.sensitive') : route('godmode.audit.index');
    $agencyValue = $filters['agency'] ?? null;
    $actorValue = $filters['actor'] ?? null;
@endphp

<form method="GET" action="{{ $target }}" class="card bg-base-100 shadow" id="audit-filters">
    <div class="card-body p-4 flex flex-wrap items-end gap-3">
        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">From</span>
            <input type="date" name="from" value="{{ $filters['from'] }}" class="input input-bordered input-sm" id="audit-from">
        </label>

        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">To</span>
            <input type="date" name="to" value="{{ $filters['to'] }}" class="input input-bordered input-sm" id="audit-to">
        </label>

        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">Actor</span>
            <select name="actor" class="select select-bordered select-sm" id="audit-actor">
                <option value="" @selected(! $actorValue)>All actors</option>
                <option value="{{ $actorNoneValue }}" @selected($actorValue === $actorNoneValue)>{{ $systemLabel }} (no actor)</option>
                @foreach ($actors as $id => $name)
                    <option value="{{ $id }}" @selected($actorValue === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
        </label>

        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">Agency</span>
            <select name="agency" class="select select-bordered select-sm" id="audit-agency">
                <option value="" @selected(! $agencyValue)>All agencies</option>
                <option value="{{ $agencyNoneValue }}" @selected($agencyValue === $agencyNoneValue)>{{ $noAgencyLabel }}</option>
                @foreach ($agencies as $id => $name)
                    <option value="{{ $id }}" @selected($agencyValue === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
        </label>

        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">Action</span>
            <select name="action" class="select select-bordered select-sm" id="audit-action">
                <option value="" @selected(! ($filters['action'] ?? null))>All actions</option>
                @foreach ($actions as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['action'] ?? null) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        @if (! $sensitive)
        {{-- subject_type is an audit-events vocabulary; the sensitive tab's
             record types are free-form slugs, so it offers no type filter. --}}
        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">Record type</span>
            <select name="subject_type" class="select select-bordered select-sm" id="audit-subject-type">
                <option value="" @selected(! ($filters['subject_type'] ?? null))>All types</option>
                @foreach ($subjectTypes as $type)
                    <option value="{{ $type }}" @selected(($filters['subject_type'] ?? null) === $type)>{{ class_basename($type) }}</option>
                @endforeach
            </select>
        </label>
        @endif

        <label class="form-control flex-1 min-w-[12rem]">
            <span class="label-text text-xs font-semibold mb-1">Search</span>
            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Reference"
                   class="input input-bordered input-sm" autocomplete="off" id="audit-q">
        </label>

        <label class="form-control">
            <span class="label-text text-xs font-semibold mb-1">Per page</span>
            <select name="per" class="select select-bordered select-sm" onchange="this.form.submit()" id="audit-per">
                @foreach ($perOptions as $option)
                    <option value="{{ $option }}" @selected((int) ($filters['per'] ?? 25) === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </label>

        <button type="submit" class="btn btn-sm btn-neutral">Apply</button>
        <a href="{{ $target }}" class="btn btn-sm btn-ghost">Clear</a>
    </div>
</form>
