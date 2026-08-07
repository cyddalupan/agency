<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * LANDAS "Personal Information" — PI:1 Basic Information tab.
 *
 * Persists the applicant-level fields captured on the Basic Information tab
 * (notably the Number of Siblings of the Family Information section) via a
 * dedicated "Save Update" action that returns to the applicant show page,
 * without altering the generic applicants.update flow/redirect.
 */
class PersonalInformationBasicController extends Controller
{
    public function update(Request $request, Applicant $applicant)
    {
        $validator = Validator::make($request->all(), [
            'number_of_siblings' => 'nullable|integer|min:0',
            'mother_name'        => 'nullable|string|max:255',
            'mother_occupation'  => 'nullable|string|max:255',
            'father_name'        => 'nullable|string|max:255',
            'father_occupation'  => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->route('applicants.show', $applicant)
                ->withErrors($validator, 'basic')
                ->withInput();
        }

        $applicant->number_of_siblings  = $request->input('number_of_siblings');
        $applicant->mother_name         = $request->input('mother_name');
        $applicant->mother_occupation   = $request->input('mother_occupation');
        $applicant->father_name         = $request->input('father_name');
        $applicant->father_occupation   = $request->input('father_occupation');
        $applicant->save();

        return redirect()->route('applicants.show', $applicant)
            ->with('success', __('Basic information updated.'));
    }
}
