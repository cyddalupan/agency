<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Request;
use App\user_system;
use Session;
use App\applicant_files;
use Cache;

class ApplicantFilesController extends Controller {

	/**
	 * Get Count of Media Upadates, that is > notif_media
	 */
	public function get_notification_count_media(){

		return Cache::remember('get_notification_count_media',3, function()
		{
			$user_session = Session::get("user");
			$user = user_system::find($user_session['user_id']);
			if(isset($user['notif_media'])){
				$notifcount = applicant_files::where('file_created', ">",  $user['notif_media'])->count();
				return $notifcount;
			}else{
				return 'logout';
			}
		});
	}

	public function get_media_notifications(){
		return Cache::remember('get_media_notifications',3, function()
		{
			$user_session = Session::get("user");
			$user = user_system::find($user_session['user_id']);

			//get current log datettime before updating
			$notif_media = $user->notif_media;

			//now update
			$user->notif_media = date('Y-m-d H:i:s', time());

			$user->save();

			$media['unread'] = applicant_files::where('file_created','>',$notif_media)
				->take(30)
				->with('applicant')
				->with('user')
				->orderBy('file_created', 'desc')
				->get();

			$media['read'] = applicant_files::where('file_created','<',$notif_media)
				->take(30)
				->with('applicant')
				->with('user')
				->orderBy('file_created', 'desc')
				->get();

			return json_encode($media);
		});
	}

}
