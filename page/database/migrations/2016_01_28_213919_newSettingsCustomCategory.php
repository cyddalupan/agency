<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewSettingsCustomCategory extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('settings')->insert([
			'id' => 19,
		    'key' => 'customCategory1'
		]);
		DB::table('settings')->insert([
			'id' => 20,
		    'key' => 'customCategory2'
		]);
		DB::table('settings')->insert([
			'id' => 21,
		    'key' => 'customCategory3'
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('settings')->where('key', 'customCategory1')->delete();
		DB::table('settings')->where('key', 'customCategory2')->delete();
		DB::table('settings')->where('key', 'customCategory3')->delete();
	}

}
