<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<!-- Head -->
<head>


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
count(DISTINCT multiple_lineups.applicant_id) as alls,
applicant.applicant_id as adonis,applicant.*,multiple_lineups.*
FROM multiple_lineups

LEFT JOIN applicant
ON multiple_lineups.applicant_id = applicant.applicant_id
where

multiple_lineups.created_at between '2021-01-01' AND '2021-01-31'
AND applicant_id=5
order by multiple_lineups.created_atdesc ");
$fitjan1 = mysql_fetch_array($fitjan);


$fitfeb =mysql_query("SELECT 
count(DISTINCT multiple_lineups.applicant_id) as alls,
applicant.applicant_id as adonis,applicant.*,multiple_lineups.*
FROM multiple_lineups

LEFT JOIN applicant
ON multiple_lineups.applicant_id = applicant.applicant_id
where

multiple_lineups.created_at between '2021-02-01' AND '2021-02-31'
AND multiple_lineups.applicant_id=5
order by multiple_lineups.created_at desc");
$fitfeb1 = mysql_fetch_array($fitfeb);











$visajan =  mysql_query("SELECT 
count(requirement_visa_stamp) as alls,
applicant.applicant_id as adonis, 
applicant_requirement.*,
applicant.* 
FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where 

 requirement_visa_stamp between '2021-01-01' AND '2021-01-31'
AND applicant_status  not IN (9)");
$visajan1 = mysql_fetch_array($visajan);



$visafeb =  mysql_query("SELECT 
count(requirement_visa_stamp) as alls,
applicant.applicant_id as adonis, 
applicant_requirement.*,
applicant.* 
FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where 

requirement_visa_stamp between '2021-02-01' AND '2021-02-31'
AND applicant_status  not IN (9)");
$visafeb1 = mysql_fetch_array($visafeb);




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
AND applicant_status  not IN (9)");
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
AND applicant_status  not IN (9)");
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
AND applicant_status  not IN (9)");
$visat = mysql_fetch_array($visas2);




$signjan = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status  not IN (9)
AND requirement_contract between '2021-01-01' AND '2021-01-31'");
$signjan1 = mysql_fetch_array($signjan);

$signfeb99 = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status  not IN (9)
AND requirement_contract between '2021-02-01' AND '2021-02-31'");

$signfeb18 = mysql_fetch_array($signfeb99);




$sign = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status  not IN (9)
AND requirement_contract between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)");
$signrow = mysql_fetch_array($sign);


$sign30= mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status  not IN (9)
AND requirement_contract between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)");
$sign301 = mysql_fetch_array($sign30);


$sign1 = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status  not IN (9)
AND requirement_contract between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day)");
$signroww = mysql_fetch_array($sign1);

$sign2 = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status  not IN (9)
AND requirement_contract between date_sub(now(), interval 0 day)  AND date_add(now(), interval 1 day)");
$signrowt = mysql_fetch_array($sign2);


