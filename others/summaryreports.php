<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<!-- Head -->
<head>
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<title></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

</head>
<style>
body {background:#F5F5F5;
FONT-FAMILY:arial;
}

.box h3{
text-align:center;
position:relative;
top:5px;
FONT-SIZE:16PX;
}

.box p{
text-align:center;
position:relative;
top:-15px;
FONT-SIZE:30PX;
COLOR:gray;
}

h4{
color:white;
FONT-SIZE:14px;
letter-spacing:0.0625em;
background:blue;
padding:3px;
width:200px;
text-align:center;
position:relative;
}

.box {
width:28%;
height:120px;
background:#FFF;
margin:2px auto;
float:left;
}
/*==================================================
* Effect 1
* ===============================================*/
.effect1{
-webkit-box-shadow: 0 10px 6px -6px #777;
-moz-box-shadow: 0 10px 6px -6px #777;
box-shadow: 0 10px 6px -6px #777;
margin:10px;
}
</style>
<body>

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
$visas =  mysql_query("SELECT 
count(requirement_visa_expiration) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_id

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.applicant_id

LEFT JOIN recruitment_agent
ON applicant.applicant_source=recruitment_agent.agent_id 

where 
applicant_requirement.requirement_visa_expiration!='0000-00-00'
AND applicant.status NOT IN ('Deployed','BACKOUT','UNFIT')
AND requirement_visa_expiration <= DATE_ADD(DATE(now()), INTERVAL 30 day)
AND applicant.status NOT IN('Deployed','BACKOUT','CANCELLED')
");

$visa = mysql_fetch_array($visas);


$unf = mysql_query("SELECT count(certificate_medical_result) as alls FROM applicant_certificate 
where certificate_medical_result='UNFIT' 
");
$unfit = mysql_fetch_array($unf);

$pen =mysql_query("SELECT 
count(applicant_certificate.certificate_medical_result) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant


LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
LEFT JOIN employer
ON applicant.applicant_employer=employer.employer_id


where applicant.applicant_status IN (0,5)
AND  applicant_certificate.certificate_medical_result='PENDING

");

$pending = mysql_fetch_array($pen);


$fit =
mysql_query("SELECT 
count(applicant_certificate.certificate_medical_result) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant

LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
LEFT JOIN employer
ON applicant.applicant_employer=employer.employer_id

where applicant.applicant_status IN (0,5)
AND  applicant_certificate.certificate_medical_result='FIT TO WORK'  ");

$fitr = mysql_fetch_array($fit);



$booking =
mysql_query("SELECT 
count(applicant_certificate.certificate_medical_result) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant

LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
LEFT JOIN employer
ON applicant.applicant_employer=employer.employer_id

where applicant.applicant_status IN (12,8)
");

$bookingr = mysql_fetch_array($booking);


$deployed =
mysql_query("SELECT 
count(applicant_certificate.certificate_medical_result) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant

LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
LEFT JOIN employer
ON applicant.applicant_employer=employer.employer_id

where applicant.applicant_status IN (9)
");

$deployed1 = mysql_fetch_array($deployed);



$passporting1 =mysql_query("SELECT 
count(applicant.applicant_id) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.*,applicant_passport.* FROM applicant

LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
LEFT JOIN employer
ON applicant.applicant_employer=employer.employer_id

LEFT JOIN applicant_passport ON 
applicant.applicant_id = applicant_passport.passport_applicant
where applicant.applicant_status IN (15)");

$pass12 = mysql_fetch_array($passporting1);




$MEDEX = mysql_query("SELECT
count(certificate_medical_expiration) as alls from applicant_certificate
where 
certificate_medical_expiration NOT IN('0000-00-00','1970-01-01')
AND certificate_medical_expiration <= DATE_ADD(DATE(now()), INTERVAL 14 day)
");
$medexpired = mysql_fetch_array($MEDEX);

$sign = mysql_query("SELECT 
count(applicant_certificate.certificate_medical_result) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
LEFT JOIN employer
ON applicant.applicant_employer=employer.employer_id

where applicant.applicant_status=4
AND  applicant_requirement.requirement_contract NOT IN('0000-00-00','')
AND  applicant_requirement.requirement_contract_sign IN('0000-00-00','')");
$signrow = mysql_fetch_array($sign);

$signcontract = mysql_query("SELECT 
count(applicant_certificate.certificate_medical_result) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
LEFT JOIN employer
ON applicant.applicant_employer=employer.employer_id

where applicant.applicant_status=4
AND  applicant_requirement.requirement_contract NOT IN('0000-00-00','')
AND  applicant_requirement.requirement_contract_sign NOT IN('0000-00-00','')");
$signcontract1 = mysql_fetch_array($signcontract);


$owwas = mysql_query("SELECT 
count(applicant_certificate.certificate_owwa_from) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.applicant_id

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.applicant_id
where 
applicant_certificate.certificate_owwa_from NOT IN('0000-00-00','1970-01-01')
AND applicant_certificate.certificate_owwa_from >= DATE_ADD(DATE(now()), INTERVAL 15 day)
where applicant.applicant_status NOT IN (9)
");
$owwa = mysql_fetch_array($owwas);



$bios = mysql_query("SELECT 
count(applicant_certificate.biometrics) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.applicant_id

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.applicant_id
where 
applicant_requirement.vfs NOT IN('0000-00-00','1970-01-01','')
AND applicant_requirement.vfs <= DATE_ADD(DATE(now()), INTERVAL 15 day)
AND  applicant.applicant_status NOT IN (9)
");
$bio = mysql_fetch_array($bios);



$oecs =  mysql_query("SELECT 
count(applicant_requirement.oec_expired) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.applicant_id

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_id
where 
applicant_requirement.oec_expired NOT IN('0000-00-00','1970-01-01')
AND applicant_requirement.oec_expired <= DATE_ADD(DATE(now()), INTERVAL 10 day)
AND applicant.applicant_status NOT IN (9)
");
$oec = mysql_fetch_array($oecs);

$exp_date = $update['dateadded'];
$effectiveDate = strtotime("+3 months", strtotime($exp_date));
$timestamp = strtotime($exp_date);
//echo "<b><U>". date("d-F-Y",$effectiveDate); 
?>


<div class="col-md-12 col-sm-12" style="margin-top:-1px;margin-LEFT:50px;">
<div class="row" style="floar:left;">

<h2 style="text-align:center">Summary Reports</h2>
<p>As of <?php echo date('d-F-Y', strtotime($date1)); ?>

<h4>New Applicant</h4>
<div class="col-md-3 col-sm-3 box effect1">
			<h3>Passporting</h3>
			<p><?php echo $pass12['alls']; ?></p>
			</div>
			
		    <div class="col-md-3 col-sm-3 box effect1">
			<h3>For Release Passport</h3>
			<p><?php echo $pass12['alls']; ?></p>
			</div>
			
			<div class="col-md-3 col-sm-3 box effect1">
			<h3>Fit to Work</h3>
			<p><?php echo $fitr['alls']; ?></p>
			</div>
			
		
			<div class="col-md-3 col-sm-3 box effect1">
			<h3>Pending Medical</h3>
			<p><?php echo $pending['alls']; ?></p>
			</div>
			
			<div class="col-md-3 col-sm-3 box effect1">
			<h3>Unfit Medical</h3>
			<p><?php echo $unfit['alls']; ?></p>
			</div>
			
		
			
			
	<div style="clear:both; height:1px;"></div>
<h4>On Process Applicant</h4>

			<div class="col-md-3 col-sm-3 box effect1">
			<h3>For Contract Signing</h3>
			<p><?php echo $signrow['alls']; ?></p>
			</div>
			
			<div class="col-md-3 col-sm-3 box effect1">
			<h3>Contract Signed</h3>
			<p><?php echo $signcontract1['alls']; ?></p>
			</div>
			
			<div class="col-md-3 col-sm-3 box effect1">
			<h3>Visa Stamping</h3>
			<p></p>
			</div>
			
			
			<div class="col-md-3 col-sm-3 box effect1">
			<h3>OWWA Schedule</h3>
			<p><?php echo $owwa['alls']; ?></p>
			</div>
			
			<div class="col-md-3 col-sm-3 box effect1">
			<h3>VFS Schedule</h3>
			<p><?php echo $bio['alls']; ?></p>
			</div>
			
				<div class="col-md-3 col-sm-3 box effect1">
			<h3>OEC Expiration</h3>
			<p><?php echo $oec['alls']; ?></p>
			</div>
	<div style="clear:both; height:1px;"></div>
			

<h4>Deployment Applicant</h4>			
			<div class="col-md-3 col-sm-3 box effect1">
			<h3>For Deployment</h3>
			<p><?php echo $bookingr['alls']; ?></p>
			</div>
			
			<div class="col-md-3 col-sm-3 box effect1">
			<h3>Deployed</h3>
			<p><?php echo $deployed1['alls']; ?></p>
			</div>
			
			
  	<div style="clear:both; height:1px;"></div>
	
	
	
	
	
	
	
	
</div>
</div>
</body>
<!--  /Body -->
</html>

