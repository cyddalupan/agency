<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecruitmentTableAddColumnAgentCommission extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('recruitment_agent', function($table)
		{
		    $table->integer('agent_commission')->after('agent_email');
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
		    $table->dropColumn('agent_commission');
		});
	}

}
