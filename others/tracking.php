<?php
$ip2= gethostbyaddr($_SERVER['REMOTE_ADDR']);
$timezone = "Asia/Kuala_Lumpur";
if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);
$date1=date('Y-m-d');
?>
<?php
$ip2= gethostbyaddr($_SERVER['REMOTE_ADDR']);
$timezone = "Asia/Kuala_Lumpur";
if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);
$date2=date('Y-m-d');

$ip3= gethostbyaddr($_SERVER['REMOTE_ADDR']);
$timezone1 = "Asia/Manila";
if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone1);
$date3=date('Y-m-d');

?>