<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AgencyBrandingController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show the branding settings page.
     */
    public function __invoke(Agency $agency)
    {
        $this->authorize('branding', $agency);

        return view('agencies.branding', compact('agency'));
    }

    /**
     * Update the agency branding (logo, favicon, colors).
     */
    public function update(Request $request, Agency $agency)
    {
        $this->authorize('branding', $agency);

        $validated = $request->validate([
            'logo'            => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'favicon'         => ['nullable', 'mimes:ico,png,jpg,jpeg,svg', 'max:512'],
            'primary_color'   => ['nullable', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            'secondary_color' => ['nullable', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($agency->logo && Storage::disk('public')->exists($agency->logo)) {
                Storage::disk('public')->delete($agency->logo);
            }

            $path = $request->file('logo')->store('logos', 'public');
            $agency->logo = $path;
        }

        // Get current settings
        $settings = $agency->settings ?? [];

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            // Delete old favicon if exists
            if (isset($settings['favicon']) && Storage::disk('public')->exists($settings['favicon'])) {
                Storage::disk('public')->delete($settings['favicon']);
            }

            $faviconPath = $request->file('favicon')->store('favicons', 'public');
            $settings['favicon'] = $faviconPath;
        }

        // Update colors
        if ($request->filled('primary_color')) {
            $settings['primary_color'] = $request->primary_color;
        }

        if ($request->filled('secondary_color')) {
            $settings['secondary_color'] = $request->secondary_color;
        }

        $agency->settings = $settings;
        $agency->save();

        return redirect()->route('agencies.branding', $agency)
            ->with('success', 'Branding updated successfully.');
    }
}
