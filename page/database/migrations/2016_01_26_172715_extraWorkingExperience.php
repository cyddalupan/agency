<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExtraWorkingExperience extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('settings')->insert([
			'id' => 7,
		    'key' => 'extraExperience1'
		]);
		DB::table('settings')->insert([
			'id' => 8,
		    'key' => 'extraExperience2'
		]);
		DB::table('settings')->insert([
			'id' => 9,
		    'key' => 'extraExperience3'
		]);
		DB::table('settings')->insert([
			'id' => 10,
		    'key' => 'extraExperience4'
		]);
		DB::table('settings')->insert([
			'id' => 11,
		    'key' => 'extraExperience5'
		]);
		DB::table('settings')->insert([
			'id' => 12,
		    'key' => 'extraExperience6'
		]);
		DB::table('settings')->insert([
			'id' => 13,
		    'key' => 'extraExperience7'
		]);
		DB::table('settings')->insert([
			'id' => 14,
		    'key' => 'extraExperience8'
		]);
		DB::table('settings')->insert([
			'id' => 15,
		    'key' => 'extraExperience9'
		]);
		DB::table('settings')->insert([
			'id' => 16,
		    'key' => 'extraExperience10'
		]);
		DB::table('settings')->insert([
			'id' => 17,
		    'key' => 'extraExperience11'
		]);
		DB::table('settings')->insert([
			'id' => 18,
		    'key' => 'extraExperience12'
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('settings')->where('key', 'extraExperience1')->delete();
		DB::table('settings')->where('key', 'extraExperience2')->delete();
		DB::table('settings')->where('key', 'extraExperience3')->delete();
		DB::table('settings')->where('key', 'extraExperience4')->delete();
		DB::table('settings')->where('key', 'extraExperience5')->delete();
		DB::table('settings')->where('key', 'extraExperience6')->delete();
		DB::table('settings')->where('key', 'extraExperience7')->delete();
		DB::table('settings')->where('key', 'extraExperience8')->delete();
		DB::table('settings')->where('key', 'extraExperience9')->delete();
		DB::table('settings')->where('key', 'extraExperience10')->delete();
		DB::table('settings')->where('key', 'extraExperience11')->delete();
		DB::table('settings')->where('key', 'extraExperience12')->delete();
	}

}
