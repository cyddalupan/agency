<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApplicantUploaded extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        DB::table('user')->insert(
            [
                'user_id' => 9999, 
                'user_name' => 'applicant upload',
                'user_password' => md5( sha1( '123'.env('sess_cookie_name') ) ),
                'user_fullname' => 'Applicant Upload',
                'user_email' => 'cydmdalupan@gmail.com',
                'user_type' => 4,
                'user_status' => 1,
            ]
        );
        DB::table('settings')->insert(
            [
                'id' => 23, 
                'key' => 'otherLogo1',
                'value' => '',
            ]
        );
        DB::table('settings')->insert(
            [
                'id' => 24, 
                'key' => 'otherLogo2',
                'value' => '',
            ]
        );
        DB::table('settings')->insert(
            [
                'id' => 25, 
                'key' => 'otherLogo3',
                'value' => '',
            ]
        );
        DB::table('settings')->insert(
            [
                'id' => 26, 
                'key' => 'Insurance',
                'value' => '',
            ]
        );
        DB::table('settings')->insert(
            [
                'id' => 27, 
                'key' => 'Medical',
                'value' => '',
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
		DB::table('user')->where('user_id',9999)->delete();

		DB::table('settings')->where('id',23)->delete();
		DB::table('settings')->where('id',24)->delete();
		DB::table('settings')->where('id',25)->delete();
		DB::table('settings')->where('id',26)->delete();
		DB::table('settings')->where('id',27)->delete();
	}

}
