<fieldset class="fieldset">
    <legend class="fieldset-legend">OEC No.</legend>
    <input type="text" name="oec_no" value="{{ old('oec_no', $record->oec_no ?? '') }}"
           class="input w-full" placeholder="e.g. OEC-2026-12345">
</fieldset>
<fieldset class="fieldset">
    <legend class="fieldset-legend">OEC Release</legend>
    <input type="date" name="oec_release" value="{{ old('oec_release', $record->oec_release ?? '') }}"
           class="input w-full">
</fieldset>
