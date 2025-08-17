<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ApplicantRequirementcOLUMN extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		 Schema::table('applicant_certificate', function($table)
		{
		    $table->string('certificate_pdos_date')->after('certificate_pdos');
			$table->string('certificate_tesda_date')->after('certificate_tesda');
			$table->string('certificate_mmr')->after('certificate_tesda');
		  
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
		    $table->dropColumn('certificate_pdos_date');
		    $table->dropColumn('certificate_tesda_date');
		    $table->dropColumn('certificate_mmr');
		});
	}

}
