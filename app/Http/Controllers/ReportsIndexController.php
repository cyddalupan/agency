<?php

namespace App\Http\Controllers;

use App\Models\ReportTemplate;
use App\Services\ReportBuilder;
use Illuminate\Http\Request;

class ReportsIndexController extends Controller
{
    public function index()
    {
        $templates = ReportTemplate::where('agency_id', auth()->user()->agency_id)
            ->active()
            ->latest()
            ->get();

        return view('reports.index', compact('templates'));
    }

    public function preview(ReportTemplate $reportTemplate)
    {
        if ($reportTemplate->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        $builder = new ReportBuilder($reportTemplate);
        $rows = $builder->get();

        return view('reports.preview', [
            'template' => $reportTemplate,
            'rows' => $rows,
            'columns' => $reportTemplate->config['columns'] ?? [],
        ]);
    }
}
