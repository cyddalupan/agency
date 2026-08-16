<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FraDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        // KPI counts for dashboard (match actual tab status code ranges)
        $selected = Applicant::whereIn('status_code', [4, 5, 6])->count();
        $onprocess = Applicant::whereIn('status_code', [7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,29,30,31,32,39,40,41,42])->count();
        $flight = Applicant::whereIn('status_code', [21, 22])->count();
        $deployed = Applicant::whereIn('status_code', [8, 33, 34])->count();

        // Chart data: pipeline funnel
        $pipelineStages = [
            ['label' => 'Line Up', 'count' => Applicant::whereIn('status_code', [0,1,2,3])->count()],
            ['label' => 'Selected', 'count' => $selected],
            ['label' => 'On Process', 'count' => $onprocess],
            ['label' => 'Flight', 'count' => $flight],
            ['label' => 'Deployed', 'count' => $deployed],
        ];

        // Chart data: status breakdown (grouped by pipeline zone)
        $statusGroups = [
            ['zone' => 'Line Up', 'count' => Applicant::whereIn('status_code', [0,1,2,3])->count(), 'color' => '#3b82f6'],
            ['zone' => 'Selected', 'count' => $selected, 'color' => '#a855f7'],
            ['zone' => 'On Process', 'count' => $onprocess, 'color' => '#f97316'],
            ['zone' => 'Flight', 'count' => $flight, 'color' => '#eab308'],
            ['zone' => 'Deployed', 'count' => $deployed, 'color' => '#22c55e'],
            ['zone' => 'Cancelled', 'count' => Applicant::whereIn('status_code', [36,37,38])->count(), 'color' => '#ef4444'],
        ];

        // Top positions this agency is hiring for
        $topPositions = DB::table('applicants')
            ->select('position_id', DB::raw('COUNT(*) as count'))
            ->where('agency_id', $user->agency_id)
            ->whereIn('applicants.status_code', [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,29,30,31,32,39,40,41,42])
            ->groupBy('position_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $positionLabels = [];
        $positionCounts = [];
        foreach ($topPositions as $pos) {
            $p = \App\Models\Position::find($pos->position_id);
            $positionLabels[] = $p ? $p->name : '—';
            $positionCounts[] = (int) $pos->count;
        }

        return view('fra.dashboard', compact(
            'user', 'selected', 'onprocess', 'flight', 'deployed',
            'pipelineStages', 'statusGroups',
            'positionLabels', 'positionCounts'
        ));
    }

    public function lineup(): View
    {
        $user = Auth::user();

        $lineUpCodes = [0, 1, 2, 3, 4, 5, 6];

        $applicants = Applicant::with(['position', 'passport', 'workExperiences', 'statusCode'])
            ->whereIn('status_code', $lineUpCodes)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($app) {
                $totalYears = 0;
                foreach ($app->workExperiences as $exp) {
                    $from = $exp->date_from ?? $exp->start_date ?? $exp->from_date;
                    $to   = $exp->date_to ?? $exp->end_date ?? $exp->to_date;
                    if ($from && $to) {
                        $totalYears += max(0, strtotime($to) - strtotime($from)) / (365.25 * 86400);
                    }
                }
                $app->total_experience_years = round($totalYears, 1);
                $app->is_exabroad = $app->workExperiences->count() > 0 && $totalYears > 0;
                return $app;
            });

        return view('fra.lineup', compact('user', 'applicants'));
    }

    public function selected(): View
    {
        $user = Auth::user();

        $selectedCodes = [4, 5, 6];

        $applicants = Applicant::with(['position', 'passport', 'workExperiences', 'statusCode'])
            ->whereIn('status_code', $selectedCodes)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($app) {
                $totalYears = 0;
                foreach ($app->workExperiences as $exp) {
                    $from = $exp->date_from ?? $exp->start_date ?? $exp->from_date;
                    $to   = $exp->date_to ?? $exp->end_date ?? $exp->to_date;
                    if ($from && $to) {
                        $totalYears += max(0, strtotime($to) - strtotime($from)) / (365.25 * 86400);
                    }
                }
                $app->total_experience_years = round($totalYears, 1);
                $app->is_exabroad = $app->workExperiences->count() > 0 && $totalYears > 0;
                return $app;
            });

        return view('fra.selected', compact('user', 'applicants'));
    }

    public function selectedPost(Request $request): View
    {
        $user = Auth::user();

        return view('fra.selected', compact('user'));
    }

    public function bulkRemoveSelected(Request $request): RedirectResponse
    {
        $request->validate([
            'applicant_ids' => 'required|array',
            'applicant_ids.*' => 'exists:applicants,id',
        ]);

        $count = Applicant::whereIn('id', $request->applicant_ids)
            ->whereIn('status_code', [4, 5, 6])
            ->update(['status_code' => 0]);

        return redirect()->route('fra.selected')
            ->with('success', "$count applicant(s) removed from Selected.");
    }

    public function cancelled(): View
    {
        $user = Auth::user();

        $cancelledCodes = [36, 37, 38];

        $applicants = Applicant::with(['position', 'passport', 'workExperiences', 'statusCode'])
            ->whereIn('status_code', $cancelledCodes)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($app) {
                $totalYears = 0;
                foreach ($app->workExperiences as $exp) {
                    $from = $exp->date_from ?? $exp->start_date ?? $exp->from_date;
                    $to   = $exp->date_to ?? $exp->end_date ?? $exp->to_date;
                    if ($from && $to) {
                        $totalYears += max(0, strtotime($to) - strtotime($from)) / (365.25 * 86400);
                    }
                }
                $app->total_experience_years = round($totalYears, 1);
                $app->is_exabroad = $app->workExperiences->count() > 0 && $totalYears > 0;
                return $app;
            });

        return view('fra.cancelled', compact('user', 'applicants'));
    }

    public function onprocess(): View
    {
        $user = Auth::user();

        // On-process status codes: document processing through visa/completion
        $onProcessCodes = [7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 29, 30, 31, 32, 39, 40, 41, 42];

        $applicants = Applicant::with(['position', 'passport', 'workExperiences', 'contractRecords'])
            ->whereIn('status_code', $onProcessCodes)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($app) {
                $totalYears = 0;
                foreach ($app->workExperiences as $exp) {
                    $from = $exp->date_from ?? $exp->start_date ?? $exp->from_date;
                    $to   = $exp->date_to ?? $exp->end_date ?? $exp->to_date;
                    if ($from && $to) {
                        $totalYears += max(0, strtotime($to) - strtotime($from)) / (365.25 * 86400);
                    }
                }
                $app->total_experience_years = round($totalYears, 1);
                $app->is_exabroad = $app->workExperiences->count() > 0 && $totalYears > 0;
                return $app;
            });

        return view('fra.onprocess', compact('user', 'applicants'));
    }

    /**
     * Download Line Up applicants as CSV
     */
    public function selectApplicant(Applicant $applicant)
    {
        $applicant->update(['status_code' => 4]);
        return redirect()->route('fra.lineup')->with('success', $applicant->full_name . ' has been selected and moved to Selected tab.');
    }

    public function viewApplicant(Applicant $applicant): View
    {
        $user = Auth::user();
        $applicant->load(['position', 'passport', 'workExperiences', 'statusCode']);

        $totalYears = 0;
        foreach ($applicant->workExperiences as $exp) {
            $from = $exp->date_from ?? $exp->start_date ?? $exp->from_date;
            $to   = $exp->date_to ?? $exp->end_date ?? $exp->to_date;
            if ($from && $to) {
                $totalYears += max(0, strtotime($to) - strtotime($from)) / (365.25 * 86400);
            }
        }
        $applicant->total_experience_years = round($totalYears, 1);
        $applicant->is_exabroad = $applicant->workExperiences->count() > 0 && $totalYears > 0;

        return view('fra.applicant-view', compact('user', 'applicant'));
    }

    /**
     * Download Line Up applicants as CSV
     */
    public function lineupExport()
    {
        $lineUpCodes = [0, 1, 2, 3, 4, 5, 6];

        $applicants = Applicant::with(['position', 'passport', 'workExperiences', 'statusCode'])
            ->whereIn('status_code', $lineUpCodes)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($app) {
                $totalYears = 0;
                foreach ($app->workExperiences as $exp) {
                    $from = $exp->date_from ?? $exp->start_date ?? $exp->from_date;
                    $to   = $exp->date_to ?? $exp->end_date ?? $exp->to_date;
                    if ($from && $to) {
                        $totalYears += max(0, strtotime($to) - strtotime($from)) / (365.25 * 86400);
                    }
                }
                $app->total_experience_years = round($totalYears, 1);
                $app->is_exabroad = $app->workExperiences->count() > 0 && $totalYears > 0;
                return $app;
            });

        return $this->exportCsv($applicants, 'line-up-applicants.csv');
    }

    public function onprocessExport()
    {
        $user = Auth::user();

        $onProcessCodes = [7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 29, 30, 31, 32, 39, 40, 41, 42];

        $applicants = Applicant::with(['position', 'passport', 'workExperiences', 'contractRecords'])
            ->whereIn('status_code', $onProcessCodes)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($app) {
                $totalYears = 0;
                foreach ($app->workExperiences as $exp) {
                    $from = $exp->date_from ?? $exp->start_date ?? $exp->from_date;
                    $to   = $exp->date_to ?? $exp->end_date ?? $exp->to_date;
                    if ($from && $to) {
                        $totalYears += max(0, strtotime($to) - strtotime($from)) / (365.25 * 86400);
                    }
                }
                $app->total_experience_years = round($totalYears, 1);
                $app->is_exabroad = $app->workExperiences->count() > 0 && $totalYears > 0;
                return $app;
            });

        return $this->exportCsv($applicants, 'on-process-applicants.csv');
    }

    /**
     * Build and return a CSV response with BOM for UTF-8 support.
     */
    private function exportCsv($applicants, $filename)
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $columns = [
            '#', 'Name',
            'Position', 'Passport',
            'Total Experience (Years)', 'First Timer / Ex-Abroad',
            'Process Status',
        ];

        $stream = fopen('php://temp', 'r+');
        fprintf($stream, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM
        fputcsv($stream, $columns);

        foreach ($applicants as $index => $app) {
            $isExabroad = ($app->total_experience_years ?? 0) > 0
                && $app->workExperiences->count() > 0;

            $f = $app->first_name;
            $l = $app->last_name;
            $name = ($f && $l) ? "{$f} {$l}" : ($f ?: $l ?: 'N/A');

            $processStatus = $app->statusCode?->description ?? 'Unknown';

            $row = [
                $index + 1,
                $name,
                optional($app->position)->name ?? '—',
                optional($app->passport)->passport_no ?? '—',
                $app->total_experience_years ?? 0,
                $isExabroad ? 'EXABROAD' : 'FIRSTIMER',
                $processStatus,
            ];

            fputcsv($stream, $row);
        }

        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return new \Illuminate\Http\Response($content, 200, $headers);
    }

    public function account(): View
    {
        $user = Auth::user();

        $userAgency = $user->agency;
        $employer = $user->employer;
        $languages = config('app.supported_languages', [
            'en' => 'English',
            'ar' => 'العربية',
            'zh' => '中文',
            'ja' => '日本語',
        ]);

        return view('fra.account', compact('user', 'languages', 'userAgency', 'employer'));
    }

    public function updateLanguage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'language' => ['required', 'string', 'in:en,ar,zh,ja'],
        ]);

        $user = Auth::user();
        $user->locale = $validated['language'];
        $user->save();

        App::setLocale($validated['language']);
        session(['locale' => $validated['language']]);

        return redirect()->back()->with('success', __('messages.settings_saved'));
    }
}
