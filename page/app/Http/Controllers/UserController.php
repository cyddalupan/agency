<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Request;

use App\user_system;
use Session;
use App\applicant_files;
use App\applicant_log;

class UserController extends Controller {

	/**
	 * When User Loged in using CI but the angular ang laravel needs to confirm the session also
	 * so we save it here using angulars HTTP
	 */
	public function save_session()
	{
		$user = Request::input("user");
		Session::put("user",$user);
		return "success";
	}

	/**
	 * Deletes Session
	 */
	public function logout(){
		Session::forget("user");
		return "success";
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy()
	{
		$id = Request::input('user_id');

		$user = user_system::find($id);
		$user->user_type = 0;
		$user->save();

		echo 'User '.$id.' Deleted';
	}	

	public function check_login(){
		if(null !== Session::get('user'))
		{
			return json_encode(Session::get('user'));
		}
		else
		{
			return 0;
		}
	}

}
