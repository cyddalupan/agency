<?php

namespace App\Http\Controllers;

use App\Models\ReportGeneratedLog;
use App\Models\ReportTemplate;
use App\Services\ReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportsIndexController extends Controller
{
    public function index()
    {
        $templates = ReportTemplate::where('agency_id', auth()->user()->agency_id)
            ->active()
            ->ordered()
            ->get();

        return view('reports.index', compact('templates'));
    }

    public function preview(ReportTemplate $reportTemplate)
    {
        if ($reportTemplate->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        $supportedTypes = ['applicant_report'];
        if (!in_array($reportTemplate->type, $supportedTypes)) {
            return view('reports.preview', [
                'template' => $reportTemplate,
                'rows' => collect(),
                'columns' => $reportTemplate->config['columns'] ?? [],
                'unsupported_type' => true,
            ]);
        }

        $builder = new ReportBuilder($reportTemplate);
        $rows = $builder->paginate(25);

        return view('reports.preview', [
            'template' => $reportTemplate,
            'rows' => $rows,
            'columns' => $reportTemplate->config['columns'] ?? [],
        ]);
    }

    public function downloadPdf(ReportTemplate $reportTemplate)
    {
        if ($reportTemplate->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        $builder = new ReportBuilder($reportTemplate);
        $rows = $builder->get();
        $columns = $reportTemplate->config['columns'] ?? [];
        $agency = auth()->user()->agency;

        $pdf = Pdf::loadView('reports.pdf.applicant_report', compact('reportTemplate', 'rows', 'columns', 'agency'))
            ->setPaper('a4', 'landscape');

        $this->logGeneration($reportTemplate, 'pdf');

        $filename = Str::slug($reportTemplate->name) . '-report.pdf';

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    public function downloadCsv(ReportTemplate $reportTemplate)
    {
        if ($reportTemplate->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        $builder = new ReportBuilder($reportTemplate);
        $rows = $builder->get();
        $columns = $reportTemplate->config['columns'] ?? [];

        $headerLabels = array_map(fn($c) => [
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'gender' => 'Gender',
            'country' => 'Country',
            'status' => 'Status',
            'position' => 'Position',
            'employer' => 'Employer',
            'salary' => 'Salary',
            'source' => 'Source',
            'agent' => 'Agent',
            'created_at' => 'Date Created',
            'updated_at' => 'Last Updated',
        ][$c] ?? ucfirst($c), $columns);

        $callback = function () use ($headerLabels, $columns, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headerLabels);
            foreach ($rows as $row) {
                $line = array_map(fn($c) => $row[$c] ?? '', $columns);
                fputcsv($handle, $line);
            }
            fclose($handle);
        };

        $this->logGeneration($reportTemplate, 'csv');

        $filename = Str::slug($reportTemplate->name) . '-report.csv';

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function logGeneration(ReportTemplate $template, string $format): void
    {
        ReportGeneratedLog::create([
            'agency_id' => auth()->user()->agency_id,
            'user_id' => auth()->id(),
            'report_template_id' => $template->id,
            'format' => $format,
        ]);
    }
}
