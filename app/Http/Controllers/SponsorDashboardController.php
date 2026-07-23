<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Sponsor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SponsorDashboardController extends Controller
{
    private function getCurrentSponsor(): Sponsor
    {
        $user = Auth::user();

        $sponsor = Sponsor::where('id_number', $user->username)->first();

        if (! $sponsor) {
            abort(403, 'Sponsor record not found. Please contact administrator.');
        }

        return $sponsor;
    }

    public function index(): View
    {
        $user = Auth::user();

        $lineUpCodes = [0, 1, 2, 3, 4, 5, 6];

        $applicantsQuery = Applicant::with(['position', 'passport', 'workExperiences', 'statusCode'])
            ->whereIn('status_code', $lineUpCodes)
            ->where('status', 'active');

        // Position filter
        if ($position = request('position')) {
            $applicantsQuery->whereHas('position', function ($q) use ($position) {
                $q->where('name', $position);
            });
        }

        $lineupApplicants = $applicantsQuery->orderBy('created_at', 'desc')
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

        $positions = Applicant::whereIn('status_code', $lineUpCodes)
            ->where('status', 'active')
            ->whereHas('position')
            ->with('position')
            ->get()
            ->pluck('position')
            ->filter()
            ->unique('id');

        return view('sponsor.dashboard', compact('user', 'lineupApplicants', 'positions'));
    }

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

    private function exportCsv($applicants, $filename)
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($applicants) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, [
                'Name', 'Position', 'Passport No', 'Experience',
                'Status', 'Email', 'Contact', 'Date Added',
            ]);

            foreach ($applicants as $app) {
                $name = trim(($app->first_name ?? '') . ' ' . ($app->last_name ?? $app->name ?? ''));
                $experience = ($app->is_exabroad ?? false) ? 'Ex-Abroad (' . ($app->total_experience_years ?? 0) . 'yr)' : 'Firstimer';
                $statusName = $app->statusCode?->name ?? 'Pending';

                fputcsv($handle, [
                    $name,
                    $app->position?->name ?? '',
                    $app->passport?->passport_no ?? '',
                    $experience,
                    $statusName,
                    $app->email ?? '',
                    $app->contact ?? '',
                    $app->created_at ? $app->created_at->format('Y-m-d') : '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
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

        return redirect()->back();
    }

    public function select(Request $request): RedirectResponse
    {
        $request->validate([
            'applicant_id' => 'required|exists:applicants,id',
        ]);

        $sponsor = $this->getCurrentSponsor();

        // Check if already selected
        if ($sponsor->applicants()->where('applicant_id', $request->applicant_id)->exists()) {
            throw ValidationException::withMessages([
                'applicant_id' => 'This applicant has already been selected.',
            ]);
        }

        $sponsor->applicants()->attach($request->applicant_id, [
            'selected_at' => now(),
            'status'      => 'active',
        ]);

        return redirect()->route('sponsor.my-applicants')
            ->with('success', 'Applicant selected successfully!');
    }

    public function unselect(Request $request): RedirectResponse
    {
        $request->validate([
            'applicant_id' => 'required|exists:sponsor_applicant,applicant_id,sponsor_id,' . $this->getCurrentSponsor()->id,
        ]);

        $sponsor = $this->getCurrentSponsor();

        $sponsor->applicants()->updateExistingPivot($request->applicant_id, [
            'status' => 'removed',
        ]);

        return redirect()->route('sponsor.my-applicants')
            ->with('success', 'Applicant removed from your list.');
    }

    public function myApplicants(): View
    {
        $user = Auth::user();
        $sponsor = $this->getCurrentSponsor();

        $selectedApplicants = $sponsor->activeApplicants()
            ->with(['position', 'passport', 'workExperiences', 'statusCode', 'requirements'])
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

        return view('sponsor.my-applicants', compact('user', 'sponsor', 'selectedApplicants'));
    }
}
