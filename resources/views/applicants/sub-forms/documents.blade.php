<fieldset class="fieldset">
    <legend class="fieldset-legend">Document Type <span class="text-error">*</span></legend>
    <select name="document_type" class="select w-full">
        <option value="">-- Select --</option>
        <option value="passport_copy" @selected(old('document_type') === 'passport_copy')>Passport Copy</option>
        <option value="birth_certificate" @selected(old('document_type') === 'birth_certificate')>Birth Certificate</option>
        <option value="marriage_certificate" @selected(old('document_type') === 'marriage_certificate')>Marriage Certificate</option>
        <option value="nbi_clearance" @selected(old('document_type') === 'nbi_clearance')>NBI Clearance</option>
        <option value="medical_result" @selected(old('document_type') === 'medical_result')>Medical Result</option>
        <option value="contract" @selected(old('document_type') === 'contract')>Contract</option>
        <option value="photo" @selected(old('document_type') === 'photo')>Photo</option>
        <option value="other" @selected(old('document_type') === 'other')>Other</option>
    </select>
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">File <span class="text-error">*</span></legend>
    <input type="file" name="file" class="file-input file-input-bordered w-full" accept="image/*,.pdf" required>
    <label class="fieldset-label">JPG, PNG, WebP, or GIF (max 2MB)</label>
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Notes</legend>
    <textarea name="notes" class="textarea w-full" rows="2" placeholder="Optional notes">{{ old('notes') }}</textarea>
</fieldset>
