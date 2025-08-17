<?php 
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

$pen = mysql_query("SELECT 
count(requirement_visa_expiration) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_id

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.applicant_id

LEFT JOIN recruitment_agent
ON applicant.applicant_source=recruitment_agent.agent_id 


where certificate_medical_result='PENDING' 
AND applicant.status='Available'
");

$pending = mysql_fetch_array($pen);


$fit =
mysql_query("SELECT 
count(applicant_certificate.certificate_medical_result) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_id

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_id
where applicant.status='Available'
AND  applicant_certificate.certificate_medical_result='FIT TO WORK' 
");

$fitr = mysql_fetch_array($fit);



$booking =
mysql_query("SELECT 
count(applicant.status) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_id

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_id

LEFT JOIN employer
ON applicant.applicant_employer=employer.employer_id

where applicant.status='For Deployment'
");

$bookingr = mysql_fetch_array($booking);



$MEDEX = mysql_query("SELECT
count(certificate_medical_expiration) as alls from applicant_certificate
where 
certificate_medical_expiration NOT IN('0000-00-00','1970-01-01')
AND certificate_medical_expiration <= DATE_ADD(DATE(now()), INTERVAL 14 day)
");
$medexpired = mysql_fetch_array($MEDEX);

$sign = mysql_query("SELECT count(applicant_id) as alls  from applicant 
where sub_status='Waiting for Contract Signing'
");
$signrow = mysql_fetch_array($sign);


$owwas = mysql_query("SELECT 
count(applicant_certificate.certificate_owwa_from) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.applicant_id

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.applicant_id
where 
applicant_certificate.certificate_owwa_from NOT IN('0000-00-00','1970-01-01')
AND applicant_certificate.certificate_owwa_from >= DATE_ADD(DATE(now()), INTERVAL 3 day)
AND applicant.status!='Deployed'
AND applicant.sub_status='OWWA Training'
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
applicant_certificate.biometrics NOT IN('0000-00-00','1970-01-01','')
AND applicant_certificate.biometrics <= DATE_ADD(DATE(now()), INTERVAL 3 day)
AND applicant.status!='Deployed'
AND applicant.sub_status='Biometrics Schedule'
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
AND applicant_requirement.oec_expired <= DATE_ADD(DATE(now()), INTERVAL 3 day)
AND applicant.status NOT IN('Deployed','BACKOUT','CANCELLED')
");
$oec = mysql_fetch_array($oecs);




?>


<div class="row">

<div class="clearfix"></div>
<div class="col-md-6 col-sm-6 col-xs-12">
<div class="x_panel" style="height:300px">
<div class="x_title">
<h2>MEDICAL REMINDER</h2>
<div class="clearfix"></div></br>


<div class="clearfix"></div></br>

<p style="color:black">Unfit Medical: 
<a href="reports/pending.php?exp=1" style="color:red;font-size:18px" target="_blank"><?php echo $unfit['alls']; ?></a>
</p><div class="clearfix"></div>


<p style="color:black">Pending Medical: 
<a href="reports/pending.php?exp=2" style="color:red;font-size:18px" target="_blank"><?php echo $pending['alls']; ?></a>
</p><div class="clearfix"></div>

<p style="color:GREEN">FIT TO WORK: 
<a href="reports/pending.php?exp=4" style="color:red;font-size:18px" target="_blank"><?php echo $fitr['alls']; ?></a>
</p><div class="clearfix"></div>


<p style="color:GREEN">For Deployment : 
<a href="reports/for.php?exp=5" style="color:red;font-size:18px" target="_blank"><?php echo $bookingr['alls']; ?></a>
</p><div class="clearfix"></div>


</form>

</div>
</div>
</div>



<div class="col-md-6 col-sm-6 col-xs-12">
<div class="x_panel" style="height:400px">
<div class="x_title">

<div class="clearfix"></div></br>

<p style="color:black">Expired Visa  (30-down Days): <a href="reports/visa.php?exp=1" target="_blank" style="color:red;font-size:18px">
<?php echo $visa['alls'];?></a></p><div class="clearfix"></div></br>


<div class="clearfix"></div>
<p style="color:black">FOR CONTRACT SIGNING: <a href="reports/contract.php" style="color:red;font-size:18px" target="_blank"><?php echo $signrow['alls']; ?> </a>
</a></p><div class="clearfix"></div></br>
<p style="color:black">OWWA SCHEDULE (2 DAYS BEFORE):  <a href="reports/owwa.php?exp=2" style="color:red;font-size:18px" target="_blank"><?php echo $owwa['alls']; ?>  </a>
</a></p><div class="clearfix"></div></br>
<p style="color:black">BIOMETRIC SCHEDULE (2 DAYS BEFORE):  <a href="reports/visa.php?exp=3" style="color:red;font-size:18px" target="_blank"> 
<?php echo $bio['alls']; ?></a>
</a></p><div class="clearfix"></div></br>


<p style="color:black">OEC EXPIRATION:  <a href="reports/visa.php?exp=4" style="color:red;font-size:18px" target="_blank"><?php echo $oec['alls']; ?> </a>
</a></p><div class="clearfix"></div></br>


</div>
</div>
</div>

<div class="clearfix"></div>
</div>
