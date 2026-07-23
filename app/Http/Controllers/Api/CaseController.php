<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    public function index()
    {
        $cases = Cases::with('applicant')
            ->where('agency_id', auth()->user()->agency_id)
            ->latest()
            ->paginate(15);

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

        return response()->json($case);
    }

    public function update(Request $request, $id)
    {
        $case = Cases::withoutGlobalScopes()->findOrFail($id);

        if ($case->agency_id !== auth()->user()->agency_id) {
            abort(403);
        }

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

        $case->delete();

        return response()->json(['message' => 'Case deleted successfully.']);
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

        $results = $query->with('applicant')->latest()->paginate(15);

        return response()->json($results);
    }
}
