<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use Illuminate\Http\Request;

/**
 * LANDAS "Personal Information" — PI:2 Requirements tab.
 *
 * Persists the checkbox flags captured on the Requirements tab
 * (E-REG, PEOS, Info sheet, Birth Certificate, Marriage Certificate) via a
 * dedicated "Save Requirements" action that returns to the applicant show
 * page. Unchecked checkboxes are absent from the request, so every flag is
 * defaulted to false first (checkbox-on means true).
 */
class PersonalInformationRequirementsController extends Controller
{
    public function update(Request $request, Applicant $applicant)
    {
        $flags = ['e_reg', 'peos', 'info_sheet', 'birth_certificate', 'marriage_certificate'];

        // Checked checkboxes send '1'; unchecked ones are absent. Default all
        // flags to false, then apply the checked ones.
        $values = [];
        foreach ($flags as $flag) {
            $values[$flag] = $request->boolean($flag);
        }
        $applicant->update($values);

        return redirect()->route('applicants.show', $applicant)
            ->with('success', __('Requirements updated.'));
    }
}
