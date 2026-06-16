<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use Illuminate\Http\Request;
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
            ],
            'certificates' => [
                'type'           => 'required|string|max:100',
                'certificate_no' => 'nullable|string|max:100',
                'issue_date'     => 'nullable|date',
                'expiry_date'    => 'nullable|date',
                'file_path'      => 'nullable|string|max:255',
                'remarks'        => 'nullable|string',
            ],
            'requirements' => [
                'type'           => 'required|string|max:100',
                'reference_no'   => 'nullable|string|max:100',
                'status'         => 'nullable|string|in:pending,submitted,approved,rejected',
                'submitted_date' => 'nullable|date',
                'approved_date'  => 'nullable|date',
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
                'skill_name'  => 'required|string|max:255',
                'proficiency' => 'nullable|string|in:beginner,intermediate,expert',
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
            'passport'         => ['agency_id', 'applicant_id', 'passport_no', 'issue_date', 'expiry_date', 'place_of_issue'],
            'certificates'     => ['agency_id', 'applicant_id', 'type', 'certificate_no', 'issue_date', 'expiry_date', 'file_path', 'remarks'],
            'requirements'     => ['agency_id', 'applicant_id', 'type', 'reference_no', 'status', 'submitted_date', 'approved_date', 'file_path', 'remarks'],
            'work-experiences' => ['agency_id', 'applicant_id', 'company', 'position', 'from_date', 'to_date', 'responsibilities'],
            'skills'           => ['agency_id', 'applicant_id', 'skill_name', 'proficiency'],
            'references'       => ['agency_id', 'applicant_id', 'name', 'contact', 'relation', 'position'],
            'salary-records'   => ['agency_id', 'applicant_id', 'amount', 'currency', 'type', 'notes'],
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
            'references'       => \App\Models\ApplicantReference::class,
            'salary-records'   => \App\Models\ApplicantSalaryRecord::class,
            default            => abort(404, "Unknown sub-table: {$type}"),
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
        $data['agency_id'] = auth()->user()->agency_id;

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
