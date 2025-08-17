<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashAdvanceTransactionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cash_advance_transactions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('agent_id');
			$table->integer('applicant_id');
			$table->integer('current_commission');
			$table->integer('cash_advance');
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
		Schema::drop('cash_advance_transactions');
	}

}
