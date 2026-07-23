<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\JobPosition;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $validated['agency_id'] = $this->resolveAgencyId();
        if (! $validated['agency_id']) { return back()->withErrors(['agency' => 'No agency context. Please log in with an agency account.'])->withInput(); }
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

    // ================================================================
    //  Employer Portal Methods (uses Auth user's employer)
    // ================================================================

    public function employerIndex(): View
    {
        $employer = Auth::user()->employer;
        $jobPositions = $employer->jobPositions()->latest()->paginate(15);
        return view('employer.job-positions.index', compact('employer', 'jobPositions'));
    }

    public function employerCreate(): View
    {
        $employer = Auth::user()->employer;
        $basePositions = Position::orderBy('name')->get();
        return view('employer.job-positions.create', compact('employer', 'basePositions'));
    }

    public function employerStore(Request $request): RedirectResponse
    {
        $employer = Auth::user()->employer;

        $validated = $request->validate([
            'position_id'      => 'nullable|exists:positions,id',
            'name'             => 'required|string|max:255',
            'content'          => 'nullable|string',
            'gender_preference' => 'nullable|string|in:male,female,any',
            'salary'           => 'nullable|numeric|min:0',
            'salary_currency'  => 'nullable|string|max:3',
            'total_slots'      => 'nullable|integer|min:0',
        ]);

        $validated['agency_id'] = $this->resolveAgencyId() ?? $employer->agency_id;
        if (! $validated['agency_id']) { return back()->withErrors(['agency' => 'No agency context. Please log in with an agency account.'])->withInput(); }
        $validated['employer_id'] = $employer->id;
        $validated['occupied'] = 0;
        $validated['status'] = 'open';

        JobPosition::create($validated);

        return redirect()->route('employer.job-positions.index')
            ->with('success', 'Job position created successfully.');
    }

    public function employerShow(JobPosition $jobPosition): View
    {
        $employer = Auth::user()->employer;
        abort_if($jobPosition->employer_id !== $employer->id, 403);
        $jobPosition->load('position');
        return view('employer.job-positions.show', compact('employer', 'jobPosition'));
    }

    public function employerEdit(JobPosition $jobPosition): View
    {
        $employer = Auth::user()->employer;
        abort_if($jobPosition->employer_id !== $employer->id, 403);
        $basePositions = Position::orderBy('name')->get();
        return view('employer.job-positions.edit', compact('employer', 'jobPosition', 'basePositions'));
    }

    public function employerUpdate(Request $request, JobPosition $jobPosition): RedirectResponse
    {
        $employer = Auth::user()->employer;
        abort_if($jobPosition->employer_id !== $employer->id, 403);

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

        return redirect()->route('employer.job-positions.index')
            ->with('success', 'Job position updated successfully.');
    }

    public function employerDestroy(JobPosition $jobPosition): RedirectResponse
    {
        $employer = Auth::user()->employer;
        abort_if($jobPosition->employer_id !== $employer->id, 403);

        $jobPosition->delete();

        return redirect()->route('employer.job-positions.index')
            ->with('success', 'Job position deleted successfully.');
    }
}
