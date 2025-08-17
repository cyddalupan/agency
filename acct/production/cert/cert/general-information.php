<?php 
include '../dbconfig.php';

$apps =mysql_query("SELECT applicant.*,	applicant_certificate.*
FROM 	applicant_certificate 
LEFT JOIN applicant
ON applicant_certificate.certificate_id=applicant.applicant_id

where applicant.applicant_id=".$_GET['pid']."
");
$info=mysql_fetch_array($apps);


$view =mysql_query("SELECT *
FROM 	applicant_view 
where applicant_id=".$_GET['pid']."
");
$rowview=mysql_fetch_array($view);

$pass =mysql_query("SELECT *
FROM 	applicant_passport 
where passport_id=".$_GET['pid']."
");
$pass1=mysql_fetch_array($pass);


$req =mysql_query("SELECT *
FROM 	applicant_requirement 
where requirement_applicant=".$_GET['pid']."
");
$req1=mysql_fetch_array($req);

$settings =mysql_query("SELECT *
FROM 	settings 
where id=2
");
$settingsrow=mysql_fetch_array($settings);

?>

<style>
table{ 
margin-left:60px;
}
table td { 
border:0px solid #000000;padding:0; 
text-transform:uppercase; 
font-size:12px; 
padding:2px;
border-bottom:0;
}

#subtable td{ border-bottom:0; border-top:0; border-left:0}
#blue{
color:blue;
text-align:center;
letter-spacing:1px;
text-align:left;
}
#photo{
position:absolute;
width:180px;
height:160px;
border:2px solid black;
margin-top:40px;
margin-left:700px;
}
</style>


<div style="width:900px; margin:0 auto; font-family:Arial, Helvetica, sans-serif;border:1px solid black;padding:20px">
	<h2 style="text-align:center"><?=$settingsrow['value']?> </h2>
	<div style="clear:both;height:2px"></div>
	<img src="../../files/applicant/<?=$info['applicant_photo']?>" id="photo">
	<table cellpadding="0" cellspacing="0" width="39%">
	
	<tr>
		<td colspan="2"><h3 id="blue">Applicant Information</h3></td>
		
		</tr>

	</table>		
		
	<table style="margin-bottom:20px">
		
		<td style="width:150px;font-size:14px" >Last Name:<b id="bold"></br><?=$info['applicant_last']?> </b>
		<td style="width:150px;font-size:14px">First Name:<b id="bold"></br><?=$info['applicant_first']?> </b></td>
		<td style="width:150px;font-size:14px">Middle Name:<b id="bold"></br><?=$info['applicant_middle']?> </b></td>
	</tr>

	
	</table>
	
	
	<table cellpadding="0" cellspacing="0" width="55%">
	
	<tr>
		<td>Code:</td>
		<td><b id="bold"><?=$info['applicantNumber']?> </b></td>
	</tr>
	</tr>
		<tr>
		<td>Agent:</td>
		<td><b id="bold"><?=$rowview['agent_first']?> <?=$rowview['agent_last']?> </b></td>
	</tr>

	<tr>
	<tr>
		<td>Position:</td>
		<td><b id="bold"><?=$rowview['position_name']?> </b></td>
	</tr>
	
	
	</table>
	
	

	
	
	<table>

	<tr>
		<td>CONTACT NO:</td>
		<td><b id="bold"><?=$info['applicant_contacts']?> </b></td>
	</tr>
	
	<tr>
		<td>AGE:</td>
		<td><b id="bold"><?=$info['applicant_age']?> </b></td>
	</tr>
	
	<tr>
		<td>DATE OF BIRTH:</td>
		<td><b id="bold"><?php echo date('F j, Y', strtotime($info['applicant_birthdate'])); ?> </b></td>
	</tr>
		<tr>
		<td>RELIGION:</td>
		<td><b id="bold"><?=$info['applicant_religion']?> </b></td>
	</tr>
	
	
	</tr>
		<tr>
		<td>GENDER:</td>
		<td><b id="bold"><?=$info['applicant_gender']?> </b></td>
	</tr>
	
	<tr>
		<td>height:</td>
		<td><b id="bold"><?=$info['applicant_height']?> </b></td>
	</tr>
	
		<tr>
		<td>weight:</td>
		<td><b id="bold"><?=$info['applicant_weight']?> </b></td>
	</tr>
	
	
	</tr>
		<tr>
		<td>address:</td>
		<td><b id="bold"><?=$info['applicant_address']?> </b></td>
	</tr>
	
	
	<tr>
		<td>NATIONALITY:</td>
		<td><b id="bold">FILIPINO </b></td>
	</tr>
	
	<tr>
		<td>REMARKS:</td>
		<td><b id="bold"><?=$info['applicant_remarks']?> </b></td>
	</tr>
	
	
	
	<tr>
		<td colspan="2"><h3 id="blue">Passport Information</h3></td>
		
	</tr>
		<tr>
		<td>Passport #:</td>
		<td><b id="bold"><?=$pass1['passport_number']?> </b></td>
	</tr>
	<tr>
		<td>Passport Issue:</td>
		<td><b id="bold"><?php if($pass1['passport_number']!='') {  echo date('F j, Y', strtotime($pass1['passport_issue'])); } ?> </b></td>
	</tr>
	
	<tr>
		<td>Passport Expiration:</td>
		<td><b id="bold"><?php if($pass1['passport_number']!='') { echo date('F j, Y', strtotime($pass1['passport_expiration'])); } ?> </b></td>
	</tr>
	
	
	<tr>
		<td colspan="2"><h3 id="blue"></br>Medical Information</h3></td>
		
	</tr>
	
	<tr>
		<td style="width:150px">Medical Clinic</td>
		<td><b id="bold"><?=$info['certificate_medical_clinic']?> </b></td>
	</tr>
	
	<tr>
		<td>EXAM DATE:</td>
		<td><b id="bold"><?php if($info['certificate_medical_clinic']!='') {  echo date('F j, Y', strtotime($info['certificate_medical_exam_date'])); } ?> </b></td>
	</tr>
	<tr>
		<td>EXPIRED DATE:</td>
		<td><b id="bold"><?php if($info['certificate_medical_clinic']!='') { echo date('F j, Y', strtotime($info['certificate_medical_expiration'])); } ?> </b></td>
	</tr>
	
	
	<tr>
		<td style="width:150px">REMARKS</td>
		<td><b id="bold"><?=$info['certificate_medical_remarks']?> </b></td>
	</tr>
	
	
	
		<tr>
		<td colspan="2"><h3 id="blue"></br>Principal Information</h3></td>
	</tr>
	
	<tr>
		<td>Contract Received:</td>
		<td><b id="bold"><?php if($info['sub_employer']!='') { echo date('F j, Y', strtotime($req1['requirement_contract'],'0000-00-00')); } ?> </b></td>
	</tr>
	<tr>
	<td>Principal:</td>
		<td><b id="bold"><?=$rowview['employer_name']?> </b></td>
	</tr>
	
	<tr>
	<td>Employer:</td>
		<td><b id="bold"><?=$info['sub_employer']?> </b></td>
	</tr>
	<td>Country:</td>
		<td><b id="bold"><?=$rowview['country_name']?> </b></td>
	</tr>
	
	
	
	<tr>
		<td colspan="2"><h3 id="blue"></br>OWWA INFORMATION</h3></td>
	</tr>
	
	<tr>
		<td style="width:150px">OWWA</td>
		<td><b id="bold"><?php if($info['certificate_owwa']==1) { echo'OK'; }?> </b></td>
	</tr>
	
	<tr>
		<td>OWWA FROM:</td>
		<td><b id="bold"><?php if($info['certificate_owwa']==1) { echo date('F j, Y', strtotime($info['certificate_owwa_from'],'0000-00-00')); }?> </b></td>
	</tr>
	<tr>
		<td>OWWA TO:</td>
	
		<td><b id="bold"><?php if($info['certificate_owwa']==1) { echo date('F j, Y', strtotime($info['certificate_owwa_to'],'0000-00-00')); }?> 
	</tr>
	
	<tr>
		<td colspan="2"><h3 id="blue"></br>VISA INFORMATION</h3></td>
	</tr>
	
	<tr>
		<td style="width:150px">VISA NO.</td>
		<td><b id="bold"><?=$req1['requirement_visa_no']?></b></td>
	</tr>
	
	<tr>
		<td style="width:150px">VISA CATEGORY.</td>
		<td><b id="bold"><?=$req1['requirement_visa_category']?></b></td>
	</tr>
	
	<tr>
		<td>VISA RELEASE:</td>
		<td><b id="bold"><?php if($req1['requirement_visa_no']!='') { echo date('F j, Y', strtotime($req1['requirement_visa_release_date'],'0000-00-00')); }?> </b></td>
	</tr>
	<tr>
		<td>VISA EXPIRED:</td>
	
		<td><b id="bold"><?php if($req1['requirement_visa_no']!='') { echo date('F j, Y', strtotime($req1['requirement_visa_expiration'])); }?> 
	</tr>
	
	
	<tr>
		<td colspan="2"><h3 id="blue"></br>OEC INFORMATION</h3></td>
	</tr>
	
	<tr>
		<td style="width:150px">OEC NO.</td>
		<td><b id="bold"><?=$req1['requirement_oec_number']?></b></td>
	</tr>
	
	<tr>
		<td>OWWA RELEASE:</td>
		<td><b id="bold"><?php if($req1['requirement_oec_number']!='') { echo date('F j, Y', strtotime($req1['requirement_oec_release_date'],'0000-00-00')); }?> </b></td>
	</tr>
	<tr>
		<td>OWWA EXPIRED:</td>
	
		<td><b id="bold"><?php if($req1['requirement_oec_number']!='') { echo date('F j, Y', strtotime($req1['applicant_requirement_oec_expired'])); }?> 
	</tr>
	
		<tr>
		<td colspan="2"><h3 id="blue"></br>FLIGHT INFORMATION</h3></td>
	</tr>
	
	<tr>
		<td style="width:150px">AIRLINES:</td>
		<td><b id="bold"><?=$req1['requirement_ticket']?></b></td>
	</tr>
	
	<tr>
		<td style="width:150px">TICKET NO.:</td>
		<td><b id="bold"><?=$req1['ticket_no']?></b></td>
	</tr>
	
	<tr>
		<td>FLIGHT DATE</td>
		<td><b id="bold"><?php if($req1['requirement_ticket']!='') { echo date('F j, Y', strtotime($req1['flight_date'])); }?> </b></td>
	</tr>
	<tr>
	<tr>
	<td style="width:150px">remarks:</td>
		<td><b id="bold"><?=$req1['ticket_remarks']?></b></td>
	</tr>
	
	
	
	
	
	</table>


</div>