$deployedjan =
mysql_query("SELECT count(DISTINCT deployed.deployed_applicant) as alls,
applicant.applicant_id as adonis,deployed.*,
applicant.*
FROM deployed
LEFT JOIN applicant
ON deployed.deployed_applicant = applicant.applicant_id
where
deployed_date between '2021-01-01' AND '2021-01-31'
AND applicant.applicant_status IN (9)
order by deployed_date desc");
$deployedjan1 = mysql_fetch_array($deployedjan);




$deployedfeb =
mysql_query("SELECT count(DISTINCT deployed.deployed_applicant) as alls,
applicant.applicant_id as adonis,deployed.*,
applicant.*
FROM deployed
LEFT JOIN applicant
ON deployed.deployed_applicant = applicant.applicant_id
where
deployed_date between '2021-02-01' AND '2021-02-31'
AND applicant.applicant_status IN (9)
order by deployed_date desc");
$deployedfeb1 = mysql_fetch_array($deployedfeb);



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


$backoutjan =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view

LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where
log_date between '2021-01-01' AND '2021-01-31'
AND log_status IN (1)
order by log_date desc");
$backoutjan1 = mysql_fetch_array($backoutjan);



$backoutfeb =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view

LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where
log_date between '2021-02-01' AND '2021-02-31'
AND log_status IN (1)
order by log_date desc");
$backoutfeb1 = mysql_fetch_array($backoutfeb);



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



$unfitjan =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view
LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where
log_date between '2021-01-01' AND '2021-01-31'
AND log_status IN (21)
order by log_date desc");
$unfitjan1 = mysql_fetch_array($unfitjan);



$unfitfeb =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view
LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where
log_date between '2021-02-01' AND '2021-02-31'
AND log_status IN (21)
order by log_date desc");
$unfitfeb1 = mysql_fetch_array($unfitfeb);


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


$repatjan =mysql_query("SELECT 
count(repat_date) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
repat_date between '2021-02-01' AND '2021-02-31'
AND applicant_status IN (22)");
$repatjan1 = mysql_fetch_array($repatjan);



$repatfeb =mysql_query("SELECT 
count(repat_date) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
repat_date between '2021-02-01' AND '2021-02-31'
AND applicant_status IN (22)");
$repatfeb1 = mysql_fetch_array($repatfeb);


$repat444 =mysql_query("SELECT 
count(repat_date) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
repat_date between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)
AND applicant_status IN (22)");
$repatm = mysql_fetch_array($repat444);

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
count(applicant_id) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status IN (0)");
$available1 = mysql_fetch_array($available);



$preselected =mysql_query("SELECT 
count(applicant_id) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status IN (5)");
$preselected1 = mysql_fetch_array($preselected);


$selected =mysql_query("SELECT 
count(applicant_id) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status    NOT IN (9,21,22,1,14,25,5) and applicant_employer!=0 ");
$selected1 = mysql_fetch_array($selected);


$fordeployed =mysql_query("SELECT 
count(applicant_id) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status IN (12,8)");
$fordeployed1 = mysql_fetch_array($fordeployed);



$deployedna =mysql_query("SELECT 
count(applicant_id) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status IN (9)");
$deployedna1 = mysql_fetch_array($deployedna);

$finish1 =mysql_query("SELECT 
count(applicant_id) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status IN (23)");
$finish = mysql_fetch_array($finish1);


$cancelledna =mysql_query("SELECT 
count(applicant_id) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status IN (1,25)");
$cancelled1 = mysql_fetch_array($cancelledna);


$unfitme =mysql_query("SELECT 
count(applicant_id) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status IN (21)");
$unfitme1 = mysql_fetch_array($unfitme);


$atoame =mysql_query("SELECT 
count(applicant_id) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
 applicant_status IN (14)");
$atoame1 = mysql_fetch_array($atoame);


$repatna =mysql_query("SELECT 
count(applicant_id) as alls,
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

 


$oec1 = mysql_query("SELECT 
count(requirement_oec_release_date) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status  not IN (9)
AND requirement_oec_release_date NOT IN ('1970-01-01','0000-00-00')
AND requirement_oec_release_date between date_sub(now(), interval 0 day)  AND date_add(now(), interval 1 day)");
$oec1row = mysql_fetch_array($oec1);



$oec2 = mysql_query("SELECT 
count(requirement_oec_release_date) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status  not IN (9)
AND requirement_oec_release_date NOT IN ('1970-01-01','0000-00-00')
AND requirement_oec_release_date between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day)");
$oec2row = mysql_fetch_array($oec2);



$oec3 = mysql_query("SELECT 
count(requirement_oec_release_date) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status  not IN (9)
AND requirement_oec_release_date NOT IN ('1970-01-01','0000-00-00')
AND requirement_oec_release_date between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)");
$oec3row = mysql_fetch_array($oec3);


$oec4 = mysql_query("SELECT 
count(requirement_oec_release_date) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status  not IN (9)
AND requirement_oec_release_date NOT IN ('1970-01-01','0000-00-00')
AND requirement_oec_release_date between '2021-01-01' AND '2021-01-31'");
$oec4row = mysql_fetch_array($oec4);


$oec5 = mysql_query("SELECT 
count(requirement_oec_release_date) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status  not IN (9)
AND requirement_oec_release_date NOT IN ('1970-01-01','0000-00-00')
AND requirement_oec_release_date between '2021-02-01' AND '2021-02-31'");
$oec5row = mysql_fetch_array($oec5);






$musaned1 = mysql_query("SELECT 
count(requirement_musaned_sign) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status NOT IN (9,21,22,1,14,25)
AND certificate_owwa_to NOT IN ('1970-01-01','0000-00-00','')
AND requirement_musaned_sign between date_sub(now(), interval 0 day)  AND date_add(now(), interval 1 day)");
$musaned1row = mysql_fetch_array($musaned1);



$musaned2 = mysql_query("SELECT 
count(requirement_musaned_sign) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status NOT IN (9,21,22,1,14,25)
AND certificate_owwa_to NOT IN ('1970-01-01','0000-00-00','')
AND requirement_musaned_sign between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day)");
$musaned2row = mysql_fetch_array($musaned2);



$musaned3 = mysql_query("SELECT 
count(requirement_musaned_sign) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status NOT IN (9,21,22,1,14,25)
AND certificate_owwa_to NOT IN ('1970-01-01','0000-00-00','')
AND requirement_musaned_sign between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)");
$musaned3row = mysql_fetch_array($musaned3);


$musaned4 = mysql_query("SELECT 
count(requirement_musaned_sign) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status  not IN (9)
AND requirement_musaned_sign NOT IN ('1970-01-01','0000-00-00')
AND requirement_musaned_sign between '2021-01-01' AND '2021-01-31'");
$musaned4row = mysql_fetch_array($musaned4);


$musaned5 = mysql_query("SELECT 
count(requirement_musaned_sign) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status  not IN (9)
AND requirement_musaned_sign NOT IN ('1970-01-01','0000-00-00')
AND requirement_musaned_sign between '2021-02-01' AND '2021-02-31'");
$musaned5row = mysql_fetch_array($musaned5);











?>

<style>
body {background:#F5F5F5;
FONT-FAMILY:arial;
font-size:14px;
}
h3{
font-size:14px;	
}

#customers {
font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
border-collapse: collapse;
width: 70%;

}

#customers td, #customers th {
border: 1px solid #ddd;
padding: 4px;
font-size:16px;
text-align:center;
}

#customers tr:nth-child(even){background-color: white;}

#customers tr:hover {background-color: white;}

#customers th {
padding-top: 10px;
padding-bottom: 10px;
text-align: left;
background-color: #DCDCDC;
color: black;
font-size:12px;
font-weight:bold;
}

.tablesorter {
font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
border-collapse: collapse;
width: 80%;
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


<div style="clear:both;height:0px"></div>
<div class="tab">
  <button class="tablinks" onclick="openCity(event, 'London')">Statistic Reports </button>
  <button class="tablinks" onclick="openCity(event, 'Paris')">Complaint Reports</button>
  <?php if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){ ?>
  <button class="tablinks" onclick="openCity(event, 'Tokyo')">Agent  Reports</button>
  <?php } ?>
  <button class="tablinks" onclick="openCity(event, 'London1')" style="display:none">Status Summary Reports </button>

</div>

<div  style="margin-top:-1px;margin:10px" id="London"  class="tabcontent col-md-12 col-sm-12">

<div class="row" style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC;border:1px solid #DCDCDC">

<h1 style="text-align:left;color:#000080">Statistic  Reports</h1>
<h3 style="text-align:left">As of <?php echo date('M-d-Y', strtotime($date1)); ?></p></h3>


<h3 style="color:blue;text-decoration: underline;width:90%">Daily  /  Weekly  /  Monthly</h3>
<table id="customers">
<tr>
<th  style="text-align:center;font-size:18px">Status</th>
<th   style="text-align:center;font-size:18px">Yesterday</th>
<th   style="text-align:center;font-size:18px">Today</th>
<th  style="text-align:center;font-size:18px">LAST 7  DAYS</th>
<th  style="text-align:center;font-size:18px">LAST 30  DAYS</th>

</tr>

<?php

$monday=date("l");
if($monday=='Monday'){
$dateme=date_create($date1);
date_sub($dateme,date_interval_create_from_date_string("3 days"));
$dateko=date_format($dateme,"Y-m-d");
}
else{
$dateme=date_create($date1);
date_sub($dateme,date_interval_create_from_date_string("1 days"));
$dateko=date_format($dateme,"Y-m-d");
}

 
$medyesteraday = mysql_query("SELECT 
count(applicant_id) as alls,
 applicant_certificate.*,applicant.* FROM applicant
LEFT JOIN 	applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant 
where   applicant_date_applied=".$dateko." ");
$medyesteraday1 = mysql_fetch_array($medyesteraday); 
 
 
$medicalfit88 = mysql_query("SELECT 
count(applicant_id) as alls,
 applicant_certificate.*,applicant.* FROM applicant
LEFT JOIN 	applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant 
where   applicant_date_applied between date_sub(now(), interval 0 day)  AND date_add(now(), interval 1 day) ");
$medicalfit888 = mysql_fetch_array($medicalfit88);


$medicalfit2222 = mysql_query("SELECT 
count(applicant_id) as alls,
applicant_certificate.*,applicant.* FROM applicant
LEFT JOIN 	applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant
where  applicant_date_applied between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day) ");
$medicalfit22228 = mysql_fetch_array($medicalfit2222);


$medicalfit333 = mysql_query("SELECT 
count(applicant_id) as alls,
applicant_certificate.*,applicant.* FROM applicant
LEFT JOIN 	applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant

where   applicant_date_applied between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day) ");
$medicalfit3338 = mysql_fetch_array($medicalfit333);

?>



<tr> 
<td   style="text-align:left">NEW </td>
<td><a href="summary/new-medical.php?num=4"></a><?php echo $medyesteraday1['alls']; ?></a></td>
<td><a href="summary/new-medical.php?num=1"><?php echo $medicalfit888['alls']; ?></a></td>
<td><a href="summary/new-medical.php?num=2"><?php echo $medicalfit22228['alls']; ?></a></td>
<td><a href="summary/new-medical.php?num=3"><?php echo $medicalfit3338['alls']; ?></a></td>
</tr>

<?php
 
$yesterdayftw = mysql_query("SELECT 
count(applicant_id) as alls,
 applicant_certificate.*,applicant.* FROM applicant
LEFT JOIN 	applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant 
where  certificate_medical_result  IN ('FIT TO WORK')
AND medical_fit=".$dateko."");
$yesterdayftw1 = mysql_fetch_array($yesterdayftw); 
 
 
$fitmet = mysql_query("SELECT 
count(applicant_id) as alls,
 applicant_certificate.*,applicant.* FROM applicant
LEFT JOIN 	applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant 
where  certificate_medical_result  IN ('FIT TO WORK')
AND  medical_fit between date_sub(now(), interval 0 day)  AND date_add(now(), interval 1 day) ");
$fitmet1 = mysql_fetch_array($fitmet);


$fitmet12 = mysql_query("SELECT 
count(applicant_id) as alls,
applicant_certificate.*,applicant.* FROM applicant
LEFT JOIN 	applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant
where  certificate_medical_result  IN ('FIT TO WORK')
AND medical_fit between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day) ");
$fitmet122 = mysql_fetch_array($fitmet12);


$fitmet13 = mysql_query("SELECT 
count(applicant_id) as alls,
applicant_certificate.*,applicant.* FROM applicant
LEFT JOIN 	applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant

where   certificate_medical_result  IN ('FIT TO WORK')
AND medical_fit between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day) ");
$fitmet133 = mysql_fetch_array($fitmet13);

?>


<tr>
<td   style="text-align:left">FTW </td>
<td><a href="summary/ftw-medical.php?num=4"><?php echo $yesterdayftw1['alls']; ?></a></td>
<td><a href="summary/ftw-medical.php?num=1"><?php echo $fitmet1['alls']; ?></a></td>
<td><a href="summary/ftw-medical.php?num=2"><?php echo $fitmet122['alls']; ?></a></td>
<td><a href="summary/ftw-medical.php?num=3"><?php echo $fitmet133['alls']; ?></a></td>
</tr>


<?php



$yesterdaycv =mysql_query("SELECT 
count(DISTINCT multiple_lineups.applicant_id) as alls,
applicant.applicant_id as adonis,applicant.*,multiple_lineups.*
FROM multiple_lineups

LEFT JOIN applicant
ON multiple_lineups.applicant_id = applicant.applicant_id
where multiple_lineups.created_at=".$dateko."
order by multiple_lineups.created_at desc");
$yesterdaycv1 = mysql_fetch_array($yesterdaycv);



$fit =mysql_query("SELECT 
count(DISTINCT multiple_lineups.applicant_id) as alls,
applicant.applicant_id as adonis,applicant.*,multiple_lineups.*
FROM multiple_lineups

LEFT JOIN applicant
ON multiple_lineups.applicant_id = applicant.applicant_id
where
multiple_lineups.created_at between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)
order by multiple_lineups.created_at desc");
$fitr = mysql_fetch_array($fit);



$fit1 =mysql_query("SELECT 
count(DISTINCT multiple_lineups.applicant_id) as alls,
applicant.applicant_id as adonis,applicant.*,multiple_lineups.*
FROM multiple_lineups

LEFT JOIN applicant
ON multiple_lineups.applicant_id = applicant.applicant_id
where
created_at between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day)
order by created_at desc");
$fitw = mysql_fetch_array($fit1);


$fit2 =mysql_query("SELECT 
count(DISTINCT multiple_lineups.applicant_id) as alls,
applicant.applicant_id as adonis,applicant.*,multiple_lineups.*
FROM multiple_lineups
LEFT JOIN applicant
ON multiple_lineups.applicant_id = applicant.applicant_id
where
created_at between date_sub(now(), interval 0 day)  AND date_add(now(), interval 1 day)
order by created_at desc");
$fitt = mysql_fetch_array($fit2);
//log_date between date_sub(now(), interval 200 day)  AND date_add(now(), interval 1 day)


$yesterdaysign = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status  not IN (9)
AND requirement_contract=".$dateko."");
$yesterdaysign1 = mysql_fetch_array($yesterdaysign);


$yesterdayvisa =  mysql_query("SELECT 
count(requirement_visa_stamp) as alls,
applicant.applicant_id as adonis, 
applicant_requirement.*,
applicant.* 
FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where 
requirement_visa_stamp=".$dateko."
AND applicant_status  not IN (9)");
$yesterdayvisa1 = mysql_fetch_array($yesterdayvisa);



$yesterdaydep =
mysql_query("SELECT count(DISTINCT deployed.deployed_applicant) as alls,
applicant.applicant_id as adonis,deployed.*,
applicant.*
FROM deployed
LEFT JOIN applicant
ON deployed.deployed_applicant = applicant.applicant_id
where
 deployed_date=".$dateko."
AND applicant.applicant_status IN (9)
order by deployed_date desc");
$yesterdaydep1 = mysql_fetch_array($yesterdaydep);



$yesterdayoec = mysql_query("SELECT 
count(requirement_oec_release_date) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status  not IN (9)
AND requirement_oec_release_date NOT IN ('1970-01-01','0000-00-00')
AND requirement_oec_release_date=".$dateko."");
$yesterdayoec1 = mysql_fetch_array($yesterdayoec);


$yesterdaybackout =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view
LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where log_date=".$dateko."
AND log_status IN (1)
order by log_date desc");
$yesterdaybackout1 = mysql_fetch_array($yesterdaybackout);


$yesterdayunfit =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view
LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where log_date=".$dateko."
AND log_status IN (21)
order by log_date desc");
$yesterdayunfit1 = mysql_fetch_array($yesterdayunfit);



$yesterdayrepat =mysql_query("SELECT 
count(repat_date) as alls,
applicant.applicant_id as adonis,applicant.*
FROM applicant
where
repat_date=".$dateko."
AND applicant_status IN (22)");
$yesterdayrepat1 = mysql_fetch_array($yesterdayrepat);
?>


<tr>
<td   style="text-align:left">CV Sent</td>
<td><a href="summary/ftw.php?num=4"><?php echo $yesterdaycv1['alls']; ?></a></td>
<td><a href="summary/ftw.php?num=1"><?php echo $fitt['alls']; ?></a></td>
<td><a href="summary/ftw.php?num=2"><?php echo $fitw['alls']; ?></a></td>
<td><a href="summary/ftw.php?num=3"><?php echo $fitr['alls']; ?></a></td>
</tr>


<tr>
<td   style="text-align:left">SELECTED</td>
<td><a href="summary/selected.php?num=4"><?php echo $yesterdaysign1['alls']; ?></a></td>
<td><a href="summary/selected.php?num=1"><?php echo $signrowt['alls']; ?></a></td>
<td><a href="summary/selected.php?num=2"><?php echo $signroww['alls']; ?></a></td>
<td><a href="summary/selected.php?num=3"><?php echo $sign301['alls']; ?></a></td>
</tr>



<tr>
<td   style="text-align:left">VISA STAMP</td>
<td><a href="summary/visa.php?num=4"><?php echo $yesterdayvisa1['alls']; ?></a></td>
<td><a href="summary/visa.php?num=1"><?php echo $visat['alls']; ?></a></td>
<td><a href="summary/visa.php?num=2"><?php echo $visaw['alls']; ?></a></td>
<td><a href="summary/visa.php?num=3"><?php echo $visa['alls']; ?></a></td>
</tr>

<tr>
<td   style="text-align:left"> CONTRACT ARRIVED</td>
<td><a href="summary/contract.php?num=4"><?php echo $yesterdaysign1['alls']; ?></a></td>
<td><a href="summary/contract.php?num=1"><?php echo $signrowt['alls']; ?></a></td>
<td><a href="summary/contract.php?num=2"><?php echo $signroww['alls']; ?></a></td>
<td><a href="summary/contract.php?num=3"><?php echo $sign301['alls']; ?></a></td>

</tr>





<tr Style="display:none">
<td   style="text-align:left"> OWWA </td>
<td><a href="summary/new-medical.php?num=1"></a></td>
<td><a href="summary/contract.php?num=1"><?php echo $musaned1row['alls']; ?></a></td>
<td><a href="summary/contract.php?num=2"><?php echo $musaned2row['alls']; ?></a></td>
<td><a href="summary/contract.php?num=3"><?php echo $musaned3row['alls']; ?></a></td>

</tr>


<tr>
<td   style="text-align:left"> OEC </td>
<td><a href="summary/oec.php?num=4"><?php echo $yesterdayoec1['alls']; ?></a></td>
<td><a href="summary/oec.php?num=1"><?php echo $oec1row['alls']; ?></a></td>
<td><a href="summary/oec.php?num=2"><?php echo $oec2row['alls']; ?></a></td>
<td><a href="summary/oec.php?num=3"><?php echo $oec3row['alls']; ?></a></td>

</tr>






<tr>
<td   style="text-align:left">DEPLOYED</td>
<td><a href="summary/deployed.php?num=4"><?php echo $yesterdaydep1['alls']; ?></a></td>
<td><a href="summary/deployed.php?num=1"><?php echo $deployedt['alls']; ?></a></td>
<td><a href="summary/deployed.php?num=2"><?php echo $deployedw['alls']; ?></a></td>
<td><a href="summary/deployed.php?num=3"><?php echo $deployedm['alls']; ?></a></td>

</tr>




<tr>
<td  style="text-align:left;BACKGROUND:#ADFF2F"  style="color:black">BACKOUT</td>
<td  style="color:white;BACKGROUND:#ADFF2F"><a href="summary/backout.php?num=4" style="color:black"><?php echo $yesterdaybackout1['alls']; ?></a></td>
<td  style="color:white;BACKGROUND:#ADFF2F"><a href="summary/backout.php?num=1" style="color:black"><?php echo $backoutt['alls']; ?></a></td>
<td  style="color:white;BACKGROUND:#ADFF2F"><a href="summary/backout.php?num=2" style="color:black"><?php echo $backoutw['alls']; ?></a></td>
<td  style="color:white;BACKGROUND:#ADFF2F"><a href="summary/backout.php?num=3" style="color:black"><?php echo $backoutm['alls']; ?></a></td>

</tr>

<tr> 
<td  style="text-align:left;BACKGROUND:#1E90FF" style="color:black">UNFIT</td>
<td  style="color:white;BACKGROUND:#1E90FF"><a href="summary/unfit.php?num=4" style="color:black"><?php echo $yesterdayunfit1['alls']; ?></a></td>
<td  style="color:white;BACKGROUND:#1E90FF"><a href="summary/unfit.php?num=1" style="color:black"><?php echo $unfit1t['alls']; ?></a></td>
<td  style="color:white;BACKGROUND:#1E90FF"><a href="summary/unfit.php?num=2" style="color:black"><?php echo $unfit1w['alls']; ?></a></td>
<td  style="color:white;BACKGROUND:#1E90FF"><a href="summary/unfit.php?num=3" style="color:black"><?php echo $unfit1m['alls']; ?></a></td>

</tr>

<tr>
<td  style="text-align:left;BACKGROUND:#DC143C" style="color:black">REPAT</td>
<td  style="color:white;BACKGROUND:#DC143C"><a href="summary/repat.php?num=4" style="color:black"><?php echo $yesterdayrepat1['alls']; ?></a></td>
<td  style="color:white;BACKGROUND:#DC143C"><a href="summary/repat.php?num=1" style="color:black"><?php echo $repatt['alls']; ?></a></td>
<td  style="color:white;BACKGROUND:#DC143C"><a href="summary/repat.php?num=2" style="color:black"><?php echo $repatw['alls']; ?></a></td>
<td  style="color:white;BACKGROUND:#DC143C"><a href="summary/repat.php?num=3" style="color:black"><?php echo $repatm['alls']; ?></a></td>

</tr>

<tr style="display:none">
<td></td>
<td  style="color:white"><a href="summary/finished.php?num=1"><?php //echo $finishedt['alls']; ?></a></td>
<td  style="color:white"><a href="summary/finished.php?num=2"><?php //$echo finishedw['alls']; ?></a></td>
<td  style="color:white"><a href="summary/finished.php?num=3"><?php //echo $finishedm['alls']; ?></a></td>

</tr>
  

<?php
$yesterday=$yesterdayrepat1['alls']+$yesterdayunfit1['alls']+$yesterdaybackout1['alls']+$yesterdaydep1['alls']+$yesterdayoec1['alls']+$yesterdaysign1['alls']+$yesterdaysign1['alls']+$yesterdaycv1['alls']+$yesterdayftw1['alls']+$medyesteraday1['alls'];
$daily=$backoutt['alls']+ $unfit1t['alls']+ $repatt['alls']+$fitt['alls']+$signrowt['alls']+$visat['alls']+$signrowt['alls']+$musaned1row['alls']+$oec1row['alls']+$deployedt['alls'];
$daily7=$backoutw['alls']+ $unfit1w['alls']+ $repatw['alls']+$fitw['alls']+$signroww['alls']+$visaw['alls']+$signroww['alls']+$musaned2row['alls']+$oec2row['alls']+$deployedw['alls'];
$daily30=$backoutm['alls']+ $unfit1m['alls']+ $repatm['alls']+$fitr['alls']+$sign301['alls']+$visaw['alls']+$sign301['alls']+$musaned3row['alls']+$oec3row['alls']+$oec3row['alls'];
?>



<tr>
<td style="color:black;BACKGROUND:#FFEBCD;text-align:center;FONT-SIZE:22PX">TOTAL</td>
<td  style="color:white;BACKGROUND:#FFEBCD"><a href="#" STYLE="FONT-SIZE:28PX"><?php echo $yesterday; ?></a></td>
<td  style="color:white;BACKGROUND:#FFEBCD"><a href="#" STYLE="FONT-SIZE:28PX"><?php echo $daily; ?></a></td>
<td  style="color:white;BACKGROUND:#FFEBCD"><a href="#" STYLE="FONT-SIZE:28PX"><?php echo $daily7; ?></a></td>
<td  style="color:white;BACKGROUND:#FFEBCD"><a href="#" STYLE="FONT-SIZE:28PX"><?php echo $daily30; ?></a></td>

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
<td STYLE="text-align:left">CV/SENT</td>
<td><a href="#"><?php echo $preselected1['alls']; ?></a></td>
</tr>


<tr>
<td  STYLE="text-align:left">ON PROCESS</td>
<td><a href="#"><?php echo $selected1['alls']; ?></a></td>
</tr>


<tr>
<td  STYLE="text-align:left">BOOKING</td>
<td><a href="summary/status1.php?num=4"><?php echo $fordeployed1['alls']; ?></a></td>
</tr>

<tr>
<td  STYLE="text-align:left">Deployed</td>
<td><a href="summary/status1.php?num=5"><?php echo $deployedna1['alls']; ?></a></td>
</tr>




<tr>
<td  STYLE="text-align:left">CANCELLED</td>
<td><a href="#"><?php echo $cancelled1['alls']; ?></a></td>
</tr>


<tr>
<td  STYLE="text-align:left">UNFIT </td>
<td><a href="#"><?php echo $unfitme1['alls']; ?></a></td>
</tr>


<tr>
<td  STYLE="text-align:left">A to A</td>
<td><a href="#"><?php echo $atoame1['alls']; ?></a></td>
</tr>





<tr>
<td  STYLE="text-align:left">REPAT</td>
<td><a href="summary/status1.php?num=7"><?php echo $repatna1['alls']; ?></a></td>
</tr>
<?php
$totalapp=$preselected1['alls']+$selected1['alls']
+$deployedna1['alls']+$cancelled1['alls']+$repatna1['alls'];
?>

<tr>
<td style="text-align:center;font-weight:bold">Total</td>
<td  style="text-align:right;font-weight:bold"><?php echo $totalapp; ?></td>
</tr>


</table>
</div>
</div>
<div style="clear:both; height:5px;"></div>






<div style="clear:both; height:20px;"></div>
<div style="margin-top:-1px;margin:3px;display: none;"  id="Paris"  class="tabcontent col-md-12 col-sm-12">
<div class="row" style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC">

<h1 style="text-align:center;color:#000080">Complaint Reports </h1>
<h3 style="text-align:center">As of <?php echo date('d-F-Y', strtotime($date1)); ?></p></h3>



<div style="clear:both; height:5px;"></div>
<h3 style="color:blue;text-decoration: underline;">Category</h3>

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
<td style="text-align:left">POEA</td>
<td><a href="summary/complaint.php?num=1"><?=$poeacount['poea']?></a></td>
</tr>

<tr>
<td style="text-align:left">OWWA</td>
<td><a href="summary/complaint.php?num=2"><?=$owwacount['owwa']?></a></td>
</tr>

<tr>
<td style="text-align:left">LABOUR</td>
<td><a href="summary/complaint.php?num=3"><?=$laborcount['laborcount']?></a></td>
</tr>

<tr>
<td style="text-align:left">ARMM</td>
<td><a href="summary/complaint.php?num=4"><?=$ARMM['ARMM']?></a></td>
</tr>

<tr>
<td style="text-align:left">FB/PRO/AGENT/SMS/WALK-IN/OTHERS</td>
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

<h1 style="text-align:center;color:#000080">Agent  Reports</h1>
<h3 style="text-align:center">As of <?php echo date('d-F-Y', strtotime($date1)); ?></p></h3>

<?php
$app = mysql_query("SELECT * FROM recruitment_agent
order by agent_first desc");

?>


<style>
.tablesorter table{
width:60%;	
}
.tablesorter td{
font-size:16px;
text-align:center;	
}
</style>



<table  class="tablesorter">
	<table  cellspacing="0" id="example-checkbox" style="border:1px solid black">
<thead>
	<tr>

	<th STYLE="width:2%"></th>

	<th>NAME</th>
	<th>APPLICANTS</th>

	<th style="color:white;background:#1E90FF">ON PROCESS</th>
	<th style="color:white;background:#1E90FF">%</th>
	<th style="color:white;background:#CD5C5C">BACKOUT</th>
	<th style="color:white;background:#CD5C5C">%</th>
	<th style="color:white;background:#800000">UNFIT</th>
	<th style="color:white;background:#800000">%</th>
	<th style="color:white;background:#FF6347">REPAT</th>
	<th style="color:white;background:#FF6347">%</th>
	<th style="color:white;background:#FF0000">A to A</th>
	<th style="color:white;background:#FF0000">%</th>
	<th style="color:white;background:#2E8B57">DEPLOYED</th>
	<th style="color:white;background:#2E8B57">%</th>
<?php if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){  ?>
	
	
	<th >DEBIT</th>
	<th >CREDIT</th>
	<th >BALANCE</th>
<?php } ?>	
	</tr>
	
	</thead>
	<tbody>
<?php	

$app = mysql_query("SELECT * FROM recruitment_agent where agent_type='Agent'
order by agent_first asc");

if(isset($_POST['Search']))
{
$app = mysql_query("SELECT * FROM recruitment_agent where
agent_first LIKE '%".$_POST['agent_name']."%' OR agent_last LIKE '%".$_POST['agent_name']."%'
OR agent_id LIKE '%".$_POST['agent_name']."%'
");
}


while($row=mysql_fetch_array($app))
{

// DEBIT    
$old = mysql_query("SELECT SUM(receivable) as totalamount,  email_address4.* FROM email_address4 
where  status_me=1 AND agent_id=".$row["agent_id"]." ");    
$old1=mysql_fetch_array($old);   

$release = mysql_query("SELECT SUM(amount) as totalamount,  email_address.* FROM email_address where  agent_id=".$row['agent_id']."
AND request_payment=3
AND account='Commission'
order by date_ad desc");
$release1=mysql_fetch_array($release);



$deduction88 = mysql_query("SELECT SUM(deduction_amount) as totalamount,  deduction.* FROM deduction
where agent_id=".$row['agent_id']."
ORDER BY deduction_id desc");
$deduction881=mysql_fetch_array($deduction88); 

$cashadvance = mysql_query("SELECT SUM(amount) as totalamount,  email_address.* FROM email_address
where account  IN ('Cash Advance agent')
AND agent_id=".$row['agent_id']."
order by date_ad,e_id asc");
$cashadvance1=mysql_fetch_array($cashadvance);  


$debitpayable = mysql_query("SELECT SUM(receivable) as totalamount,  email_address4.* FROM email_address4 where  agent_id=".$row['agent_id']."
 AND status_me=0
order by date_transaction desc");
$debitpayable1=mysql_fetch_array($debitpayable); 



$totaldebit=$debitpayable1['totalamount']+$cashadvance1['totalamount']+$release1['totalamount']+$old1['totalamount']+$deduction881['totalamount'];



//CREDIT
$applicantftw = mysql_query("SELECT SUM(agent_ftw+agent_contract+agent_deployed+agent_ppt) as totalamount,  applicant.* FROM applicant
WHERE applicant_source=".$row['agent_id']." AND ( agent_ftw!=0 OR agent_contract!=0 OR agent_deployed!=0 OR agent_ppt!=0)  AND applicant_status NOT IN (25,1,21,14,22)  ");
$applicantftw1=mysql_fetch_array($applicantftw); 

$colec = mysql_query("SELECT UM(amount) as totalamount,  email_address6.* FROM email_address6
where agent_id=".$row['agent_id']."
ORDER BY tr_id desc");
$colec1=mysql_fetch_array($colec); 


$totalcredit=$colec1['totalamount']+$applicantftw1['totalamount'];
    
    
    
    
    
$app1 = mysql_query("SELECT SUM(amount) as USD ,agent_id,currency FROM email_address
WHERE agent_id=".$row["agent_id"]." AND currency='USD'");
$usd=mysql_fetch_array($app1);

$app2 = mysql_query("SELECT SUM(amount) as PHP2 ,agent_id,currency FROM email_address
WHERE agent_id=".$row["agent_id"]." AND currency='PHP'");
$php=mysql_fetch_array($app2);


$unfit = mysql_query("SELECT SUM(receivable) as receive ,agent_id,receivable FROM email_address4
WHERE agent_id='".$row["agent_id"]."'  AND currency='PHP' and type_status='charge'");
$unfitrow=mysql_fetch_array($unfit);

$CON = mysql_query("SELECT SUM(receivable) as receiveusd ,agent_id,receivable FROM email_address4
WHERE agent_id='".$row["agent_id"]."'  AND  currency='USD' and type_status='charge'");
$contract=mysql_fetch_array($CON);

$col = mysql_query("SELECT SUM(amount) as amountusd ,agent_id,amount FROM email_address6
WHERE agent_id='".$row["agent_id"]."'  AND  currency='USD' ");
$colusd=mysql_fetch_array($col);


$colS = mysql_query("SELECT SUM(amount) as amounPHP ,agent_id,amount FROM email_address6
WHERE agent_id='".$row["agent_id"]."'  AND  currency='PHP' ");
$colphp=mysql_fetch_array($colS);

$app11 = mysql_query("SELECT  SUM(receivable) as tot FROM email_address4 where  agent_id=".$row['agent_id']."
AND type_status='Agent'
AND status_paid=0");
$totalcharge=mysql_fetch_array($app11);


$result1 = mysql_query("SELECT count(applicant_source) as alls FROM  applicant where applicant_source='".$row['agent_id']."'");
$all = mysql_fetch_array($result1);


$onprocess = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$row['agent_id']."' 
AND applicant_status    NOT IN (9,21,22,1,14,25,15)");
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
AND applicant_status IN (0,10,5)
");
$pass1 = mysql_fetch_array($pass);


$backout = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$row['agent_id']."' 
AND applicant_status IN (1,25)");
$backout1 = mysql_fetch_array($backout);

$unfits = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$row['agent_id']."' 
AND applicant_status IN (21)");
$unfits1 = mysql_fetch_array($unfits);

$atota = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$row['agent_id']."' 
AND applicant_status IN (14)");
$atota1 = mysql_fetch_array($atota);


$agree = mysql_query("SELECT count(agent_id) as totalamount,  agent_agreement.* FROM agent_agreement
where agent_id=".$row['agent_id']."");
$agree1=mysql_fetch_array($agree);


echo'<tr>';
if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){
echo'<td>
<a href="profile1.php?agent='.$row["agent_id"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Debit/Credit</button></a>

</td>';
}
Else{
echo'<td>
</td>';	
}


echo'<td style="text-align:center"> '.$row["agent_first"].' '.$row["agent_last"].'</td>';


IF($pass1["countme"]==0){$availpercentage="";}
IF($pass1["countme"]!=0){$availpercentage=($pass1["countme"]/$all["alls"])*100;}

IF($onprocess1["countme"]==0){$availselected="";}
IF($onprocess1["countme"]!=0){$availselected=($onprocess1["countme"]/$all["alls"])*100;}

IF($backout1["countme"]==0){$availback="";}
IF($backout1["countme"]!=0){$availback=($backout1["countme"]/$all["alls"])*100;}

IF($unfits1["countme"]==0){$availunf="";}
IF($unfits1["countme"]!=0){$availunf=($unfits1["countme"]/$all["alls"])*100;}

IF($repat1["countme"]==0){$availrepat="";}
IF($repat1["countme"]!=0){$availrepat=($repat1["countme"]/$all["alls"])*100;}

IF($atota1["countme"]==0){$availatoa="";}
IF($atota1["countme"]!=0){$availatoa=($atota1["countme"]/$all["alls"])*100;}

IF($deployed1["countme"]==0){$availdeployed="";}
IF($deployed1["countme"]!=0){$availdeployed=($deployed1["countme"]/$all["alls"])*100;}

echo'<td style="color:black;font-weight:bold;gray:">'.$all["alls"].'</td>';

echo'<td style="color:white;background:#1E90FF">'.$onprocess1["countme"].'</td>';
echo'<td  style="color:white;background:#1E90FF"> '.number_format($availselected, 2).' %</td>';
echo'<td  style="color:white;background:#CD5C5C">'.$backout1["countme"].'</td>';
echo'<td  style="color:white;background:#CD5C5C"> '.number_format($availback, 2).' %</td>';
echo'<td  style="color:white;background:#800000">'.$unfits1["countme"].'</td>';
echo'<td  style="color:white;background:#800000"> '.number_format($availunf, 2).' %</td>';
echo'<td  style="color:white;background:#FF6347"> '.$repat1["countme"].'</td>';
echo'<td  style="color:white;background:#FF6347"> '.number_format($availrepat, 2).' %</td>';
echo'<td  style="color:white;background:#FF0000">'.$atota1["countme"].'</td>';
echo'<td  style="color:white;background:#FF0000"> '.number_format($availatoa, 2).' %</td>';
//mybalanbce
$mybalance=$totaldebit-$totalcredit;


echo'<td  style="color:white;background:#2E8B57"> '.$deployed1["countme"].'</td>';
echo'<td  style="color:white;background:#2E8B57"> '.number_format($availdeployed, 2).' %</td>';
if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){

echo'<td style="color:black">  '.number_format($totaldebit,2).'</td>';
echo'<td style="color:black">  '.number_format($totalcredit,2).'</td>';
echo'<td style="color:black">  '.number_format($mybalance,2).'</td>';
}

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




