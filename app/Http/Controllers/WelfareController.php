<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\ApplicantWelfareRecord;
use App\Models\Country;
use App\Models\Employer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * WELFARE module (Mjolnir card 6af81f6a, 2026-11-14).
 *
 * "Separate the Welfare from the Personal Information of the Applicants and
 * create a new module named WELFARE under BACKOUT, CANCELLED, & REPAT."
 *
 * CSDI and FINAS only. The table columns, filters and "Create Welfare" action
 * follow the card exactly; every query is scoped to the user's agency.
 */
class WelfareController extends Controller
{
    /** Agencies that offer the module (card: "FINAS and CSDI only"). */
    public const AGENCIES = ['finas', 'csdi'];

    public static function enabled(?int $agencyId): bool
    {
        return \App\Support\ModuleAccess::isAnyAgency($agencyId, self::AGENCIES);
    }

    public function index(Request $request): View
    {
        $agencyId = $this->agencyId();
        $query = $this->filtered($request, $agencyId);

        $records = $query->with(['applicant'])
            ->orderByDesc('date_of_contact')
            ->orderByDesc('id')
            ->paginate(print_per_page(25))
            ->withQueryString();

        return view('welfare.index', [
            'records'   => $records,
            'countries' => Country::orderBy('name')->get(),
            'employers' => Employer::orderBy('name')->get(),
            'total'     => $this->filtered($request, $agencyId)->count(),
        ]);
    }

