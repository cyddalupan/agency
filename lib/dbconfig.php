<?php //-->
//get env
$rawEnv = file_get_contents('page/.env');
$envArr = explode("\n", $rawEnv);
//convert to readable
foreach ($envArr as $envVar) {
	$bievn = explode('=', $envVar);
	if($bievn[0] != '')
		$env[$bievn[0]] = $bievn[1];
}
$GLOBALS['env'] = $env;
//$env is now ready

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
		'host'			=> $env['DB_HOST'],
		'user'			=> $env['DB_USERNAME'],
		'pass'			=> $env['DB_PASSWORD'],
		'dbname'		=> $env['DB_DATABASE'],
		'debug'		=> $env['debug'],
		'environment'	=> 0,
	],
	'hosts'		=> [
		$env['homeHost'],
		'www.'.$env['homeHost'],
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