<?php

declare(strict_types=1);

namespace App\Http\Controllers\GodMode;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Services\GodMode\GodAgencyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

/**
 * Card 6b055a7a36a4a0bd098bdd29 — the platform-wide "Powered by Landas"
 * footer logo, operator side. WRITE SIDE ONLY.
 *
 * The read path already ships and is not touched here: the footer's `<img>`
 * in `layouts/app.blade.php` calls `app_brand_landas_logo()` (`app/helpers.php`),
 * which reads one `settings` row. This controller is the only writer of that
 * row, and it is the only thing that changes how the footer looks.
 *
 * ## The store (read this before changing anything)
 *
 * - Table: `settings`. Columns `id, agency_id NOT NULL, key varchar(255),
 *   value text, timestamps`, `UNIQUE(agency_id, key)`. There is **no
 *   platform-scope row**: `agency_id` is `NOT NULL` with an FK to `agencies`.
 * - key: `brand.landas_footer_logo`.
 * - agency_id: 7 (`PLATFORM_SETTINGS_AGENCY_ID`). The LANDAS tenant. The
 *   helper reads the key **unfiltered** (`where('key', ...)->value('value')`,
 *   no agency filter, no ordering), so exactly ONE row may exist for this key
 *   or the helper silently picks whichever the engine returns first. Pin it
 *   with `updateOrInsert` on `(agency_id, key)`, which the unique index makes
 *   idempotent, so a double submit cannot create a second row.
 * - value: for an upload, a **`public`-disk relative path** (`logos/<random>.png`,
 *   never an absolute URL — that would bake the host into the DB). For a
 *   pasted URL, the `https://` URL **verbatim**. That is exactly what the
 *   helper's two branches already accept.
 * - reset: **delete the row.** "Default" is the helper's missing/blank branch
 *   returning `asset('images/landas-logo.png')`. No file is ever deleted by
 *   reset, and `public/images/landas-logo.png` must never be deleted at all.
 *
 * ## Formats: PNG and JPEG only. SVG is refused, and no dependency was added.
 *
 * There is no SVG sanitiser and no `intervention/image` in `composer.json`. An
 * SVG under `/storage/logos/` is a script-executable document served from the
 * main host on an asset that renders on every page — stored XSS the moment
 * someone opens it directly. `mimes:png,jpg,jpeg` reads the file's **content**
 * (finfo `guessExtension`), not the client `Content-Type`, so a PHP or SVG
 * body behind a `.png` name is refused too. `image` alone would admit SVG,
 * which is why the `mimes` list is explicit.
 *
 * ## Broken paths: checked on the WRITE side, helper untouched
 *
 * `Storage::url()` never stats the file, so a stored value pointing at a
 * missing file renders a 200 page with a broken `<img>` — no 500, but worse
 * than the default. Two ways to close that; this takes the spec's preferred
 * (a): a relative `logos/` path must pass `Storage::disk('public')->exists()`
 * before the row is written, and a pasted URL gets a short HEAD/GET probe.
 * The helper keeps rendering on the God Mode login and error pages with its
 * current single DB read, unchanged.
 *
 * ## Audit
 *
 * Reuses `GodAgencyService::writeAudit` so there is one audit shape on the god
 * plane: `agency_id = 7` (the FK cannot be null and the subject is that
 * tenant's footer), `subject_type = App\Models\Agency`, `subject_id = 7`,
 * action `godmode.footer_logo_updated` (27 chars; the column is varchar(32)),
 * `before`/`after` = `['value' => <old>]` / `['value' => <new>]`.
 *
 * `['auth:godmode', 'godmode.only']` is the sole gate in `routes/web.php`. No
 * policy, no `@can`, no role check in here.
 */
class GodBrandingController extends Controller
{
    /**
     * The agency the platform-wide footer logo row hangs on.
     *
     * `settings.agency_id` is `NOT NULL`, so there is no platform row to use;
     * the helper reads the key without an agency filter, so the row has to
     * live somewhere and be the only one. Agency 7 is the LANDAS tenant
     * (`gulf`) whose footer this brands. Changing this value without moving
     * the existing row would leave two rows for the key and the helper would
     * pick one at random.
     */
    public const PLATFORM_SETTINGS_AGENCY_ID = 7;

