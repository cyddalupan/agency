<?php

namespace App\Http\Controllers;

use App\Models\ReportTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportTemplateController extends Controller
{
    private const AVAILABLE_COLUMNS = [
        'name', 'email', 'phone', 'gender', 'country', 'status',
        'position', 'employer', 'salary', 'source', 'agent',
        'created_at', 'updated_at',
    ];

    private const DATE_PRESETS = [
        'today', 'this_week', 'this_month', 'last_month', 'this_quarter', 'this_year',
    ];

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
            'columns' => 'nullable|array',
            'columns.*' => 'string|in:' . implode(',', self::AVAILABLE_COLUMNS),
            'group_by' => 'nullable|string|in:' . implode(',', self::AVAILABLE_COLUMNS),
            'sort_by' => 'nullable|string|in:' . implode(',', self::AVAILABLE_COLUMNS),
            'sort_order' => 'nullable|string|in:asc,desc',
            'date_preset' => 'nullable|string|in:' . implode(',', self::DATE_PRESETS),
            'is_active' => 'nullable|boolean',
            'template_sort_order' => 'nullable|integer|min:0|max:999',
        ]);

        $data = [
            'agency_id' => auth()->user()->agency_id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $validated['template_sort_order'] ?? 0,
        ];

        $data['config'] = [
            'columns' => $validated['columns'] ?? ['name', 'status', 'country', 'created_at'],
            'group_by' => $validated['group_by'] ?? null,
            'sort_by' => $validated['sort_by'] ?? 'created_at',
            'sort_order' => $validated['sort_order'] ?? 'desc',
            'date_preset' => $validated['date_preset'] ?? null,
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
            'columns' => 'nullable|array',
            'columns.*' => 'string|in:' . implode(',', self::AVAILABLE_COLUMNS),
            'group_by' => 'nullable|string|in:' . implode(',', self::AVAILABLE_COLUMNS),
            'sort_by' => 'nullable|string|in:' . implode(',', self::AVAILABLE_COLUMNS),
            'sort_order' => 'nullable|string|in:asc,desc',
            'date_preset' => 'nullable|string|in:' . implode(',', self::DATE_PRESETS),
            'is_active' => 'nullable|boolean',
            'template_sort_order' => 'nullable|integer|min:0|max:999',
        ]);

        $data = [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'is_active' => $request->boolean('is_active', $reportTemplate->is_active),
            'sort_order' => $validated['template_sort_order'] ?? $reportTemplate->sort_order ?? 0,
        ];

        $data['config'] = [
            'columns' => $validated['columns'] ?? $reportTemplate->config['columns'] ?? ['name', 'status', 'country', 'created_at'],
            'group_by' => $validated['group_by'] ?? $reportTemplate->config['group_by'] ?? null,
            'sort_by' => $validated['sort_by'] ?? $reportTemplate->config['sort_by'] ?? 'created_at',
            'sort_order' => $validated['sort_order'] ?? $reportTemplate->config['sort_order'] ?? 'desc',
            'date_preset' => $validated['date_preset'] ?? $reportTemplate->config['date_preset'] ?? null,
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

    private function authorizeAccess(ReportTemplate $template): void
    {
        if ($template->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }
    }
}
