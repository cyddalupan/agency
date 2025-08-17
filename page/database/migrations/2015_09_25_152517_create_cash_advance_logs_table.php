<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashAdvanceLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cash_advance_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('recruitment_agent_id');
			$table->integer('applicant_id');
			$table->string('current_status');
			$table->integer('remaining_commission');
			$table->integer('cash_advance');
			$table->integer('current_balance');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cash_advance_logs');
	}

}
