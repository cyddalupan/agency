<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CertificateAddTESDAremarks extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
			Schema::table('applicant_requirement', function($table)
		{
		    $table->string('requirement_trade_remarks')->after('requirement_trade_test');
		    
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
			Schema::table('applicant_requirement', function($table)
		{
		    $table->dropColumn('requirement_trade_remarks');
		});
	}

}
