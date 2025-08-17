<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecruitmentAgentAddCashAdvanceAndBalance extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('recruitment_agent', function($table)
		{
		    $table->integer('balance')->after('agent_commission');
		    $table->integer('cash_advance')->after('agent_commission');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('recruitment_agent', function($table)
		{
		    $table->dropColumn(['balance', 'cash_advance']);
		});
	}

}
