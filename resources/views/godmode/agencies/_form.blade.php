{{--
    Card 6b0344184136c21243a7af8b — shared create/edit form.
    Writes identity + status only; settings/logo/profile belong to 6b03439e.
--}}
@php
    $isEdit = $agency->exists;
    $action = $isEdit ? route('godmode.agencies.update', $agency) : route('godmode.agencies.store');
    $status = old('status', $agency->status ?: 'active');
    // Never let an edit form submit `pending`: it is registration's to grant.
    if (! in_array($status, ['active', 'inactive'], true)) {
        $status = 'active';
    }
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5" id="agency-form">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="card bg-base-100 shadow">
        <div class="card-body grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="form-control">
                <span class="label-text font-semibold mb-1">Agency name <span class="text-error">*</span></span>
                <input type="text" name="name" value="{{ old('name', $agency->name) }}" required maxlength="255"
                       class="input input-bordered @error('name') input-error @enderror">
                @error('name')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
            </label>

            <label class="form-control">
                <span class="label-text font-semibold mb-1">Subdomain <span class="text-error">*</span></span>
                <div class="flex items-center gap-1">
                    <input type="text" name="subdomain" value="{{ old('subdomain', $agency->subdomain) }}" required maxlength="63"
                           class="input input-bordered w-full @error('subdomain') input-error @enderror" placeholder="gulf">
                    <span class="text-xs opacity-60 whitespace-nowrap">.landas.fixitautoservices.com</span>
                </div>
                @if ($isEdit)
                    <span class="text-xs text-warning mt-1">
                        Changing this changes the agency's URL and every bookmark. It cannot be left empty.
                    </span>
                @else
                    <span class="text-xs opacity-60 mt-1">Letters, numbers, dashes and underscores. `www`, `api`, `godmode`, `admin`, `mail`, `portal`, `app`, `static` are reserved.</span>
                @endif
                @error('subdomain')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
            </label>

            <label class="form-control">
                <span class="label-text font-semibold mb-1">Email</span>
                <input type="email" name="email" value="{{ old('email', $agency->email) }}" maxlength="255"
                       class="input input-bordered @error('email') input-error @enderror">
                @error('email')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
            </label>

            <label class="form-control">
                <span class="label-text font-semibold mb-1">Contact person <span class="text-error">*</span></span>
                <input type="text" name="contact_person" value="{{ old('contact_person', $agency->contact_person) }}" required maxlength="255"
                       class="input input-bordered @error('contact_person') input-error @enderror">
                @error('contact_person')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
            </label>

            <label class="form-control">
                <span class="label-text font-semibold mb-1">City</span>
                <input type="text" name="city" value="{{ old('city', $agency->city) }}" maxlength="100"
                       class="input input-bordered @error('city') input-error @enderror">
                @error('city')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
            </label>

            <label class="form-control">
                <span class="label-text font-semibold mb-1">Address</span>
                <input type="text" name="address" value="{{ old('address', $agency->address) }}" maxlength="255"
                       class="input input-bordered @error('address') input-error @enderror">
                @error('address')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
            </label>

            <label class="form-control">
                <span class="label-text font-semibold mb-1">Declared branches <span class="text-error">*</span></span>
                <input type="number" name="num_branches" value="{{ old('num_branches', $agency->num_branches ?: 1) }}" required min="1" max="200"
                       class="input input-bordered @error('num_branches') input-error @enderror">
                <span class="text-xs opacity-60 mt-1">What the agency declared. The list shows the real branch count beside it.</span>
                @error('num_branches')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
            </label>

            <label class="form-control">
                <span class="label-text font-semibold mb-1">Status <span class="text-error">*</span></span>
                <select name="status" class="select select-bordered @error('status') select-error @enderror">
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive (archived)</option>
                </select>
                <span class="text-xs opacity-60 mt-1">`pending` is only reachable from public registration.</span>
                @error('status')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
            </label>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <button type="submit" class="btn btn-primary" id="agency-submit">{{ $isEdit ? 'Save changes' : 'Create agency' }}</button>
        <a href="{{ $isEdit ? route('godmode.agencies.show', $agency) : route('godmode.agencies.index') }}" class="btn btn-ghost">Cancel</a>
    </div>

    <p class="text-xs opacity-60">
        Owner accounts are created separately: this form creates the tenant, not its users.
    </p>
</form>
