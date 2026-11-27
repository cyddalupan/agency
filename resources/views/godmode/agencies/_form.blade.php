{{--
    Card 6b07f36226255ccf628c992d — the agency settings form, shared.

    Rendered in two places and nowhere else:
      * godmode/agencies/settings.blade.php  — the standalone page
      * godmode/dashboard.blade.php          — the stacked modal, via a
        <template> per agency so opening it makes no request.

    One markup, one rule set. If a field is added here it appears in both, which
    is the whole point of pulling it out — two copies of a form drift.

    `data-settings-form` marks the form element so the dashboard's JS can find
    it inside the template without depending on the surrounding page.
--}}
<form method="POST"
      action="{{ route('godmode.agencies.settings.update', [$agency->id]) }}"
      class="space-y-4"
      data-testid="agency-settings-form"
      data-settings-form="1">
    @csrf
    {{-- Concurrency token: md5 of the document as rendered. A changed hash
         refuses the save with 422 rather than overwriting someone else. --}}
    <input type="hidden" name="settings_version" value="{{ $state['settings_version'] }}">

    @include('godmode.agencies._fields', ['state' => $state, 'themes' => $themes, 'agency' => $agency])

    <div class="flex justify-end gap-2">
        <button type="button" class="btn btn-sm btn-ghost" data-settings-cancel>Cancel</button>
        <button type="submit" class="btn btn-sm btn-primary" data-action="save-settings">Save settings</button>
    </div>
</form>
