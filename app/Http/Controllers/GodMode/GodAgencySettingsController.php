<?php

declare(strict_types=1);

namespace App\Http\Controllers\GodMode;

use App\Http\Controllers\Controller;
use App\Http\Requests\GodMode\GodAgencySettingsRequest;
use App\Models\Agency;
use App\Services\GodMode\GodAgencyService;
use App\Services\GodMode\GodAgencySettingsService;
use App\Services\GodMode\VersionMismatchException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Card 6b03439e6043673862503f2b — per-agency settings, operator side.
 *
 * Thin on purpose: the service owns the document, the merge, the diff and the
 * transaction; the request owns the rules. This class only wires them.
 *
 * `godmode.only` is the sole gate. No policy, no `@can`, no role check.
 * `{agency}` binds unscoped (as `agencies.show` does), so an unknown id is a
 * 404 from the model binding, not a 500.
 */
class GodAgencySettingsController extends Controller
{
    public function __construct(
        private readonly GodAgencySettingsService $settings,
        private readonly GodAgencyService $agencies,
    ) {
    }

    public function edit(Agency $agency): View
    {
        return view('godmode.agencies.settings', [
            'agency'    => $agency,
            'state'     => $this->settings->formState($agency),
            'readOnly'  => $this->settings->readOnlyValues($agency),
            'qrToken'   => $this->settings->maskedQrToken($agency),
            'themes'    => app_color_themes(),
            'fullHost'  => $this->agencies->fullHost($agency->subdomain),
        ]);
    }

    public function update(GodAgencySettingsRequest $request, Agency $agency): RedirectResponse
    {
        try {
            $changed = $this->settings->save(
                $agency,
                $request->settingsInput(),
                $request->settingsVersion(),
                $request->user('godmode'),
                $this->agencies,
            );
        } catch (VersionMismatchException $e) {
            return redirect()
                ->route('godmode.agencies.settings', $agency)
                ->withErrors(['settings_version' => $e->getMessage()])
                ->withInput();
        }

        $back = redirect()->route('godmode.agencies.settings', $agency);

        // A no-op submit says so instead of claiming a save it did not make.
        if ($changed === null) {
            return $back->with('status', 'No changes to save.');
        }

        return $back->with('status', $agency->name.' settings saved ('.count($changed['after']).' key(s) changed).');
    }
}
