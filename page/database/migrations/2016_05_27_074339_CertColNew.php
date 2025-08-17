<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CertColNew extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		 Schema::table('applicant_certificate', function($table)
		{
		    $table->integer('certificate_owwa')->after('certificate_pdos');
			$table->string('certificate_owwa_to')->after('certificate_pdos');
			$table->string('certificate_owwa_from')->after('certificate_pdos');
		  
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
		    $table->dropColumn('certificate_owwa');
		    $table->dropColumn('certificate_owwa_to');
		    $table->dropColumn('certificate_owwa_from');
		});
	}

}
