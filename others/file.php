<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<!-- Head -->
<head>
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<title><?=$_GET['filet']?></title>

   		
<?php
include'db.php';

$file = mysql_query("SELECT * FROM applicant_files
where file_applicant = ".$_GET['app']."
AND file_type='".$_GET['filet']."'");
$filerow=mysql_fetch_array($file);
?>

<img src="http://fcec-inc.com/system/<?=$filerow['file_path']?>" STYLE="WIDTH:50%;margin:25px">




