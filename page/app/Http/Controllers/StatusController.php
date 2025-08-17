<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Request;
use App\Status;
use Cache;

class StatusController extends Controller {

	/**
	 * Get All Status
	 *
	 * @return Response
	 */
	public function get_statuses()
	{
		return Cache::remember('statuses', "59", function()
		{
			$statusesraw = Status::all();
			foreach ($statusesraw as $statusraw) {
				$statuses[$statusraw->number]['statusColors'] = $statusraw->statusColors;
				$statuses[$statusraw->number]['statusText'] = $statusraw->statusText;
			}
			return json_encode($statuses);
		});
	}

}
