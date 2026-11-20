<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Cases;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    public function index()
    {
        $cases = Cases::with('applicant')
            ->where('agency_id', auth()->user()->agency_id)
            // (Branch feature) Branch-locked users only see cases of applicants
            // in their own branch.
            ->when(auth()->user()->isBranchLocked(), function ($q) {
                $q->whereHas('applicant', function ($aq) {
                    $aq->where('branch_id', auth()->user()->branch_id);
                });
            })
            ->latest()
            ->paginate(print_per_page(15));

        return response()->json($cases);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'applicant_id' => 'required|exists:applicants,id',
            'status' => 'nullable|in:open,closed',
            'priority' => 'nullable|in:low,normal,high,urgent',
        ]);

        // Security: the case's applicant must belong to the caller's agency
        // (and, for branch-locked users, to their own branch).
        $applicant = Applicant::where('id', $validated['applicant_id'])
            ->where('agency_id', resolve_agency_id())
            ->first();

        if (! $applicant) {
            abort(403, 'Applicant not found in this agency.');
        }

        if (auth()->user()->isBranchLocked() && (int) $applicant->branch_id !== (int) auth()->user()->branch_id) {
            abort(403, 'This applicant belongs to another branch.');
        }

        $validated['agency_id'] = $this->resolveAgencyId();

        $case = Cases::create($validated);

        return response()->json($case, 201);
    }

    public function show($id)
    {
        $case = Cases::withoutGlobalScopes()->findOrFail($id);

        if ($case->agency_id !== auth()->user()->agency_id) {
            abort(403);
        }
        $this->authorizeBranch($case);

        return response()->json($case);
    }

    public function update(Request $request, $id)
    {
        $case = Cases::withoutGlobalScopes()->findOrFail($id);

        if ($case->agency_id !== auth()->user()->agency_id) {
            abort(403);
        }
        $this->authorizeBranch($case);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:open,closed',
            'priority' => 'sometimes|in:low,normal,high,urgent',
        ]);

        $case->update($validated);

        return response()->json($case);
    }

    public function destroy($id)
    {
        $case = Cases::withoutGlobalScopes()->findOrFail($id);

        if ($case->agency_id !== auth()->user()->agency_id) {
            abort(403);
        }
        $this->authorizeBranch($case);

        $case->delete();

        return response()->json(['message' => 'Case deleted successfully.']);
    }

    /**
     * (Branch feature) Branch-locked users may only act on cases belonging to
     * applicants of their own branch.
     */
    private function authorizeBranch(Cases $case): void
    {
        $user = auth()->user();
        if (! $user || ! $user->isBranchLocked()) {
            return;
        }

        $branchId = $case->applicant?->branch_id;
        if ($branchId !== null && (int) $branchId !== (int) $user->branch_id) {
            abort(403, 'This case belongs to another branch.');
        }
    }

    public function search(Request $request)
    {
        $query = Cases::query();

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('applicant', function ($aq) use ($search) {
                      $aq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $query->where('agency_id', auth()->user()->agency_id);

        // (Branch feature) Branch-locked users only see cases of their own branch.
        if (auth()->user()->isBranchLocked()) {
            $query->whereHas('applicant', function ($aq) {
                $aq->where('branch_id', auth()->user()->branch_id);
            });
        }

        $results = $query->with('applicant')->latest()->paginate(print_per_page(15));

        return response()->json($results);
    }
}
