<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
Route::get('migrate', function()
{
	echo 'migrating<br>';
	Artisan::call('migrate');
	echo 'Migrated!!';
});

//APPLICANTS
Route::post('delete_file/', 'ApplicantsController@destroy');
Route::post('get_files/', 'ApplicantsController@applicant_files');
Route::any('applicants/all-applicants', 'ApplicantsController@all_applicants');
Route::get('applicants/all-employer', 'ApplicantsController@all_employer');
Route::get('applicants/all-country', 'ApplicantsController@all_country');
Route::get('applicants/all-position', 'ApplicantsController@all_position');
Route::get('applicants/quick_search/{keyword}', 'ApplicantsController@quick_search');
Route::post('get_notification_count_encodebranch', 'ApplicantsController@get_notification_count_encodebranch');
Route::post('applicants/send_multiple_lineup', 'ApplicantsController@send_multiple_lineup');

//STATUSES
Route::post('get_statuses/', 'StatusController@get_statuses');

//APPLICANT REPORTS
Route::any('reports/selected_count_positions/{employer_id}', 'ReportsController@selected_count_positions');
Route::any('reports/deployed_with_position/{employer_id}', 'ReportsController@deployed_with_position');
Route::any('reports/lineup_with_position/{employer_id}', 'ReportsController@lineup_with_position');
Route::any('reports/summary-applied-online/{agent_id}', 'ReportsController@summary_applied_online');

//USER PAGE
Route::post('delete_user/', 'UserController@destroy');
Route::post('save-session/', 'UserController@save_session');
Route::post('logout/', 'UserController@logout');
Route::get('check_login/', 'UserController@check_login');

//SETTINGS
Route::post('get_settings/', 'SettingsController@get_settings');

//CYD CONTROLLER
Route::get('cyd', 'CydController@index');
Route::get('cyd/login', 'CydController@login');
Route::post('cyd/submit', 'CydController@submit');
Route::any('cyd/admin', 'CydController@admin');

//APPLICANT LOGS
Route::post('get_notification_count_status/', 'ApplicantLogController@get_notification_count_status');
Route::post('get_status_notifications/', 'ApplicantLogController@get_status_notifications');

//APPLICANT FILES 
Route::post('get_notification_count_media/', 'ApplicantFilesController@get_notification_count_media');
Route::post('get_media_notifications/', 'ApplicantFilesController@get_media_notifications');

########################################################################
########################################################################
########################################################################
########################################################################
//Not Admin PAGE

//employer page
Route::post('multiple_lineup/', 'ApplicantsController@multiple_lineup');
Route::post('get_currency/', 'ApplicantsController@get_currency');

//case management
Route::post('case/hit-applicants', 'CaseManagementApi@hitApplicants');
Route::post('case/get-hit-count', 'CaseManagementApi@getHitCount');
Route::any('case/check-hearing-date', 'CaseManagementApi@checkHearingDate');

//applicant profile
Route::get('applicant/profile', ['middleware' => 'isApplicantLoggin','uses' => 'ApplicantLogin@index']);
Route::any('applicant/login', 'ApplicantLogin@login');
Route::post('applicant/login-submit', 'ApplicantLogin@submit');



