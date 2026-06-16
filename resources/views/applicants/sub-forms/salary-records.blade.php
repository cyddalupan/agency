<fieldset class="fieldset">
    <legend class="fieldset-legend">Amount</legend>
    <input type="number" step="0.01" name="amount" value="{{ old('amount', $record->amount ?? '') }}"
           class="input w-full" placeholder="0.00">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Currency</legend>
    <select name="currency" class="select w-full">
        <option value="PHP" @selected(old('currency', $record->currency ?? '') === 'PHP')>PHP</option>
        <option value="USD" @selected(old('currency', $record->currency ?? '') === 'USD')>USD</option>
        <option value="SAR" @selected(old('currency', $record->currency ?? '') === 'SAR')>SAR</option>
        <option value="AED" @selected(old('currency', $record->currency ?? '') === 'AED')>AED</option>
        <option value="KWD" @selected(old('currency', $record->currency ?? '') === 'KWD')>KWD</option>
        <option value="JPY" @selected(old('currency', $record->currency ?? '') === 'JPY')>JPY</option>
    </select>
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Type</legend>
    <select name="type" class="select w-full">
        <option value="">-- Select --</option>
        <option value="basic" @selected(old('type', $record->type ?? '') === 'basic')>Basic</option>
        <option value="allowance" @selected(old('type', $record->type ?? '') === 'allowance')>Allowance</option>
        <option value="other" @selected(old('type', $record->type ?? '') === 'other')>Other</option>
    </select>
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">Notes</legend>
    <input type="text" name="notes" value="{{ old('notes', $record->notes ?? '') }}"
           class="input w-full" placeholder="Optional notes">
</fieldset>
