<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('statuses', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('number');
			$table->string('status');
			$table->string('statusText');
			$table->string('statusColors');
		});

		DB::table('statuses')->insert(
		    [
		    	'number' => 0,
		    	'status' => 'Available',
		    	'statusText' => 'Available',
		    	'statusColors' => 'default',
		    ]
		);

		DB::table('statuses')->insert(
		    [
		    	'number' => 1,
		    	'status' => 'Cancelled',
		    	'statusText' => 'Cancelled',
		    	'statusColors' => 'danger',
		    ]
		);

		DB::table('statuses')->insert(
		    [
		    	'number' => 2,
		    	'status' => 'Reserved',
		    	'statusText' => 'Reserved',
		    	'statusColors' => 'primary',
		    ]
		);

		DB::table('statuses')->insert(
		    [
		    	'number' => 3,
		    	'status' => 'Pre-Selected',
		    	'statusText' => 'Pre-Selected',
		    	'statusColors' => 'default',
		    ]
		);

		DB::table('statuses')->insert(
		    [
		    	'number' => 4,
		    	'status' => 'Selected',
		    	'statusText' => 'Selected',
		    	'statusColors' => 'success',
		    ]
		);

		DB::table('statuses')->insert(
		    [
		    	'number' => 5,
		    	'status' => 'Line Up',
		    	'statusText' => 'Line Up',
		    	'statusColors' => 'info',
		    ]
		);

		DB::table('statuses')->insert(
		    [
		    	'number' => 6,
		    	'status' => 'Qualified',
		    	'statusText' => 'Qualified',
		    	'statusColors' => 'primary',
		    ]
		);

		DB::table('statuses')->insert(
		    [
		    	'number' => 7,
		    	'status' => 'Not Qualified',
		    	'statusText' => 'Not Qualified',
		    	'statusColors' => 'danger',
		    ]
		);

		DB::table('statuses')->insert(
		    [
		    	'number' => 8,
		    	'status' => 'For Deployment',
		    	'statusText' => 'For Deployment',
		    	'statusColors' => 'warning',
		    ]
		);

		DB::table('statuses')->insert(
		    [
		    	'number' => 9,
		    	'status' => 'Deployed',
		    	'statusText' => 'Deployed',
		    	'statusColors' => 'success',
		    ]
		);

		DB::table('statuses')->insert(
		    [
		    	'number' => 10,
		    	'status' => 'For Review',
		    	'statusText' => 'For Review',
		    	'statusColors' => 'info',
		    ]
		);

		DB::table('statuses')->insert(
		    [
		    	'number' => 11,
		    	'status' => 'For Interview',
		    	'statusText' => 'For Interview',
		    	'statusColors' => 'primary',
		    ]
		);

		DB::table('statuses')->insert(
		    [
		    	'number' => 12,
		    	'status' => 'For Booking',
		    	'statusText' => 'For Booking',
		    	'statusColors' => 'danger',
		    ]
		);

		DB::table('statuses')->insert(
		    [
		    	'number' => 13,
		    	'status' => 'Active File',
		    	'statusText' => 'Active File',
		    	'statusColors' => 'info',
		    ]
		);

		DB::table('statuses')->insert(
		    [
		    	'number' => 14,
		    	'status' => 'Blocklist',
		    	'statusText' => 'Blocklist',
		    	'statusColors' => 'default',
		    ]
		);

		DB::table('statuses')->insert(
		    [
		    	'number' => 15,
		    	'status' => 'Passporting',
		    	'statusText' => 'Passporting',
		    	'statusColors' => 'danger',
		    ]
		);

		DB::table('statuses')->insert(
		    [
		    	'number' => 20,
		    	'status' => 'Failed Interview',
		    	'statusText' => 'Failed Interview',
		    	'statusColors' => 'danger',
		    ]
		);

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('statuses');
	}

}
