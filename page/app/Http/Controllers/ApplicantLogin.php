<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Request;
use Session;
use Redirect;
use App\Http\Requests\ApplicantLoginRequest;
use App\applicant;
use App\position;

class ApplicantLogin extends Controller {

	/**
	 * Edit Applicant Profile
	 *
	 * @return Response
	 */
	public function index()
	{
		$applicant = applicant::find(1);

		$positions = position::all();

		return view('applicant.profile')
					->withApplicant($applicant)
					->withPositions($positions);
	}

	/**
	 * Edit Applicant Profile
	 *
	 * @return Response
	 */
	public function login()
	{
		return view('applicant.login');
	}

	/**
	 * Edit Applicant Profile
	 *
	 * @return Response
	 */
	public function submit(ApplicantLoginRequest $request)
	{
		$input = Request::all();

		$applicant = applicant::where('applicant_email',$input['email'])->firstOrFail();

		//if password is empty
		if($applicant->password == ''){
			
			/**
			 * if Applicant has no password
			 * Check for default passwork Welcome!1
			 */
			if($input['password'] == 'Welcome!1'){

				/**
				 * if applicant_id session is present
				 * it means the user is log
				 */
				Session::put('applicant_id', $applicant->applicant_id);
				return redirect("applicant/profile");
			}else{
				return Redirect::back()->withErrors('Incorrect Password');	
			}
		}

		//Unfinished****
	}

}
