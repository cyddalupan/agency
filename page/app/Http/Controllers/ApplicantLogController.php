<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Request;
use App\user_system;
use Session;
use App\applicant_log;
use Cache;

class ApplicantLogController extends Controller {

	/**
	 * Get Count of Media Upadates, that is > notif_logs
	 */
	public function get_notification_count_status(){
		return Cache::remember('get_notification_count_status',1, function()
		{
			$user_session = Session::get("user");
			$user = user_system::find($user_session['user_id']);
			$notifcount = applicant_log::where('log_created', ">",  $user['notif_logs'])->count();
			return $notifcount;
		});
	}

	public function get_status_notifications(){

		return Cache::remember('get_status_notifications',1, function()
		{
			$user_session = Session::get("user");
			$user = user_system::find($user_session['user_id']);

			//get current log datettime before updating
			$notif_logs = $user->notif_logs;

			//now update
			$user->notif_logs = date('Y-m-d H:i:s', time());

			$user->save();

			$logs['unread'] = applicant_log::where('log_created','>',$notif_logs)
				->take(30)
				->with('applicant')
				->with('user_system')
				->with('Status')
				->with('employer')
				->orderBy('log_created', 'desc')
				->get();

			$logs['read'] = applicant_log::where('log_created','<',$notif_logs)
				->take(30)
				->with('applicant')
				->with('user_system')
				->with('Status')
				->with('employer')
				->orderBy('log_created', 'desc')
				->get();

			return json_encode($logs);
		});
	}

}
