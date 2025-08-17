<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<!-- Head -->
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<title></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


	<!-- Demo styling -->
	<link href="docs/css/jq.css" rel="stylesheet">

	<!-- jQuery: required (tablesorter works with jQuery 1.2.3+) -->
	<script src="docs/js/jquery-1.2.6.min.js"></script>

	<!-- Pick a theme, load the plugin & initialize plugin -->
	<link href="dist/css/theme.default.min.css" rel="stylesheet">
	<script src="dist/js/jquery.tablesorter.min.js"></script>
	<script src="dist/js/jquery.tablesorter.widgets.min.js"></script>
	<script>
	$(function(){
		$('table').tablesorter({
			widgets        : ['zebra', 'columns'],
			usNumberFormat : false,
			sortReset      : true,
			sortRestart    : true
		});
	});
	</script>



</head>
<?php
include'db.php';
include'tracking.php';
// Turn off error reporting
error_reporting(0);

// Report runtime errors
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Report all errors
error_reporting(E_ALL);

// Same as error_reporting(E_ALL);
ini_set("error_reporting", E_ALL);

// Report all errors except E_NOTICE
error_reporting(E_ALL & ~E_NOTICE);


$fitjan =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view

LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where

log_date between '2021-01-01' AND '2021-01-31'
AND log_status=5
order by log_date desc");
$fitjan1 = mysql_fetch_array($fitjan);


$fitfeb =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view

LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where

log_date between '2021-01-01' AND '2021-01-31'
AND log_status=5
order by log_date desc");
$fitfeb1 = mysql_fetch_array($fitfeb);



$fit =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view

LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where
log_date between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)
AND log_status=5
order by log_date desc");
$fitr = mysql_fetch_array($fit);

$fit1 =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view
LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where
log_date between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day)
AND log_status=5
order by log_date desc");
$fitw = mysql_fetch_array($fit1);


$fit2 =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view
LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where
log_date between date_sub(now(), interval 0 day)  AND date_add(now(), interval 1 day)
AND log_status=5
order by log_date desc");
$fitt = mysql_fetch_array($fit2);
//log_date between date_sub(now(), interval 200 day)  AND date_add(now(), interval 1 day)

$visas =  mysql_query("SELECT 
count(requirement_visa_stamp) as alls,
applicant.applicant_id as adonis, 
applicant_requirement.*,
applicant.* 
FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where 
requirement_visa_stamp between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)
AND applicant_status NOT IN (9,21,22,14,1)");
$visa = mysql_fetch_array($visas);

$visas1 =  mysql_query("SELECT 
count(requirement_visa_stamp) as alls,
applicant.applicant_id as adonis, 
applicant_requirement.*,
applicant.* 
FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where 
requirement_visa_stamp between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day)
AND applicant_status NOT IN (9,21,22,14,1)");
$visaw = mysql_fetch_array($visas1);


$visas2 =  mysql_query("SELECT 
count(requirement_visa_stamp) as alls,
applicant.applicant_id as adonis, 
applicant_requirement.*,
applicant.* 
FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where 
requirement_visa_stamp between date_sub(now(), interval 0 day)  AND date_add(now(), interval 1 day)
AND applicant_status NOT IN (9,21,22,14,1)");
$visat = mysql_fetch_array($visas2);



$sign = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status IN (4,5)
AND requirement_contract between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)");
$signrow = mysql_fetch_array($sign);


$sign1 = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status IN (4,5)
AND requirement_contract between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day)");
$signroww = mysql_fetch_array($sign1);

$sign2 = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status IN (4,5)
AND requirement_contract between date_sub(now(), interval 0 day)  AND date_add(now(), interval 1 day)");
$signrowt = mysql_fetch_array($sign2);



$deployed =
mysql_query("SELECT count(DISTINCT deployed.deployed_applicant) as alls,
applicant.applicant_id as adonis,deployed.*,
applicant.*
FROM deployed
LEFT JOIN applicant
ON deployed.deployed_applicant = applicant.applicant_id
where
deployed_date between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)
AND applicant.applicant_status IN (9)
order by deployed_date desc");
$deployedm = mysql_fetch_array($deployed);


