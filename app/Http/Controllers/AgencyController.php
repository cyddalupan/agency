<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Services\SensitiveActionLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgencyController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of agencies.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Agency::class);

        $query = Agency::orderBy('name');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $agencies = $query->paginate(20);

        return view('agencies.index', compact('agencies'));
    }

    /**
     * Show a single agency's management page.
     */
    public function show(Agency $agency)
    {
        $this->authorize('viewAny', Agency::class);

        $users = $agency->users()
            ->orderBy('name')
            ->get();

        return view('agencies.show', compact('agency', 'users'));
    }

    /**
     * Show the form for creating a new agency.
     */
    public function create()
    {
        $this->authorize('create', Agency::class);

        return view('agencies.create');
    }

    /**
     * Store a newly created agency.
     */
    /**
     * Extract a clean subdomain slug from whatever the user typed.
     * Accepts: "myagency", "my-agency.landas.fixitautoservices.com",
     * "https://my-agency.landas.fixitautoservices.com", etc.
     */
    private function parseSubdomain(string $input): string
    {
        // Strip protocol & path
        $host = parse_url($input, PHP_URL_HOST) ?? $input;

        // Everything before the first dot is the subdomain slug
        return strtolower(explode('.', $host)[0]);
    }

    /**
     * Store a newly created agency.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Agency::class);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'string', 'max:255'],
        ]);

        // Normalise: extract just the subdomain slug
        $validated['subdomain'] = $this->parseSubdomain($validated['subdomain']);

        // Validate the extracted slug
        $validator = validator(['subdomain' => $validated['subdomain']], [
            'subdomain' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('agencies')],
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated['status'] = 'active';

        Agency::create($validated);

        return redirect()->route('agencies.index')
            ->with('success', 'Agency created successfully.');
    }

    /**
     * Show the form for editing an agency.
     */
    public function edit(Agency $agency)
    {
        $this->authorize('update', $agency);

        return view('agencies.edit', compact('agency'));
    }

    /**
     * Update the specified agency.
     */
    public function update(Request $request, Agency $agency)
    {
        $this->authorize('update', $agency);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'string', 'max:255'],
            'logo'      => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
        ]);

        // Normalise: extract just the subdomain slug
        $validated['subdomain'] = $this->parseSubdomain($validated['subdomain']);

        // Validate the extracted slug
        $validator = validator(['subdomain' => $validated['subdomain']], [
            'subdomain' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('agencies')->ignore($agency->id)],
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Handle icon (logo) upload
        if ($request->hasFile('logo')) {
            if ($agency->logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($agency->logo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($agency->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $agency->update($validated);

        return redirect()->route('agencies.index')
            ->with('success', 'Agency updated successfully.');
    }

    /**
     * Deactivate the specified agency.
     */
    public function deactivate(Agency $agency)
    {
        $this->authorize('deactivate', $agency);

        $agency->update(['status' => 'inactive']);

        SensitiveActionLogger::agencyStatusChange($agency, 'inactive');

        return redirect()->route('agencies.index')
            ->with('success', 'Agency deactivated successfully.');
    }

    /**
     * Activate the specified agency.
     */
    public function activate(Agency $agency)
    {
        $this->authorize('activate', $agency);

        $agency->update(['status' => 'active']);

        SensitiveActionLogger::agencyStatusChange($agency, 'active');

        return redirect()->route('agencies.index')
            ->with('success', 'Agency activated successfully.');
    }
}
