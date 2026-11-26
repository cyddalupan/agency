<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Agency;
use App\Models\ApplicantDocument;
use App\Models\Employer;
use App\Models\JobPosition;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Card 6b0798fb — "LANDAS: Job Order 'Position - Apply Now Link'".
 *
 * The card: "Create a link for every Position. The link can be found on the Job
 * Order page of the position. The link will be used for marketing. Just like QR
 * code, if i click the link it will direct me to Apply Now form where the
 * applicant will fill.up the form. And on the form the Position is fixed to what
 * Position on Job Order sample 'Chef'. Should also have upload resume on the
 * form. The status of the saved forms should be pending"
 *
 * Relationship to ApplicantQrController, which this deliberately mirrors rather
 * than extends: the QR token carries an AGENCY and the position is a free
 * choice on the form. This token carries a JOB ORDER, so the position is not a
 * choice at all — it is a property of the URL. Cyd's decision (2026-11-26) is
 * that both survive: "maybe lets also keep QR code they will ask for a removal
 * if they need". Keeping them separate keeps the QR path's behaviour provably
 * unchanged, which matters because that path is already in the hands of real
 * applicants via printed codes.
 *
 * ## What this controller will NOT do
 *
 * It never accepts position_id from the request. The position is read from the
 * resolved JobPosition row every time, on both GET and POST. A locked <select>
 * is a UI courtesy, not a boundary: anyone can post any field they like, so the
 * server has to be the thing that decides. This is the whole point of the
 * feature — a marketing post for Chef must never be able to file a Nurse.
 */
class JobApplyController extends Controller
{
    /** How long a "recently submitted" marker stays readable to the visitor. */
    public const SUCCESS_SESSION_KEY = 'job_apply_success';

    /**
     * Public entry point. No auth, no tenant in the session: the token is the
     * only credential, and an unknown token is a plain 404 so a guessed URL
     * reveals nothing about which tokens exist.
     */
    public function entry(string $token): View
    {
        $job = JobPosition::findByApplyToken($token);

        if (! $job) {
            abort(404);
        }

        return $this->renderForm($job, $token);
    }

    /**
     * Public submit. Creates the applicant in Pending, for the Job Order's own
     * agency, with the resume attached.
     */
    public function store(Request $request, string $token): RedirectResponse
    {
        $job = JobPosition::findByApplyToken($token);

        if (! $job) {
            abort(404);
        }

        $validated = $request->validate([
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'   => 'required|string|max:255',
            'suffix'      => 'nullable|string|max:255',
            'birthdate'   => 'nullable|date',
            'gender'      => 'nullable|string|max:20',
            'contact'     => 'nullable|string|max:255',
            'email'       => 'nullable|email|max:255',
            'address'     => 'nullable|string',
            // Same rule the in-app document upload uses (PortalDocumentController):
            // PDF plus the common image formats, 5 MB. Kept identical on purpose
            // so an applicant cannot get through here with something the staff
            // side would later refuse.
            'resume'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // The position is OURS, not the visitor's. Read from the row, never
        // from the request. $validated has no position_id key at all.
        $position = $job->position_id ? Position::find($job->position_id) : null;

        $applicant = DB::transaction(function () use ($validated, $job, $position, $request) {
            $attributes = [
                'agency_id'      => $job->agency_id,
                'first_name'     => $validated['first_name'],
                'middle_name'    => $validated['middle_name'] ?? null,
                'last_name'      => $validated['last_name'],
                'suffix'         => $validated['suffix'] ?? null,
                'birthdate'      => $validated['birthdate'] ?? null,
                'gender'         => $validated['gender'] ?? null,
                'contact'        => $validated['contact'] ?? null,
                'email'          => $validated['email'] ?? null,
                'address'        => $validated['address'] ?? null,
                'position_id'    => $job->position_id,
                // Ties the applicant back to the Job Order it came through, so
                // the slot counter and the FRA dashboard see this application.
                'job_id'         => $job->id,
                // Forced, never taken from the request: the card requires
                // Pending, and a public form must not be able to skip review.
                'status_code'    => 0,
                'source'         => 'Apply Now Link',
            ];

            if ($position) {
                $attributes['applicant_type'] = match ($position->category) {
                    Position::CATEGORY_HOUSEHOLD    => Applicant::TYPE_HOUSEHOLD,
                    Position::CATEGORY_PROFESSIONAL => Applicant::TYPE_PROFESSIONAL,
                    default                         => Applicant::TYPE_SKILLED,
                };
            }

            $applicant = Applicant::withoutGlobalScopes()->create($attributes);

            if ($request->hasFile('resume')) {
                $file = $request->file('resume');
                $fileName = time().'_'.$file->getClientOriginalName();
                $filePath = $file->storeAs('applicant-documents', $fileName, 'public');

                ApplicantDocument::create([
                    'agency_id'     => $job->agency_id,
                    'applicant_id'  => $applicant->id,
                    // user_id stays null: no staff user performed this upload.
                    'document_type' => 'resume',
                    'file_name'     => $file->getClientOriginalName(),
                    'file_path'     => $filePath,
                    'mime_type'     => $file->getClientMimeType(),
                    'file_size'     => $file->getSize(),
                    'notes'         => 'Uploaded by the applicant via the Apply Now link.',
                ]);
            }

            return $applicant;
        });

        return redirect()
            ->route('job-apply.entry', ['token' => $token])
            ->with(self::SUCCESS_SESSION_KEY, true);
    }

    private function renderForm(JobPosition $job, string $token): View
    {
        // Both lookups run without the tenant scope, on purpose. This is a
        // PUBLIC route: on a host whose request does not resolve to an agency
        // (the marketing/control host, a vanity domain, a bare IP), the
        // TenantScope refuses to run at all and throws UnboundTenantException
        // rather than silently returning every agency's rows. That is correct
        // behaviour for staff routes and fatal for this one, which was measured
        // as a live 500 on landas.fixitautoservices.com while the same token
        // rendered 200 on gulfcsdi.com.
        //
        // Scoping is not lost by this: $job was already resolved by a unique
        // token and carries its own agency_id, and both rows below are reached
        // FROM that job, so they cannot belong to a different agency.
        $employer = $job->employer_id
            ? Employer::withoutGlobalScopes()->find($job->employer_id)
            : null;

        $position = $job->position_id ? Position::find($job->position_id) : null;

        return view('applicants.job-apply-form', [
            'job'          => $job,
            'token'        => $token,
            'employerName' => $employer?->name,
            'agencyName'   => Agency::find($job->agency_id)?->name,
            'positionName' => $position?->name ?? $job->name,
            'submitted'    => session(self::SUCCESS_SESSION_KEY) === true,
        ]);
    }
}