    /** "Add Extract to CSV". */
    public function export(Request $request)
    {
        $agencyId = $this->agencyId();
        $rows = $this->filtered($request, $agencyId)
            ->orderByDesc('date_of_contact')->orderByDesc('id')->get();

        $headers = [
            'Name', 'Passport No.', 'DMW E-Reg ID', 'Country', 'Job Site City',
            'Position', 'FRA', 'Remarks / Issues / Actions Taken',
            'Encoder', 'Date of Contact', 'Direct Employer', 'Contact Abroad',
        ];

        return response()->streamDownload(function () use ($rows, $headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $w) {
                fputcsv($out, [
                    $w->ofw_full_name,
                    $w->passport_number,
                    $w->dmw_ereg_id,
                    $w->country_of_destination,
                    $w->job_site_city,
                    $w->position_designation,
                    $w->foreign_recruitment_agency,
                    $w->remarks,
                    $w->encoder ?: $w->logged_by,
                    optional($w->date_of_contact)->format('Y-m-d'),
                    $w->direct_employer_name,
                    $w->contact_no_abroad,
                ]);
            }
            fclose($out);
        }, 'welfare-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Type-ahead for the Welfare form (Josh, 2026-11-14): "Name: Display all
     * applicants. Encoder can type the name and display the related name.
     * Fill-up all the fields related to the Applicant's data."
     *
     * Returns the applicant plus everything the form can pre-fill, so picking a
     * name fills the whole Migrant Worker Profile in one step.
     */
    public function lookupApplicant(Request $request)
    {
        $agencyId = (int) (auth()->user()->agency_id ?? 0);
        abort_if($agencyId === 0, 403);
        abort_unless(self::enabled($agencyId), 403, 'The Welfare module is not available for this agency.');

        $term = trim((string) $request->query('q'));
        if ($term === '') {
            return response()->json(['applicants' => []]);
        }

        $applicants = Applicant::with(['passport', 'country', 'position', 'agent'])
            ->where('agency_id', $agencyId)
            ->searchName($term)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(15)
            ->get()
            ->map(fn (Applicant $a) => [
                'id'            => $a->id,
                'name'          => $a->full_name,
                'passport'      => $a->passport->passport_no ?? '',
                'country'       => $a->country->name ?? '',
                'position'      => $a->position->name ?? '',
                // The card renames Agent -> Marketing, so the FRA box carries
                // the marketing agency's name.
                'fra'           => $a->agent->name ?? $a->agent->full_name ?? '',
                'label'         => trim($a->full_name . ' · ' . ($a->passport->passport_no ?? 'no passport')),
            ]);

        return response()->json(['applicants' => $applicants]);
    }

    public function create(Request $request): View
    {
        $agencyId = $this->agencyId();

        // Pre-fill when "Create Welfare" is opened from an applicant.
        $applicant = null;
        if ($request->filled('applicant_id')) {
            $applicant = Applicant::with(['passport', 'country', 'position'])
                ->where('agency_id', $agencyId)
                ->find($request->integer('applicant_id'));
        }

        return view('welfare.form', [
            'record'    => null,
            'applicant' => $applicant,
            'countries' => Country::orderBy('name')->get(),
            'employers' => Employer::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $agencyId = $this->agencyId();
        $validated = $this->validateRecord($request);

        // Link to an applicant when one was picked; otherwise the record stands alone.
        $applicantId = null;
        if ($request->filled('applicant_id')) {
            $applicantId = Applicant::where('agency_id', $agencyId)
                ->whereKey($request->integer('applicant_id'))->value('id');
        }

        $validated['agency_id']    = $agencyId;
        $validated['applicant_id'] = $applicantId;
        $validated['encoder']   = auth()->user()->name;
        $validated['logged_by'] = ($validated['logged_by'] ?? null) ?: auth()->user()->name;

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $validated['document_path'] = $file->store("welfare/{$agencyId}", 'public');
            $validated['document_name'] = $file->getClientOriginalName();
        }

        ApplicantWelfareRecord::create($validated);

        return redirect()->route('welfare.index')
            ->with('success', 'Welfare record created.');
    }

    public function edit(ApplicantWelfareRecord $welfare): View
    {
        $this->authorizeRecord($welfare);

        return view('welfare.form', [
            'record'    => $welfare,
            'applicant' => $welfare->applicant,
            'countries' => Country::orderBy('name')->get(),
            'employers' => Employer::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ApplicantWelfareRecord $welfare): RedirectResponse
    {
        $this->authorizeRecord($welfare);
        $validated = $this->validateRecord($request);

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            if ($welfare->document_path) {
                Storage::disk('public')->delete($welfare->document_path);
            }
            $validated['document_path'] = $file->store("welfare/{$welfare->agency_id}", 'public');
            $validated['document_name'] = $file->getClientOriginalName();
        }

        $validated['encoder'] = $welfare->encoder ?: auth()->user()->name;
        $welfare->update($validated);

        return redirect()->route('welfare.index')->with('success', 'Welfare record updated.');
    }

    public function destroy(ApplicantWelfareRecord $welfare): RedirectResponse
    {
        $this->authorizeRecord($welfare);
        if ($welfare->document_path) {
            Storage::disk('public')->delete($welfare->document_path);
        }
        $welfare->delete();

        return redirect()->route('welfare.index')->with('success', 'Welfare record deleted.');
    }

    public function document(ApplicantWelfareRecord $welfare)
    {
        $this->authorizeRecord($welfare);
        abort_unless($welfare->document_path, 404);

        return Storage::disk('public')->download(
            $welfare->document_path,
            $welfare->document_name ?: basename($welfare->document_path)
        );
    }

    // ------------------------------------------------------------------ helpers

    private function filtered(Request $request, int $agencyId)
    {
        $query = ApplicantWelfareRecord::where('agency_id', $agencyId);

        // Card filters: Name, Passport, DMW E-Reg ID, Country, Position, FRA.
        if ($v = trim((string) $request->query('name'))) {
            $query->where('ofw_full_name', 'like', "%{$v}%");
        }
        if ($v = trim((string) $request->query('passport'))) {
            $query->where('passport_number', 'like', "%{$v}%");
        }
        if ($v = trim((string) $request->query('dmw_ereg_id'))) {
            $query->where('dmw_ereg_id', 'like', "%{$v}%");
        }
        if ($v = trim((string) $request->query('country'))) {
            $query->where('country_of_destination', 'like', "%{$v}%");
        }
        if ($v = trim((string) $request->query('position'))) {
            $query->where('position_designation', 'like', "%{$v}%");
        }
        if ($v = trim((string) $request->query('fra'))) {
            $query->where('foreign_recruitment_agency', 'like', "%{$v}%");
        }

        return $query;
    }

    private function agencyId(): int
    {
        $agencyId = (int) (auth()->user()->agency_id ?? 0);
        abort_if($agencyId === 0, 403, 'No agency context.');
        abort_unless(self::enabled($agencyId), 403, 'The Welfare module is not available for this agency.');

        return $agencyId;
    }

    private function authorizeRecord(ApplicantWelfareRecord $record): void
    {
        abort_unless((int) $record->agency_id === $this->agencyId(), 403);
    }

    /** Validation for the five yes/no indicators and their "specify" boxes. */
    private function indicatorRules(): array
    {
        $rules = [];
        foreach (array_keys(ApplicantWelfareRecord::INDICATORS) as $key) {
            $rules[$key]            = 'nullable|in:yes,no';
            $rules[$key.'_specify'] = 'nullable|string|max:1000';
        }

        return $rules;
    }

    private function validateRecord(Request $request): array
    {
        $validated = $request->validate([
            'ofw_full_name'             => 'required|string|max:255',
            'passport_number'           => 'nullable|string|max:255',
            'dmw_ereg_id'               => 'nullable|string|max:255',
            'country_of_destination'    => 'nullable|string|max:255',
            'job_site_city'             => 'nullable|string|max:255',
            'position_designation'      => 'nullable|string|max:255',
            'foreign_recruitment_agency'=> 'nullable|string|max:255',
            'direct_employer_name'      => 'nullable|string|max:255',
            'contact_no_abroad'         => 'nullable|string|max:255',
            'contact_person_phone'      => 'nullable|string|max:255',
            'date_of_contact'           => 'nullable|date',
            'logged_by'                 => 'nullable|string|max:255',
            'remarks'                   => 'nullable|string|max:5000',
            'document'                  => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'milestones'                => 'nullable|array',
            'milestones.*'              => 'in:' . implode(',', array_keys(ApplicantWelfareRecord::MILESTONES)),
        ] + $this->indicatorRules());

        // Josh's form (2026-11-14) carries the milestones and the five working
        // condition indicators, so they are persisted here too.
        $validated['milestones'] = array_values(array_intersect(
            (array) $request->input('milestones', []),
            array_keys(ApplicantWelfareRecord::MILESTONES)
        ));

        foreach (array_keys(ApplicantWelfareRecord::INDICATORS) as $key) {
            $validated[$key]           = $request->input($key) === 'yes' ? 'yes' : ($request->input($key) === 'no' ? 'no' : null);
            $validated[$key.'_specify'] = $request->input($key.'_specify') ?: null;
        }

        unset($validated['document']);
        $validated['date_of_contact'] = $validated['date_of_contact'] ?? now()->toDateString();

        return $validated;
    }
}
