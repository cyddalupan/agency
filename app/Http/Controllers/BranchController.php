<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(Request $request): View
    {
        $branches = Branch::where('agency_id', auth()->user()->agency_id)
            ->orderBy('name')
            ->paginate(print_per_page(15));

        return view('branches.index', compact('branches'));
    }

    public function create(): View
    {
        return view('branches.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:100',
            'status'  => 'nullable|string|in:active,inactive',
        ]);

        $agencyId = $this->resolveAgencyId();
        if (! $agencyId) {
            return back()->withErrors(['agency' => 'No agency context. Please log in with an agency account.'])->withInput();
        }

        Branch::create(array_merge($validated, ['agency_id' => $agencyId]));

        return redirect()->route('branches.index')
            ->with('success', 'Branch created successfully.');
    }

    /**
     * Resolve the branch by primary key WITHOUT the tenant scope, so the
     * documented answer to "another agency's branch" stays 403 rather than
     * becoming a 404 from route-model binding (card
     * 6b008b8d3ca32bfcb58ce970). Branch is tenant-scoped now; the explicit
     * check below is what decides, and it is the only thing that runs.
     */
    private function findBranch($branch): Branch
    {
        return $branch instanceof Branch
            ? $branch
            : Branch::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->findOrFail($branch);
    }

    public function edit($branch): View
    {
        $branch = $this->findBranch($branch);

        abort_unless($branch->agency_id === auth()->user()->agency_id, 403);

        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, $branch): RedirectResponse
    {
        $branch = $this->findBranch($branch);

        abort_unless($branch->agency_id === auth()->user()->agency_id, 403);

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:100',
            'status'  => 'nullable|string|in:active,inactive',
        ]);

        $branch->update($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy($branch): RedirectResponse
    {
        $branch = $this->findBranch($branch);

        abort_unless($branch->agency_id === auth()->user()->agency_id, 403);

        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'Branch deleted successfully.');
    }
}
