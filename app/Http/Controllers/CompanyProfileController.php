<?php

namespace App\Http\Controllers;

use App\Support\CompanyProfileTabs;
use App\Support\InlineImageExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyProfileController extends Controller
{
    /**
     * Show the agency's Company Profile page (read-only view), one tab at a time.
     */
    public function show(Request $request, ?string $tab = null)
    {
        // Legacy links pass the tab as a query string (?tab=organizational-chart)
        // because /company-profile has no {tab} segment; without this they all
        // fell back to tab 1. (Card "FINAS: QMS PREPARATION" follow-up, 2026-10-22.)
        $tab = (string) ($tab ?: $request->query('tab') ?: CompanyProfileTabs::COMPANY_PROFILE);

        $agency = resolve_agency();
        abort_if(! $agency, 403, 'No agency context.');
        abort_unless(CompanyProfileTabs::isValid($tab), 404);

        $content = app_sanitize_page_html($agency->pageTabContent($tab));

        // Give the user guide a usable starting point instead of a blank tab.
        if ($content === '' && $tab === CompanyProfileTabs::SYSTEM_USER_GUIDE) {
            $content = CompanyProfileTabs::userGuideStarter();
        }

        $tabs = CompanyProfileTabs::TABS;
        $activeTab = $tab;

        return view('company-profile.show', compact('agency', 'content', 'tabs', 'activeTab'));
    }

    /**
     * WYSIWYG editor for one tab of the Company Profile page.
     */
    public function edit(Request $request, ?string $tab = null)
    {
        // Honour ?tab= as well as the {tab} route segment. The "Edit this tab"
        // button used route('company-profile.edit', ['tab' => $slug]), which
        // only ever produced /company-profile/edit?tab=... — and because this
        // method ignored the query string, every tab opened (and then saved)
        // the Company Profile tab's content over the tab being edited.
        // (Josh report 2026-10-22: "Hndi ko maedit ibang tab".)
        $tab = (string) ($tab ?: $request->query('tab') ?: CompanyProfileTabs::COMPANY_PROFILE);

        $agency = resolve_agency();
        abort_if(! $agency, 403, 'No agency context.');
        abort_unless(CompanyProfileTabs::isValid($tab), 404);
        $this->authorizeEditing();

        // NOTE: raw HTML is passed through untouched; the view prints it inside a
        // <textarea> so Blade escapes it exactly once. Double-escaping here was
        // what corrupted the page (and its images) on the second save.
        $content = $agency->pageTabContent($tab);

        if ($content === '' && $tab === CompanyProfileTabs::SYSTEM_USER_GUIDE) {
            $content = CompanyProfileTabs::userGuideStarter();
        }

        $tabs = CompanyProfileTabs::TABS;
        $activeTab = $tab;

        return view('company-profile.edit', compact('agency', 'content', 'tabs', 'activeTab'));
    }

    /**
     * Persist the sanitized content of a single tab (other tabs are untouched).
     */
    public function update(Request $request, string $tab = CompanyProfileTabs::COMPANY_PROFILE)
    {
        // Forms may carry the tab as a query string (legacy action); never trust it
        // blindly — fall back to the default tab when it is not a known slug.
        $tab = (string) ($request->query('tab') ?: $tab);
        abort_unless(CompanyProfileTabs::isValid($tab), 404);

        $agency = resolve_agency();
        abort_if(! $agency, 403, 'No agency context.');
        abort_unless(CompanyProfileTabs::isValid($tab), 404);
        $this->authorizeEditing();

        $validated = $request->validate([
            'content' => ['nullable', 'string', 'max:5000000'],
        ]);

        // Move any pasted base64 images to disk first: big inline attributes are
        // dropped by the HTML parser, which used to wipe images on save.
        $html = InlineImageExtractor::extract($validated['content'] ?? '', (int) $agency->id);

        $agency->setPageTabContent($tab, app_sanitize_page_html($html));

        $redirect = $tab === CompanyProfileTabs::COMPANY_PROFILE
            ? route('company-profile.show')
            : route('company-profile.tab', ['tab' => $tab]);

        return redirect($redirect)
            ->with('success', CompanyProfileTabs::label($tab).' updated.');
    }

    /**
     * Upload an image for the editor.
     *
     * Images used to be inlined as base64 into the form field, which made every
     * save a multi-megabyte POST and eventually blew past post_max_size.
     * Uploading them separately keeps saves small and reliable.
     */
    public function uploadImage(Request $request)
    {
        $agency = resolve_agency();
        abort_if(! $agency, 403, 'No agency context.');
        $this->authorizeEditing();

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ], [], ['image' => 'file']);

        $file = $request->file('image');
        $name = Str::random(32).'.'.strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $path = $file->storeAs('company-profile/'.$agency->id, $name, 'public');

        // Relative URL: the same storage is served on every tenant host.
        return response()->json([
            'url' => '/storage/'.$path,
        ]);
    }

    private function authorizeEditing(): void
    {
        abort_unless(in_array(auth()->user()->user_type, ['admin', 'super_admin'], true), 403);
    }
}
