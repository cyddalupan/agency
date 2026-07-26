<?php

namespace App\Http\Controllers;

use App\Models\ReportTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportTemplateController extends Controller
{
    public function index(): View
    {
        $templates = ReportTemplate::where('agency_id', auth()->user()->agency_id)
            ->latest()
            ->get();

        return view('report-templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('report-templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:applicant_report,statistics,transactions',
            'config' => 'nullable|json',
            'is_active' => 'nullable|boolean',
        ]);

        $data = [
            'agency_id' => auth()->user()->agency_id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'config' => $request->has('config') ? json_decode($validated['config'], true) : [
                'columns' => ['name', 'status', 'country', 'created_at'],
                'group_by' => null,
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'date_preset' => null,
            ],
            'is_active' => $request->boolean('is_active', true),
        ];

        ReportTemplate::create($data);

        return redirect()->route('report-templates.index')
            ->with('success', 'Report template created successfully.');
    }

    public function edit(ReportTemplate $reportTemplate): View
    {
        $this->authorizeAccess($reportTemplate);

        return view('report-templates.edit', ['template' => $reportTemplate]);
    }

    public function update(Request $request, ReportTemplate $reportTemplate): RedirectResponse
    {
        $this->authorizeAccess($reportTemplate);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:applicant_report,statistics,transactions',
            'config' => 'nullable|json',
            'is_active' => 'nullable|boolean',
        ]);

        $data = [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'config' => $request->has('config') ? json_decode($validated['config'], true) : $reportTemplate->config,
            'is_active' => $request->boolean('is_active', $reportTemplate->is_active),
        ];

        $reportTemplate->update($data);

        return redirect()->route('report-templates.index')
            ->with('success', 'Report template updated successfully.');
    }

    public function destroy(ReportTemplate $reportTemplate): RedirectResponse
    {
        $this->authorizeAccess($reportTemplate);

        $reportTemplate->delete();

        return redirect()->route('report-templates.index')
            ->with('success', 'Report template deleted successfully.');
    }

    /**
     * Authorize that the authenticated user's agency owns the template.
     */
    private function authorizeAccess(ReportTemplate $template): void
    {
        if ($template->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }
    }
}
