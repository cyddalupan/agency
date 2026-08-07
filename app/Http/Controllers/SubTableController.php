<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SubTableController extends Controller
{
    /**
     * Validation rules per sub-table type.
     */
    protected function rulesFor(string $type): array
    {
        return match ($type) {
            'education' => [
                'level'          => 'nullable|string|max:50',
                'school'         => 'nullable|string|max:255',
                'degree'         => 'nullable|string|max:255',
                'year_graduated'  => 'nullable|string|max:10',
                'remarks'        => 'nullable|string',
            ],
            'passport' => [
                'passport_no'    => 'nullable|string|max:50',
                'issue_date'     => 'nullable|date',
                'expiry_date'    => 'nullable|date|after:issue_date',
                'place_of_issue' => 'nullable|string|max:255',
                'file'           => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,pdf|max:2048',
                'file_path'      => 'nullable|string|max:255',
            ],
            'certificates' => [
                'type'           => 'required|string|max:100',
                'certificate_no' => 'nullable|string|max:100',
                'issue_date'     => 'nullable|date',
                'expiry_date'    => 'nullable|date',
                'file'           => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,pdf|max:2048',
                'file_path'      => 'nullable|string|max:255',
                'remarks'        => 'nullable|string',
            ],
            'requirements' => [
                'type'           => 'required|string|max:100',
                'reference_no'   => 'nullable|string|max:100',
                'status'         => 'nullable|string|in:pending,submitted,approved,rejected',
                'submitted_date' => 'nullable|date',
                'approved_date'  => 'nullable|date',
                'file'           => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,pdf|max:2048',
                'file_path'      => 'nullable|string|max:255',
                'remarks'        => 'nullable|string',
            ],
            'work-experiences' => [
                'company'         => 'nullable|string|max:255',
                'position'        => 'nullable|string|max:255',
                'from_date'       => 'nullable|date',
                'to_date'         => 'nullable|date|after:from_date',
                'responsibilities' => 'nullable|string',
            ],
            'skills' => [
                'skill_name'  => 'required|string|max:255|exists:skills,name',
                'proficiency' => 'nullable|string|in:beginner,intermediate,expert',
            ],
            'languages' => [
                'name'        => 'required|string|max:255|exists:languages,name',
                'proficiency' => 'nullable|string|in:beginner,intermediate,expert',
            ],
            'contacts' => [
                'contact' => 'nullable|string|max:100',
                'type'    => 'nullable|string|max:50',
            ],
            'references' => [
                'name'     => 'required|string|max:255',
                'contact'  => 'nullable|string|max:100',
                'relation' => 'nullable|string|max:100',
                'position' => 'nullable|string|max:255',
            ],
            'salary-records' => [
                'amount'   => 'nullable|numeric|min:0',
                'currency' => 'nullable|string|size:3',
                'type'     => 'nullable|string|max:100',
                'notes'    => 'nullable|string',
            ],
            'spouse' => [
                'partner_name'      => 'required|string|max:255',
                'number_of_children' => 'nullable|integer|min:0',
            ],
            'family' => [
                'name'       => 'required|string|max:255',
                'relation'   => 'nullable|string|max:100',
                'occupation' => 'nullable|string|max:255',
            ],
            'emergency' => [
                'name'         => 'required|string|max:255',
                'relationship' => 'nullable|string|max:100',
                'contact'      => 'nullable|string|max:100',
            ],
            'nbi' => [
                'nbi_no'      => 'nullable|string|max:100',
                'issue_date'  => 'nullable|date',
                'expiry_date' => 'nullable|date|after:issue_date',
            ],
            'oec' => [
                'oec_no'      => 'nullable|string|max:100',
                'oec_release' => 'nullable|date',
            ],
            'visa' => [
                'visa_no'          => 'nullable|string|max:100',
                'visa_type'        => 'nullable|string|max:100',
                'received_date'    => 'nullable|date',
                'stamped_date'     => 'nullable|date',
                'expiry_date'      => 'nullable|date',
                'approved_musaned' => 'nullable|string|max:10',
            ],
            'contract' => [
                'rfp'               => 'nullable|string|max:100',
                'sponsor'           => 'nullable|string|max:255',
                'sponsor_id'        => 'nullable|string|max:100',
                'contact'           => 'nullable|string|max:100',
                'address'           => 'nullable|string|max:255',
                'contract_received' => 'nullable|date',
                'contract_signed'   => 'nullable|date',
            ],
            'ticket' => [
                'airline'        => 'nullable|string|max:255',
                'flight_date'    => 'nullable|date',
                'flight_time'    => 'nullable|string|max:20',
                'flight_remarks' => 'nullable|string',
            ],
            'oma' => [
                'from_date'     => 'nullable|date',
                'to_date'       => 'nullable|date|after_or_equal:from_date',
                'released_date' => 'nullable|date',
            ],
            'owwa' => [
                'from_date'        => 'nullable|date',
                'to_date'          => 'nullable|date|after_or_equal:from_date',
                'released_date'    => 'nullable|date',
                'local_flight_date' => 'nullable|date',
            ],
            default => [],
        };
    }

    /**
     * Fillable fields per type.
     */
    protected function fillableFor(string $type): array
    {
        return match ($type) {
            'education'        => ['agency_id', 'applicant_id', 'level', 'school', 'degree', 'year_graduated', 'remarks'],
            'passport'         => ['agency_id', 'applicant_id', 'passport_no', 'issue_date', 'expiry_date', 'place_of_issue', 'file_path'],
            'certificates'     => ['agency_id', 'applicant_id', 'type', 'certificate_no', 'issue_date', 'expiry_date', 'file_path', 'remarks'],
            'requirements'     => ['agency_id', 'applicant_id', 'type', 'reference_no', 'status', 'submitted_date', 'approved_date', 'file_path', 'remarks'],
            'work-experiences' => ['agency_id', 'applicant_id', 'company', 'position', 'from_date', 'to_date', 'responsibilities'],
            'skills'           => ['agency_id', 'applicant_id', 'skill_name', 'proficiency'],
            'languages'        => ['agency_id', 'applicant_id', 'name', 'proficiency'],
            'contacts'         => ['agency_id', 'applicant_id', 'contact', 'type'],
            'references'       => ['agency_id', 'applicant_id', 'name', 'contact', 'relation', 'position'],
            'salary-records'   => ['agency_id', 'applicant_id', 'amount', 'currency', 'type', 'notes'],
            'spouse'           => ['agency_id', 'applicant_id', 'partner_name', 'number_of_children'],
            'family'           => ['agency_id', 'applicant_id', 'name', 'relation', 'occupation'],
            'emergency'        => ['agency_id', 'applicant_id', 'name', 'relationship', 'contact'],
            'nbi'              => ['agency_id', 'applicant_id', 'nbi_no', 'issue_date', 'expiry_date'],
            'oec'              => ['agency_id', 'applicant_id', 'oec_no', 'oec_release'],
            'visa'             => ['agency_id', 'applicant_id', 'visa_no', 'visa_type', 'received_date', 'stamped_date', 'expiry_date', 'approved_musaned'],
            'contract'         => ['agency_id', 'applicant_id', 'rfp', 'sponsor', 'sponsor_id', 'contact', 'address', 'contract_received', 'contract_signed'],
            'ticket'           => ['agency_id', 'applicant_id', 'airline', 'flight_date', 'flight_time', 'flight_remarks'],
            'oma'              => ['agency_id', 'applicant_id', 'from_date', 'to_date', 'released_date'],
            'owwa'             => ['agency_id', 'applicant_id', 'from_date', 'to_date', 'released_date', 'local_flight_date'],
            default            => [],
        };
    }

    protected function modelClass(string $type): string
    {
        return match ($type) {
            'education'        => \App\Models\ApplicantEducation::class,
            'passport'         => \App\Models\ApplicantPassport::class,
            'certificates'     => \App\Models\ApplicantCertificate::class,
            'requirements'     => \App\Models\ApplicantRequirement::class,
            'work-experiences'  => \App\Models\ApplicantWorkExperience::class,
            'skills'           => \App\Models\ApplicantSkill::class,
            'languages'        => \App\Models\ApplicantLanguage::class,
            'contacts'         => \App\Models\ApplicantContact::class,
            'references'       => \App\Models\ApplicantReference::class,
            'salary-records'   => \App\Models\ApplicantSalaryRecord::class,
            'spouse'          => \App\Models\ApplicantSpouse::class,
            'family'          => \App\Models\ApplicantFamilyMember::class,
            'emergency'       => \App\Models\ApplicantEmergencyContact::class,
            'nbi'             => \App\Models\ApplicantNbi::class,
            'oec'             => \App\Models\ApplicantOec::class,
            'visa'            => \App\Models\ApplicantVisa::class,
            'contract'        => \App\Models\ApplicantContract::class,
            'ticket'          => \App\Models\ApplicantTicket::class,
            'oma'             => \App\Models\ApplicantOma::class,
            'owwa'            => \App\Models\ApplicantOwWa::class,
            default           => abort(404, "Unknown sub-table: {$type}"),
        };
    }

    public function store(Request $request, Applicant $applicant, string $type)
    {
        $modelClass = $this->modelClass($type);
        $validator = Validator::make($request->all(), $this->rulesFor($type));

        if ($validator->fails()) {
            return redirect()->route('applicants.show', $applicant)
                ->withErrors($validator, $type)
                ->withInput();
        }

        $data = $request->only($this->fillableFor($type));
        $data['applicant_id'] = $applicant->id;
        $data['agency_id'] = $this->resolveAgencyId();
        if (! $data['agency_id']) { return back()->withErrors(['agency' => 'No agency context. Please log in with an agency account.'])->withInput(); }

        // Handle file upload for certificates / requirements (and now passport)
        if ($request->hasFile('file')) {
            // Delete old file if replacing (passport is hasOne)
            if ($type === 'passport') {
                $existing = $modelClass::where('applicant_id', $applicant->id)->first();
                if ($existing && $existing->file_path) {
                    Storage::disk('public')->delete($existing->file_path);
                }
            }
            $file = $request->file('file');
            $path = $file->store('applicant-sub-files', 'public');
            $data['file_path'] = $path;
        }

        // Passport is hasOne — overwrite instead of create duplicate
        if ($type === 'passport') {
            $modelClass::updateOrCreate(
                ['applicant_id' => $applicant->id],
                $data
            );
        } else {
            $modelClass::create($data);
        }

        return redirect()->route('applicants.show', $applicant)
            ->with('success', __('Sub-table entry added.'));
    }

    public function update(Request $request, Applicant $applicant, string $type, $id)
    {
        $modelClass = $this->modelClass($type);
        $record = $modelClass::where('applicant_id', $applicant->id)->findOrFail($id);

        $validator = Validator::make($request->all(), $this->rulesFor($type));
        if ($validator->fails()) {
            return redirect()->route('applicants.show', $applicant)
                ->withErrors($validator, $type)
                ->withInput();
        }

        $data = $request->only($this->fillableFor($type));

        // Handle file upload for certificates / requirements
        if ($request->hasFile('file')) {
            // Delete old file
            if ($record->file_path) {
                Storage::disk('public')->delete($record->file_path);
            }
            $file = $request->file('file');
            $path = $file->store('applicant-sub-files', 'public');
            $data['file_path'] = $path;
        }

        $record->update($data);

        return redirect()->route('applicants.show', $applicant)
            ->with('success', __('Sub-table entry updated.'));
    }

    public function destroy(Request $request, Applicant $applicant, string $type, $id)
    {
        $modelClass = $this->modelClass($type);
        $record = $modelClass::where('applicant_id', $applicant->id)->findOrFail($id);
        $record->delete();

        return redirect()->route('applicants.show', $applicant)
            ->with('success', __('Sub-table entry deleted.'));
    }
}
