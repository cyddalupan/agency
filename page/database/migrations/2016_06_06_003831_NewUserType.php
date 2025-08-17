<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewUserType extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('user_types')->insert(
		    [
		    	'type_id' => 13,
		    	'type_name' => 'Handler',
		    	'type_status' => 1,
		    	'type_color' => 'info',
		    	'type_createdby' => 1,

		    ]
		);

		DB::table('user_types')->insert(
		    [
		    	'type_id' => 14,
		    	'type_name' => 'Skilled Handler',
		    	'type_status' => 1,
		    	'type_color' => 'success',
		    	'type_createdby' => 1,
		    ]
		);

		DB::table('user_types')->insert(
		    [
		    	'type_id' => 15,
		    	'type_name' => 'Professional Handler',
		    	'type_status' => 1,
		    	'type_color' => 'danger',
		    	'type_createdby' => 1,
		    ]
		);

		DB::table('user_types')->insert(
		    [
		    	'type_id' => 16,
		    	'type_name' => 'Manager',
		    	'type_status' => 1,
		    	'type_color' => 'default',
		    	'type_createdby' => 1,
		    ]
		);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('user_types')->where('type_id', 13)->delete();
		DB::table('user_types')->where('type_id', 14)->delete();
		DB::table('user_types')->where('type_id', 15)->delete();
		DB::table('user_types')->where('type_id', 16)->delete();
	}

}
