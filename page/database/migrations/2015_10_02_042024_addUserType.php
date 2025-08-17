<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserType extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('user_types')->insert(
		    [
		    	'type_id' => 9,
		    	'type_name' => 'TeamLead',
		    	'type_status' => 1,
		    	'type_color' => 'success',
		    	'type_createdby' => 1,
		    	'type_updatedby' =>1
		    ]
		);
		DB::table('user_types')->insert(
		    [
		    	'type_id' => 10,
		    	'type_name' => 'recruitment_specialist',
		    	'type_status' => 1,
		    	'type_color' => 'danger',
		    	'type_createdby' => 1,
		    	'type_updatedby' =>1
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
		DB::table('user_types')->where('type_name', 'TeamLead')->delete();
		DB::table('user_types')->where('type_name', 'recruitment_specialist')->delete();
	}

}
