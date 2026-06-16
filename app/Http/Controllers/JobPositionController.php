<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\JobPosition;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobPositionController extends Controller
{
    public function index(Employer $employer): View
    {
        $jobPositions = $employer->jobPositions()->latest()->paginate(15);
        return view('job-positions.index', compact('employer', 'jobPositions'));
    }

    public function create(Employer $employer): View
    {
        $basePositions = Position::orderBy('name')->get();
        return view('job-positions.create', compact('employer', 'basePositions'));
    }

    public function store(Request $request, Employer $employer): RedirectResponse
    {
        $validated = $request->validate([
            'position_id'      => 'nullable|exists:positions,id',
            'name'             => 'required|string|max:255',
            'content'          => 'nullable|string',
            'gender_preference' => 'nullable|string|in:male,female,any',
            'salary'           => 'nullable|numeric|min:0',
            'salary_currency'  => 'nullable|string|max:3',
            'total_slots'      => 'nullable|integer|min:0',
        ]);

        $validated['agency_id'] = auth()->user()->agency_id;
        $validated['employer_id'] = $employer->id;
        $validated['occupied'] = 0;
        $validated['status'] = 'open';

        JobPosition::create($validated);

        return redirect()->route('employers.job-positions.index', $employer)
            ->with('success', 'Job position created successfully.');
    }

    public function show(Employer $employer, JobPosition $jobPosition): View
    {
        $jobPosition->load('position');
        return view('job-positions.show', compact('employer', 'jobPosition'));
    }

    public function edit(Employer $employer, JobPosition $jobPosition): View
    {
        $basePositions = Position::orderBy('name')->get();
        return view('job-positions.edit', compact('employer', 'jobPosition', 'basePositions'));
    }

    public function update(Request $request, Employer $employer, JobPosition $jobPosition): RedirectResponse
    {
        $validated = $request->validate([
            'position_id'      => 'nullable|exists:positions,id',
            'name'             => 'required|string|max:255',
            'content'          => 'nullable|string',
            'gender_preference' => 'nullable|string|in:male,female,any',
            'salary'           => 'nullable|numeric|min:0',
            'salary_currency'  => 'nullable|string|max:3',
            'total_slots'      => 'nullable|integer|min:0',
            'status'           => 'nullable|string|in:open,closed,filled',
        ]);

        $jobPosition->update($validated);

        return redirect()->route('employers.job-positions.index', $employer)
            ->with('success', 'Job position updated successfully.');
    }

    public function destroy(Employer $employer, JobPosition $jobPosition): RedirectResponse
    {
        $jobPosition->delete();

        return redirect()->route('employers.job-positions.index', $employer)
            ->with('success', 'Job position deleted successfully.');
    }
}