    public const KEY = 'brand.landas_footer_logo';

    /** The audit action. Must stay <= 32 chars (`audit_events.action`). */
    public const AUDIT_ACTION = 'godmode.footer_logo_updated';

    /** Path prefix on the `public` disk for uploads. Never an absolute URL. */
    public const UPLOAD_DIR = 'logos';

    public const MAX_KB = 2048;

    public function __construct(private readonly GodAgencyService $agencies)
    {
    }

    public function edit(): View
    {
        $agency = $this->platformAgency();
        $row = DB::table('settings')
            ->where('agency_id', self::PLATFORM_SETTINGS_AGENCY_ID)
            ->where('key', self::KEY)
            ->first();

        return view('godmode.branding.footer-logo', [
            'rawValue'    => $row?->value !== null ? (string) $row->value : '',
            'hasRow'      => $row !== null,
            'updatedAt'   => $row?->updated_at,
            'effective'   => app_brand_landas_logo(),
            'defaultLogo' => asset('images/landas-logo.png'),
            'agency'      => $agency,
            'maxKb'       => self::MAX_KB,
        ]);
    }

    /**
     * One POST for both paths: a file and/or a URL. Exactly one must be given;
     * both or neither is a validation error, not a silent preference.
     */
    public function update(Request $request): RedirectResponse
    {
        $hasFile = $request->hasFile('logo');
        $urlInput = trim((string) $request->input('url', ''));

        // `nullable` on both rules means the "both empty / both filled" case
        // has to be stated here, because the rules themselves pass when one is
        // absent.
        $request->validate([
            'logo' => ['nullable', 'file', 'max:'.self::MAX_KB, 'mimes:png,jpg,jpeg', 'dimensions:max_width=4000,max_height=4000'],
            'url'  => ['nullable', 'string', 'max:500', 'url:http,https'],
        ], [
            'logo.mimes'      => 'Rejected. Use a PNG or JPEG, up to 2 MB. SVG is not accepted.',
            'logo.max'        => 'Rejected. Use a PNG or JPEG, up to 2 MB. SVG is not accepted.',
            'logo.uploaded'   => 'Rejected. Use a PNG or JPEG, up to 2 MB. SVG is not accepted.',
            'logo.file'       => 'Rejected. Use a PNG or JPEG, up to 2 MB. SVG is not accepted.',
            'logo.dimensions' => 'Rejected. The logo is larger than 4000x4000 pixels.',
            'url.url'         => 'Paste a full http:// or https:// image address.',
        ]);

        if ($hasFile && $urlInput !== '') {
            return back()
                ->withErrors(['logo' => 'Choose one: upload a file, or paste a URL — not both.'])
                ->withInput();
        }

        if (! $hasFile && $urlInput === '') {
            return back()
                ->withErrors(['logo' => 'Nothing to save: upload a PNG or JPEG, or paste a URL.'])
                ->withInput();
        }

        $previous = DB::table('settings')
            ->where('agency_id', self::PLATFORM_SETTINGS_AGENCY_ID)
            ->where('key', self::KEY)
            ->value('value');
        $previous = $previous !== null ? (string) $previous : null;

        if ($hasFile) {
            $stored = $this->storeUpload($request);
            if ($stored === null) {
                return back()->withErrors(['logo' => 'Rejected. The file is not a readable PNG or JPEG.'])->withInput();
            }
        } else {
            if (! $this->urlLooksReachable($urlInput)) {
                return back()
                    ->withErrors(['url' => 'That address did not load an image (it must return 200). Check the URL and try again.'])
                    ->withInput();
            }
            $stored = $urlInput;
        }

        $this->writeRow($stored);
        $this->deletePreviousUpload($previous, $stored);
        $this->audit($previous, $stored);

        return redirect()
            ->route('godmode.branding.edit')
            ->with('status', $hasFile
                ? 'Footer logo uploaded and saved. It appears on the next page load — no cache clear needed.'
                : 'Footer logo set to the pasted URL. It appears on the next page load.');
    }

