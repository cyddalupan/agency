<?php

namespace App\Http\Controllers;

use App\Models\StatusCode;
use App\Models\StatusTransition;
use App\Services\StatusCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatusCodeController extends Controller
{
    public function index(): View
    {
        $statuses = StatusCode::orderBy('sort_order')->get();

        return view('status-codes.index', compact('statuses'));
    }

    public function create(): View
    {
        // Suggest next free code (max existing + 1)
        $nextCode = (StatusCode::max('code') ?? -1) + 1;

        return view('status-codes.create', compact('nextCode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code'        => 'required|integer|min:0|unique:status_codes,code',
            'label'       => 'required|string|max:255',
            'label_saudi' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'color'       => 'nullable|string|max:7',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        StatusCode::create($validated);

        return redirect()->route('status-codes.index')
            ->with('success', 'Status created successfully.');
    }

    public function edit(StatusCode $statusCode): View
    {
        return view('status-codes.edit', compact('statusCode'));
    }

    public function update(Request $request, StatusCode $statusCode): RedirectResponse
    {
        $validated = $request->validate([
            'label'       => 'required|string|max:255',
            'label_saudi' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'color'       => 'nullable|string|max:7',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $statusCode->update($validated);

        return redirect()->route('status-codes.index')
            ->with('success', 'Status updated successfully.');
    }

    public function destroy(StatusCode $statusCode): RedirectResponse
    {
        // Safeguard: do not delete statuses referenced by transition rules or applicants.
        $transitionCount = StatusTransition::where('from_code', $statusCode->code)
            ->orWhere('to_code', $statusCode->code)
            ->count();

        $applicantCount = \App\Models\Applicant::where('status_code', $statusCode->code)->count();

        if ($transitionCount > 0 || $applicantCount > 0) {
            return redirect()->route('status-codes.index')
                ->with('error', 'Cannot delete: status is referenced by ' . $transitionCount . ' transition rule(s) and ' . $applicantCount . ' applicant(s).');
        }

        $statusCode->delete();

        return redirect()->route('status-codes.index')
            ->with('success', 'Status deleted successfully.');
    }
}
