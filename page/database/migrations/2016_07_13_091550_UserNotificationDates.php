<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserNotificationDates extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('user', function($table)
		{
			$table->dateTime('notif_media')->after('user_lastlogin');
			$table->dateTime('notif_logs')->after('user_lastlogin');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('user', function($table)
		{
		    $table->dropColumn('notif_media');
		    $table->dropColumn('notif_logs');
		});
	}

}
