<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBranchId2UserAndSeedUserType extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('user', function($table)
		{
		    $table->integer('branch_id')->after('user_type');
		});

		DB::table('user_types')->insert([
			'type_id' => 11,
		    'type_name' => 'trainingAdmin',
		    'type_status' => '1',
		    'type_color' => 'default',
		    'type_createdby' => 1,
		    'type_updatedby' => 1
		]);

		DB::table('user_types')->insert([
			'type_id' => 12,
		    'type_name' => 'trainingSpecialist',
		    'type_status' => '1',
		    'type_color' => 'primary',
		    'type_createdby' => 1,
		    'type_updatedby' => 1
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('user', function($table)
		{
		    $table->dropColumn('branch_id');
		});

		DB::table('user_types')->where('type_id', 11)->delete();

		DB::table('user_types')->where('type_id', 12)->delete();
	}

}