$deployed1 =
mysql_query("SELECT count(DISTINCT deployed.deployed_applicant) as alls,
applicant.applicant_id as adonis,deployed.*,
applicant.*
FROM deployed
LEFT JOIN applicant
ON deployed.deployed_applicant = applicant.applicant_id
where
deployed_date between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day)
AND applicant.applicant_status IN (9)
order by deployed_date desc");
$deployedw = mysql_fetch_array($deployed1);


$deployed22 =
mysql_query("SELECT count(DISTINCT deployed.deployed_applicant) as alls,
applicant.applicant_id as adonis,deployed.*,
applicant.*
FROM deployed
LEFT JOIN applicant
ON deployed.deployed_applicant = applicant.applicant_id
where
deployed_date between date_sub(now(), interval 0 day)  AND date_add(now(), interval 1 day)
AND applicant.applicant_status IN (9)
order by deployed_date desc");
$deployedt = mysql_fetch_array($deployed22);



$backout =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view

LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where
log_date between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)
AND log_status IN (1)
order by log_date desc");
$backoutm = mysql_fetch_array($backout);


$backout1 =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view

LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where
log_date between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day)
AND log_status IN (1)
order by log_date desc");
$backoutw = mysql_fetch_array($backout1);


$backout2 =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view
LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where
log_date between date_sub(now(), interval 0 day)  AND date_add(now(), interval 1 day)
AND log_status IN (1)
order by log_date desc");
$backoutt = mysql_fetch_array($backout2);


$unfit1 =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view
LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where
log_date between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)
AND log_status IN (21)
order by log_date desc");
$unfit1m = mysql_fetch_array($unfit1);

$unfit2 =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view
LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where
log_date between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day)
AND log_status IN (21)
order by log_date desc");
$unfit1w = mysql_fetch_array($unfit2);

$unfit3 =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view
LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where
log_date between date_sub(now(), interval 0 day)  AND date_add(now(), interval 1 day)
AND log_status IN (21)
order by log_date desc");
$unfit1t = mysql_fetch_array($unfit3);




$repat =mysql_query("SELECT 
count(repat_date) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
repat_date between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)
AND applicant_status IN (22)");
$repatm = mysql_fetch_array($repat);

$repat1 =mysql_query("SELECT 
count(repat_date) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
repat_date between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day)
AND applicant_status IN (22)");
$repatw = mysql_fetch_array($repat1);


