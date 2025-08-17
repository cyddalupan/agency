<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Request;
use App\Setting;
use Cache;

class SettingsController extends Controller {

	/**
	 * Get Settings From Db
	 */
	public function get_settings()
	{
		return Cache::remember('settings', "59", function()
		{
			$settingsRaw = Setting::all();
			foreach ($settingsRaw as $settingRaw) {
				$settings[$settingRaw->key] = $settingRaw->value;
			}
			return json_encode($settings);
		});
	}

}
