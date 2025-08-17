<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCertification2 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applicant_certificate', function($table)
		{
			$table->date('certificate_prc_take')->after('certificate_prc_id');
			$table->string('certificate_ksa')->after('certificate_mc');
			$table->string('certificate_haad')->after('certificate_mc');
			$table->string('certificate_qatar')->after('certificate_mc');
			$table->string('certificate_nclex')->after('certificate_mc');
			$table->string('certificate_ielts')->after('certificate_mc');
			$table->string('certificate_cgfns')->after('certificate_mc');
			
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
		    $table->dropColumn('other_source');
		   
		});
	}

}