$repat2 =mysql_query("SELECT 
count(repat_date) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
repat_date between date_sub(now(), interval 0 day)  AND date_add(now(), interval 1 day)
AND applicant_status IN (22)");
$repatt = mysql_fetch_array($repat2);






$active =mysql_query("SELECT 
count(date_added) as alls,
cases.*
FROM cases
where  status NOT IN ('DELETED','CLOSED')
AND date_added between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)");
$activem = mysql_fetch_array($active);


$active1 =mysql_query("SELECT 
count(date_added) as alls,
cases.*
FROM cases
where  status NOT IN ('DELETED','CLOSED')
AND date_added between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day)");
$activew = mysql_fetch_array($active1);

$active2 =mysql_query("SELECT 
count(date_added) as alls,
cases.*
FROM cases
where  status NOT IN ('DELETED','CLOSED')
AND date_added between date_sub(now(), interval 0 day)  AND date_add(now(), interval 1 day)");
$activet = mysql_fetch_array($active2);



$close =mysql_query("SELECT 
count(date_update) as alls,
cases.*
FROM cases
where  status  IN ('CLOSED')
AND date_update between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)");
$closem = mysql_fetch_array($close);

$close1 =mysql_query("SELECT 
count(date_update) as alls,
cases.*
FROM cases
where  status  IN ('CLOSED')
AND date_update between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day)");
$closew = mysql_fetch_array($close1);

$close2 =mysql_query("SELECT 
count(date_update) as alls,
cases.*
FROM cases
where  status  IN ('CLOSED')
AND date_update between date_sub(now(), interval 0 day)  AND date_add(now(), interval 1 day)");
$closet = mysql_fetch_array($close2);




$available =mysql_query("SELECT 
count(repat_date) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status IN (10,11,6,0)");
$available1 = mysql_fetch_array($available);

$lineup =mysql_query("SELECT 
count(repat_date) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status IN (5)");
$lineup1 = mysql_fetch_array($lineup);

$preselected =mysql_query("SELECT 
count(repat_date) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status IN (2,3)");
$preselected1 = mysql_fetch_array($preselected);


$selected =mysql_query("SELECT 
count(repat_date) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status IN (4)");
$selected1 = mysql_fetch_array($selected);


$fordeployed =mysql_query("SELECT 
count(repat_date) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status IN (12,8)");
$fordeployed1 = mysql_fetch_array($fordeployed);



$deployedna =mysql_query("SELECT 
count(repat_date) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status IN (9)");
$deployedna1 = mysql_fetch_array($deployedna);

$finish1 =mysql_query("SELECT 
count(repat_date) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status IN (23)");
$finish = mysql_fetch_array($finish1);


$cancelledna =mysql_query("SELECT 
count(repat_date) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status IN (7,21,1,14)");
$cancelled1 = mysql_fetch_array($cancelledna);


$repatna =mysql_query("SELECT 
count(repat_date) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status IN (22)");
$repatna1 = mysql_fetch_array($repatna);








$finished1 =
mysql_query("SELECT count(DISTINCT deployed.deployed_applicant) as alls,
applicant.applicant_id as adonis,deployed.*,
applicant.*
FROM deployed
LEFT JOIN applicant
ON deployed.deployed_applicant = applicant.applicant_id
where
DATE_ADD(deployed.deployed_date, INTERVAL 757 day)=DATE_SUB(DATE(now()), INTERVAL 0 day)
AND applicant.applicant_status IN (9)
order by deployed_date desc");
$finishedt = mysql_fetch_array($finished1);


/*
$finished2 =
mysql_query("SELECT count(DISTINCT deployed.deployed_applicant) as alls,
applicant.applicant_id as adonis,deployed.*,
applicant.*
FROM deployed
LEFT JOIN applicant
ON deployed.deployed_applicant = applicant.applicant_id
where
deployed.deployed_date between DATE_SUB(now(), INTERVAL 0 day)  AND DATE_ADD(deployed.deployed_date, INTERVAL 763 day)
AND applicant.applicant_status IN (9)
order by deployed_date desc");
$finishedw = mysql_fetch_array($finished2);



$finished3 =
mysql_query("SELECT count(DISTINCT deployed.deployed_applicant) as alls,
applicant.applicant_id as adonis,deployed.*,
applicant.*
FROM deployed
LEFT JOIN applicant
ON deployed.deployed_applicant = applicant.applicant_id
where
DATE_ADD(deployed.deployed_date, INTERVAL 756 day)  == DATE_SUB(DATE(now()), INTERVAL 0 day)

AND applicant.applicant_status IN (9)
order by deployed_date desc");
$finishedm = mysql_fetch_array($finished3);*/

 









?>

<style>
body {background:#F5F5F5;
FONT-FAMILY:arial;
font-size:12px;
}
#customers {
font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
border-collapse: collapse;
width: 100%;
}

#customers td, #customers th {
border: 1px solid #ddd;
padding: 4px;
}

#customers tr:nth-child(even){background-color: white;}

#customers tr:hover {background-color: white;}

#customers th {
padding-top: 10px;
padding-bottom: 10px;
text-align: left;
background-color: #DCDCDC;
color: black;
}

.tablesorter {
font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
border-collapse: collapse;
width: 100%;
}

.tablesorter td, .tablesorter th {
border: 1px solid #ddd;
padding: 4px;
}

.tablesorter tr:nth-child(even){background-color: white;}

.tablesorter tr:hover {background-color: white;}

.tablesorter th {
padding-top: 10px;
padding-bottom: 10px;
text-align: left;
background-color: #DCDCDC;
color: black;
}




/* Style the tab */
.tab {
  overflow: hidden;
  border: 0px solid #ccc;
  background-color: none;
  text-align:center;
}

/* Style the buttons inside the tab */
.tab button {
  background-color: #000080;
  float: left;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 8px 5px;
  transition: 0.3s;
  font-size: 14px;
  margin:4px;
  color:white;
}

/* Change background color of buttons on hover */
.tab button:hover {
  background-color: #DC143C;
}

/* Create an active/current tablink class */
.tab button.active {
  background-color: #DC143C;
}

/* Style the tab content */
.tabcontent {
  padding: 2px 6px;
  border: 0px solid #ccc;
  border-top: none;
}


</style>
<body>

<div style="margin:0 auto;margin-top:10px;text-align:center">
<a href="index.php" style="color:blue;font-size:16px ;text-decoration: none;">My Schedule</a> | 
<a href="request.php" style="color:blue;font-size:16px ;text-decoration: none;">Payment Request</a> | 
<a href="collection-data.php" style="color:blue;font-size:16px ;text-decoration: underline;">Collection</a> | 
<a href="b_account.php"  style="color:red;font-size:16px; text-decoration: underline;">Summary Reports</a>
</div>
<div style="clear:both;height:10px"></div>
<h2 style="font-size:22px;text-align:center">Summary Reports</h2>
<div style="clear:both;height:20px"></div>
<div class="tab">
  <button class="tablinks" onclick="openCity(event, 'London')">Statistic Reports </button>
  <button class="tablinks" onclick="openCity(event, 'Paris')">Complaint Reports</button>
  <button class="tablinks" onclick="openCity(event, 'Tokyo')">Agent  Reports</button>
  <button class="tablinks" onclick="openCity(event, 'London1')">Status Summary Reports </button>

</div>

<div  style="margin-top:-1px;margin:10px" id="London"  class="tabcontent col-md-12 col-sm-12">

<div class="row" style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC;border:1px solid #DCDCDC">

<h1 style="text-align:center;color:#000080">Statistic  Reports</h1>
<h3 style="text-align:center">As of <?php echo date('d-F-Y', strtotime($date1)); ?></p></h3>


<h3 style="color:blue;text-decoration: underline;">Daily  /  Weekly  /  Monthly</h3>
<table id="customers">
<tr>
<th>Status</th>
<th style="width:10%">Today</th>
<th style="width:10%">LAST 7  DAYS</th>
<th style="width:10%">LAST 30  DAYS</th>
<th style="width:10%">January 2021</th>
<th style="width:10%">February 2021</th>
</tr>
<tr>
<td>FIT TO WORK</td>
<td><a href="summary/ftw.php?num=1"><?php echo $fitt['alls']; ?></a></td>
<td><a href="summary/ftw.php?num=2"><?php echo $fitw['alls']; ?></a></td>
<td><a href="summary/ftw.php?num=3"><?php echo $fitr['alls']; ?></a></td>
<td><a href="#"><?php echo $fitjan1['alls']; ?>ll</a></td>
<td><a href="#"><?php echo $fitfeb1['alls']; ?></a></td>
</tr>



<tr>
<td>VISA STAMP</td>
<td><a href="summary/visa.php?num=1"><?php echo $visat['alls']; ?></a></td>
<td><a href="summary/visa.php?num=2"><?php echo $visaw['alls']; ?></a></td>
<td><a href="summary/visa.php?num=3"><?php echo $visa['alls']; ?></a></td>
</tr>

<tr>
<td> CONTRACT ARRIVED</td>
<td><a href="summary/contract.php?num=1"><?php echo $signrowt['alls']; ?></a></td>
<td><a href="summary/contract.php?num=2"><?php echo $signroww['alls']; ?></a></td>
<td><a href="summary/contract.php?num=3"><?php echo $signrow['alls']; ?></a></td>
</tr>

<tr>
<td>DEPLOYED</td>
<td><a href="summary/deployed.php?num=1"><?php echo $deployedt['alls']; ?></a></td>
<td><a href="summary/deployed.php?num=2"><?php echo $deployedw['alls']; ?></a></td>
<td><a href="summary/deployed.php?num=3"><?php echo $deployedm['alls']; ?></a></td>
</tr>

<tr>
<td>BACKOUT</td>
<td><a href="summary/backout.php?num=1"><?php echo $backoutt['alls']; ?></a></td>
<td><a href="summary/backout.php?num=2"><?php echo $backoutw['alls']; ?></a></td>
<td><a href="summary/backout.php?num=3"><?php echo $backoutm['alls']; ?></a></td>
</tr>

<tr>
<td>UNFIT</td>
<td><a href="summary/unfit.php?num=1"><?php echo $unfit1t['alls']; ?></a></td>
<td><a href="summary/unfit.php?num=2"><?php echo $unfit1w['alls']; ?></a></td>
<td><a href="summary/unfit.php?num=3"><?php echo $unfit1m['alls']; ?></a></td>
</tr>

<tr>
<td>REPAT</td>
<td><a href="summary/repat.php?num=1"><?php echo $repatt['alls']; ?></a></td>
<td><a href="summary/repat.php?num=2"><?php echo $repatw['alls']; ?></a></td>
<td><a href="summary/repat.php?num=3"><?php echo $repatm['alls']; ?></a></td>
</tr>

<tr>
<td>FINISHED CONTRACT</td>
<td><a href="summary/finished.php?num=1"><?php echo $finishedt['alls']; ?></a></td>
<td><a href="summary/finished.php?num=2"><?php //$echo finishedw['alls']; ?></a></td>
<td><a href="summary/finished.php?num=3"><?php //echo $finishedm['alls']; ?></a></td>
</tr>


</table>
<div style="clear:both; height:5px;"></div>


</div>
</div>






<div  style="margin-top:-1px;margin:10px;display:none" id="London1"  class="tabcontent col-md-12 col-sm-12">

<div class="row" style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC;border:1px solid #DCDCDC">

<h1 style="text-align:center;color:#000080">Applicants Summary  Reports</h1>
<h3 style="text-align:center">As of <?php echo date('d-F-Y', strtotime($date1)); ?></p></h3>


<table id="customers">
<tr>
<th>Status</th>
<th style="width:10%">Total</th>
</tr>

<tr>
<td>AVAILABLE / PASSPORTING / FOR REVIEW / PENDING MEDICAL</td>
<td><a href="summary/status.php?status=0"><?php echo $available1['alls']; ?></a></td>
</tr>

<tr>
<td>LINE UP</td>
<td><a href="summary/status1.php?num=1"><?php echo $lineup1['alls']; ?></a></td>
</tr>

<tr>
<td>PRE-SELECTED / RESERVED</td>
<td><a href="summary/status1.php?num=2"><?php echo $preselected1['alls']; ?></a></td>
</tr>


<tr>
<td>SELECTED</td>
<td><a href="summary/status1.php?num=3"><?php echo $selected1['alls']; ?></a></td>
</tr>


<tr>
<td>FOR DEPLOYMENT / FOR BOOKING</td>
<td><a href="summary/status1.php?num=4"><?php echo $fordeployed1['alls']; ?></a></td>
</tr>

<tr>
<td>Deployed</td>
<td><a href="summary/status1.php?num=5"><?php echo $deployedna1['alls']; ?></a></td>
</tr>

<tr>
<td>FINISHED CONTRACT</td>
<td><a href="summary/status1.php?num=8"><?php echo $finish['alls']; ?></a></td>
</tr>


<tr>
<td>CANCELLED / UNFIT / BACKOUT / BLACKLIST</td>
<td><a href="summary/status1.php?num=6"><?php echo $cancelled1['alls']; ?></a></td>
</tr>


<tr>
<td>REPAT</td>
<td><a href="summary/status1.php?num=7"><?php echo $repatna1['alls']; ?></a></td>
</tr>
<?php
$totalapp=$available1['alls']+$lineup1['alls']+$preselected1['alls']+$selected1['alls']+$fordeployed1['alls']
+$deployedna1['alls']+$cancelled1['alls']+$repatna1['alls'];
?>

<tr>
<td style="text-align:right;font-weight:bold">Total</td>
<td  style="text-align:right;font-weight:bold"><?php echo $totalapp; ?></td>
</tr>


</table>
</div>
</div>
<div style="clear:both; height:5px;"></div>






<div style="clear:both; height:20px;"></div>
<div style="margin-top:-1px;margin:3px;display: none;"  id="Paris"  class="tabcontent col-md-12 col-sm-12">
<div class="row" style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC">

<h1 style="text-align:center;color:#000080">ACTIVE & CLOSED Complaint Summary Reports</h1>
<h3 style="text-align:center">As of <?php echo date('d-F-Y', strtotime($date1)); ?></p></h3>


<h3 style="color:blue;text-decoration: underline;">Daily / Weekly / Monthly</h3>
<table id="customers">
<tr>
<th>Status</th>
<th style="width:10%">Today</th>
<th style="width:10%">LAST 7  DAYS</th>
<th style="width:10%">LAST 30  DAYS</th>
</tr>
<tr>
<td>Active Complaint</td>
<td><a href="summary/active.php?num=1"><?php echo $activet['alls']; ?></a></td>
<td><a href="summary/active.php?num=2"><?php echo $activew['alls']; ?></a></td>
<td><a href="summary/active.php?num=3"><?php echo $activem['alls']; ?></a></td>
</tr>

<tr>
<td>Close Case/Complaint</td>
<td><a href="summary/close.php?num=1"><?php echo $closet['alls']; ?></a></td>
<td><a href="summary/close.php?num=2"><?php echo $closew['alls']; ?></a></td>
<td><a href="summary/close.php?num=3"><?php echo $closem['alls']; ?></a></td>
</tr>

</table>
<div style="clear:both; height:5px;"></div>
<h3 style="color:blue;text-decoration: underline;">Active Complaint per Category</h3>

<?php
$POEA1 = mysql_query("SELECT count(case_id) as poea,case_id,case_type,status  FROM cases
where status NOT IN ('DELETED','CLOSED') 
AND case_type IN ('POEA')  ");
$poeacount = mysql_fetch_array($POEA1);
$owwa1 = mysql_query("SELECT count(case_id) as owwa,case_id,case_type,status  FROM cases
where status NOT IN ('DELETED','CLOSED') 
AND case_type IN ('OWWA')  ");
$owwacount = mysql_fetch_array($owwa1);
$LABOUR = mysql_query("SELECT count(case_id) as laborcount,case_id,case_type,status  FROM cases
where status NOT IN ('DELETED','CLOSED') 
AND case_type IN ('LABOUR')  ");
$laborcount = mysql_fetch_array($LABOUR);
$ARMM1 = mysql_query("SELECT count(case_id) as ARMM,case_id,case_type,status  FROM cases
where status NOT IN ('DELETED','CLOSED') 
AND  case_type IN ('ARMM')  ");
$ARMM = mysql_fetch_array($ARMM1);

$allnot = mysql_query("SELECT count(case_id) as allnot,case_id,case_type,status  FROM cases
where status NOT IN ('DELETED','CLOSED') 
AND case_type NOT IN ('POEA','OWWA','LABOUR','ARMM')  ");
$allnot1 = mysql_fetch_array($allnot);


?>

<table id="customers">
<tr>
<th>Total Active</th>
<th style="width:10%">Total</th>
</tr>


<tr>
<td>POEA</td>
<td><a href="summary/complaint.php?num=1"><?=$poeacount['poea']?></a></td>
</tr>

<tr>
<td>OWWA</td>
<td><a href="summary/complaint.php?num=2"><?=$owwacount['owwa']?></a></td>
</tr>

<tr>
<td>LABOUR</td>
<td><a href="summary/complaint.php?num=3"><?=$laborcount['laborcount']?></a></td>
</tr>

<tr>
<td>ARMM</td>
<td><a href="summary/complaint.php?num=4"><?=$ARMM['ARMM']?></a></td>
</tr>

<tr>
<td>FB/PRO/AGENT/SMS/WALK-IN/OTHERS</td>
<td><a href="summary/complaint.php?num=5"><?=$allnot1['allnot']?></a></td>
</tr>
<?php  $totalapp1=$poeacount['poea']+ $owwacount['owwa']+$laborcount['laborcount']+$ARMM['ARMM']+$allnot1['allnot'];?>

<tr>
<td style="text-align:right;font-weight:bold">Total</td>
<td  style="text-align:right;font-weight:bold"><?php echo $totalapp1; ?></td>
</tr>
</table>


</div>
</div>



<div style="clear:both; height:5px;"></div>
<div style="margin-top:-1px;margin:3px;display: none;" id="Tokyo"  class="tabcontent col-md-12 col-sm-12">
<div class="row" style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid  #DCDCDC" >

<h1 style="text-align:center;color:#000080">Summary Agent  Reports</h1>
<h3 style="text-align:center">As of <?php echo date('d-F-Y', strtotime($date1)); ?></p></h3>

<?php
$app = mysql_query("SELECT * FROM recruitment_agent
order by agent_first desc");

?>

<table  class="tablesorter" >
	<thead>
<tr>
<th  style="width:15%;font-size:9px">Agent Name</th>
<th style="font-size:10px;">AVAILABLE</th>
<th style="font-size:10px;">ON PROCESS</th>
<th style="font-size:10px;">Total Active</th>
<th style="font-size:10px;width:5px"></th>
<th style="font-size:10px">CANCELLED / UNFIT</th>
<th style="font-size:10px">REPAT</th>
<th style="font-size:10px">DEPLOYED</th>
<th style="font-size:10px">Total Applicants</th>

</tr>
	</thead>
	<tbody>
<?php
while($row=mysql_fetch_array($app))
{


$result1 = mysql_query("SELECT count(applicant_source) as alls FROM  applicant where applicant_source='".$row['agent_id']."'");
$all = mysql_fetch_array($result1);


$onprocess = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$row['agent_id']."' 
AND applicant_status IN (4,2,3,5,8,12,26,27,28,29,30,31)
");
$onprocess1 = mysql_fetch_array($onprocess);

$deployed = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$row['agent_id']."' 
AND applicant_status IN (9)
");
$deployed1 = mysql_fetch_array($deployed);


$repat = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$row['agent_id']."' 
AND applicant_status IN (22)
");
$repat1 = mysql_fetch_array($repat);


$pass = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$row['agent_id']."' 
AND applicant_status IN (15,0,10)
");
$pass1 = mysql_fetch_array($pass);


$not = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$row['agent_id']."' 
AND applicant_status IN (1,14,21,7)
");
$not1 = mysql_fetch_array($not);


$totalactive=$pass1["countme"]+$onprocess1["countme"];


echo'<tr>';
echo'<td  style="background:none"> '.$row["agent_first"].' '.$row["agent_last"].'</td>';
echo'<td style="color:#1E90FF;background:none">'.$pass1["countme"].'</td>';
echo'<td style="color:#1E90FF;background:none">'.$onprocess1["countme"].'</td>';
echo'<td style="color:#1E90FF;background:none"><b>'.$totalactive.'<b></td>';
echo'<td style="color:black;background:none"></td>';
echo'<td style="color:black;background:none">'.$not1["countme"].'</td>';
echo'<td style="color:black;background:none">'.$repat1["countme"].'</td>';
echo'<td style="color:black;background:none">'.$deployed1["countme"].'</td>';
echo'<td style="color:1E90FF;font-weight:bold;background:none">'.$all["alls"].'</td>';

echo'</tr>';
}
?>
	</tbody>

</table>




<div style="clear:both; height:5px;"></div>
</div>
</div>



<script>
function openCity(evt, cityName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(cityName).style.display = "block";
  evt.currentTarget.className += " active";
}
</script>




</body>
<!--  /Body -->
</html>