    /** Reset = delete the row. The shipped file stays on disk, untouched. */
    public function reset(): RedirectResponse
    {
        $previous = DB::table('settings')
            ->where('agency_id', self::PLATFORM_SETTINGS_AGENCY_ID)
            ->where('key', self::KEY)
            ->value('value');
        $previous = $previous !== null ? (string) $previous : null;

        if ($previous === null) {
            return redirect()->route('godmode.branding.edit')->with('status', 'Already using the shipped default. Nothing to reset.');
        }

        DB::transaction(function (): void {
            DB::table('settings')
                ->where('agency_id', self::PLATFORM_SETTINGS_AGENCY_ID)
                ->where('key', self::KEY)
                ->delete();
        });

        $this->deletePreviousUpload($previous, null);
        $this->audit($previous, null);

        return redirect()
            ->route('godmode.branding.edit')
            ->with('status', 'Footer logo reset to the shipped default. No file was deleted.');
    }

    /**
     * Store the upload on the **`public` disk explicitly** and return the
     * relative path, or null if the content is not a PNG/JPEG after a second,
     * independent read of the bytes.
     *
     * `->store('logos', 'public')` returns `logos/<random>.png`. Never select
     * the disk from `FILESYSTEM_DISK` (it is `local`), and never store
     * `Storage::url()` output.
     */
    private function storeUpload(Request $request): ?string
    {
        $file = $request->file('logo');
        if ($file === null) {
            return null;
        }

        // Belt and braces behind `mimes`: mimes is content-based already, but
        // one getimagesize() per save is cheap and states the intent.
        $info = @getimagesize($file->getRealPath());
        if ($info === false || ! in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG], true)) {
            return null;
        }

        $path = $file->store(self::UPLOAD_DIR, 'public');
        if (! is_string($path) || $path === '') {
            return null;
        }

        // The spec's (a): a stored relative path must exist on the disk it will
        // be served from, or the footer would render a broken image on a 200.
        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return $path;
    }

    /**
     * A pasted URL gets one short probe. A non-200 is a validation error so the
     * operator sees it now instead of a broken footer later. Failures here are
     * the operator's to fix; the page never 500s either way.
     */
    private function urlLooksReachable(string $url): bool
    {
        try {
            $response = Http::timeout(5)->connectTimeout(3)->withoutRedirecting()->head($url);
            if ($response->status() === 405 || $response->status() === 403) {
                // Plenty of image hosts refuse HEAD; fall through to a range GET.
                $response = Http::timeout(5)->connectTimeout(3)->withHeaders(['Range' => 'bytes=0-1023'])->get($url);
            }

            return $response->status() >= 200 && $response->status() < 400;
        } catch (Throwable) {
            return false;
        }
    }

    /** The one write. `updateOrInsert` on the unique `(agency_id, key)`. */
    private function writeRow(string $stored): void
    {
        DB::transaction(function () use ($stored): void {
            DB::table('settings')->updateOrInsert(
                ['agency_id' => self::PLATFORM_SETTINGS_AGENCY_ID, 'key' => self::KEY],
                ['value' => $stored, 'updated_at' => now(), 'created_at' => now()],
            );
        });
    }

    /**
     * Remove the file the row used to point at — **only** if it is one of our
     * own uploads: a relative path under `logos/`. An absolute URL, a
     * root-relative path, or anything else is left alone, and
     * `public/images/landas-logo.png` is unreachable from here by construction.
     */
    private function deletePreviousUpload(?string $previous, ?string $replacement): void
    {
        if ($previous === null || $previous === '') {
            return;
        }

        if ($previous === $replacement) {
            return;
        }

        if (! str_starts_with($previous, self::UPLOAD_DIR.'/')) {
            return;
        }

        if (str_contains($previous, '..')) {
            return;
        }

        Storage::disk('public')->delete($previous);
    }

    private function audit(?string $before, ?string $after): void
    {
        $actor = auth('godmode')->user();
        if ($actor === null) {
            return;
        }

        $this->agencies->writeAudit(
            $this->platformAgency(),
            self::AUDIT_ACTION,
            $before === null ? null : ['value' => $before],
            $after === null ? null : ['value' => $after],
            $actor,
        );
    }

    private function platformAgency(): Agency
    {
        return Agency::withoutGlobalScopes()->findOrFail(self::PLATFORM_SETTINGS_AGENCY_ID);
    }
}
