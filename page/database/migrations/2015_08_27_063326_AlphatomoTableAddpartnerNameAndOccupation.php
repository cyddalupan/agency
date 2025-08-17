<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlphatomoTableAddpartnerNameAndOccupation extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applicants_alphatomo', function($table)
		{
		    $table->string('partner_occupation')->after('relative_mobile');
		    $table->string('partner_husband')->after('relative_mobile');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('applicants_alphatomo', function($table)
		{
		    $table->dropColumn(['partner_occupation', 'partner_husband']);
		});
	}

}
