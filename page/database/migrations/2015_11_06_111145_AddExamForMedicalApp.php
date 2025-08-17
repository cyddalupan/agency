<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExamForMedicalApp extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applicant_certificate', function($table)
		{
			$table->string('certificate_cgfns_id')->after('certificate_cgfns');
			$table->string('certificate_cgfns_exam')->after('certificate_cgfns');
			$table->string('certificate_nclex_exam')->after('certificate_nclex');
			$table->string('certificate_ielts_exam')->after('certificate_ielts');
			$table->string('certificate_ielts_overall')->after('certificate_ielts');
			$table->string('certificate_vsh')->before('certificate_cgfns');
			
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
		    $table->dropColumn('certificate_cgfns_id');
			$table->dropColumn('certificate_cgfns_exam');
			$table->dropColumn('certificate_nclex_exam');
			$table->dropColumn('certificate_ielts_exam');
			$table->dropColumn('certificate_ielts_overall');
			$table->dropColumn('certificate_vsh');
		});
	}

}
