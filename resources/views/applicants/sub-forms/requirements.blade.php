<fieldset class="fieldset">
    <legend class="fieldset-legend">Type <span class="text-error">*</span></legend>
    <select name="type" class="select w-full">
        <option value="">-- Select --</option>
        <option value="visa" @selected(old('type', $record->type ?? '') === 'visa')>Visa</option>
        <option value="oec" @selected(old('type', $record->type ?? '') === 'oec')>OEC</option>
        <option value="owwa" @selected(old('type', $record->type ?? '') === 'owwa')>OWWA</option>
        <option value="contract" @selected(old('type', $record->type ?? '') === 'contract')>Contract</option>
        <option value="mofa" @selected(old('type', $record->type ?? '') === 'mofa')>MOFA</option>
        <option value="job_offer" @selected(old('type', $record->type ?? '') === 'job_offer')>Job Offer</option>
        <option value="other" @selected(old('type', $record->type ?? '') === 'other')>Other</option>
    </select>
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Reference No.</legend>
    <input type="text" name="reference_no" value="{{ old('reference_no', $record->reference_no ?? '') }}"
           class="input w-full">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Status</legend>
    <select name="status" class="select w-full">
        <option value="pending" @selected(old('status', $record->status ?? '') === 'pending')>Pending</option>
        <option value="submitted" @selected(old('status', $record->status ?? '') === 'submitted')>Submitted</option>
        <option value="approved" @selected(old('status', $record->status ?? '') === 'approved')>Approved</option>
        <option value="rejected" @selected(old('status', $record->status ?? '') === 'rejected')>Rejected</option>
    </select>
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Submitted Date</legend>
    <input type="date" name="submitted_date" value="{{ old('submitted_date', $record->submitted_date ?? '') }}"
           class="input w-full">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Remarks</legend>
    <input type="text" name="remarks" value="{{ old('remarks', $record->remarks ?? '') }}"
           class="input w-full" placeholder="Optional notes">
</fieldset>
