<?php //-->

$defaultEnvironment = 'production';

//Development Environment Settings
$dbSettings['development'] = [
	'config' 	=> [
		'host' 			=> '127.0.0.1',
		'user'			=> 'root',
		'pass'			=> '',
		'dbname'		=> 'ics-ipac-v50',
		'environment'	=> 2
	],
	'hosts'		=> [
		'local', 
		'localhost', 
		'127.0.0.1',
		'local.ics-ipac.com', 		
	]
];

//Production Environment Settings
$dbSettings['production'] = [
	'config' 	=> [
		'host'			=> 'localhost',
		'user'			=> 'stepupma_cyd',
		'pass'			=> 'd2R1*-yR46',
		'dbname'		=> 'stepupma_ics',
		'environment'	=> 0,
	],
	'hosts'		=> [
		'stepupmanpower.com', 
		'www.stepupmanpower.com', 
		'admin.stepupmanpower.com', 
		'www.admin.stepupmanpower.com',
		'admin.stepupmanpower.com', 
		'www.admin.stepupmanpower.com',
	]
];

if(!isset($dbSettings[$defaultEnvironment])) {
	echo 'Environment \''.$defaultEnvironment.'\' cannot be recognized. <br>Please check your environment settings.<br>'.__FILE__;
	exit;
}

if(in_array($_SERVER['HTTP_HOST'], $dbSettings[$defaultEnvironment]['hosts'])) {
	return $dbSettings[$defaultEnvironment]['config'];
}

echo 'Host \''.$_SERVER['HTTP_HOST'].'\' does not belong to any enviroment settings.<br>'.__FILE__;
exit;