<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewFieldtraining extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
			Schema::table('applicant_certificate', function($table)
		{
		    $table->integer('certificate_training')->after('certificate_tesda');
		  
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('applicant_certificate', function($table)
		{
		    $table->dropColumn(['certificate_training']);
		});
	}

}
