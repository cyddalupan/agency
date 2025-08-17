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
<link href="../assets/styles/admin/bootstrap.min.css" rel="stylesheet" />
<link href="../assets/styles/admin/font-awesome.min.css" rel="stylesheet" />


<!--Beyond styles-->
<link id="beyond-link" href="../assets/styles/admin/beyond.min.css" rel="stylesheet" type="text/css" />
<script>
function exportF(elem) {
  var table = document.getElementById("example-checkbox");
  var html = table.outerHTML;
  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
  elem.setAttribute("href", url);
  elem.setAttribute("download", "export.xls"); // Choose the file name
  return false;
}
</script>

 
</head>
<style>
th{
font-size:12px;
font-weight:none;
color:blue;	
padding:1px;
}
tD{
padding:1px;
}
</style>

<!-- /Head -->
<!-- Body -->
<body style="background:white">

<div class="col-md-14 col-sm-14" style="margin-top:-1px">
<div class="row">
<div class="col-md-12">

<div class="clients-page">

<div class="col-sm-2 pull-right" align="center">
<a href="javascript:window.close()" class="btn btn-md btn-danger">Close</a>
</div>

<div class="row">
<div class="col-md-12" style="margin:20px">
<?php

if($_GET['book']==1){
$stt="applicant_status IN (8,12)";	
$titi='BOOKING';
$meme="";
}


if($_GET['book']==2){
$stt="applicant_status IN (8,12)";	
$titi='BOOKING';
$meme="";
}


include'db.php';
if($_POST['employer']=='99999'){

	$employer="";	 

}

if($_POST['employer']!='99999'){
$employer="applicant_employer=".$_POST['employer']." AND";	
}


if($_POST['sub_status']=='99999'){

	$sub_status="";	
}
if($_POST['sub_status']!='99999'){
$sub_status="sub_status='".$_POST['sub_status']."' AND";	
}


if($_POST['agent']=='99999'){

	$agent="";	
}
if($_POST['agent']!='99999'){
$agent="applicant_source=".$_POST['agent']." AND";	
}



?>

<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3><span> <?=$titi?> Applicants </span></h3>
<!-- para -->
</div> 

<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">
<?php
$counter=1;
?>
<a id="downloadLink" onclick="exportF(this)" style="color:Red">SAVE MY WORK</a>



<style>
#example-checkbox td,th{
font-size:12px;	
}
</style>

<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:9px;width:99%" >
<thead>
<tr>
<th style="width:3%"></th>
<th style="width:3%">#</th>
<th>Date Applied</th>

<th>Applicant Name</th>
<th>Age</th>
<th>Position</th>
<th>Passport</th>
<th>Country</th>
<th>Agent</th>
<th>Airlines</th>
<th >Flight Date</th>
<th >Flight Time</th>
<th>Employer/Prinicipal</th>
<th>Status</th>
</tr>
<tr>

</thead>
<tbody>

<?php
$count=1;




$app = mysql_query("SELECT * FROM applicant


LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

LEFT JOIN  	applicant_certificate
ON applicant.applicant_id = applicant_certificate.certificate_applicant


LEFT JOIN  	applicant_requirement
ON applicant.applicant_id =applicant_requirement.requirement_applicant

LEFT JOIN  	employer
ON applicant.applicant_employer =employer.employer_id

LEFT JOIN  	recruitment_agent
ON applicant.applicant_source =recruitment_agent.agent_id


LEFT JOIN  applicant_passport
ON applicant.applicant_id =	applicant_passport.passport_applicant	


where
$employer
$agent
$stt

 ");
?>   		
<?php

while($row=mysql_fetch_array($app))
{			
$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);
$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);


echo'<tr>';
echo'<td></td>';
echo'<td>'.$count.'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';


echo'<td><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';
echo"<td>".$row['applicant_age']."</td>";
echo"<td>".$position1['position_name']."</td>";
echo'<td>'.$row["passport_number"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';

echo'<td style="color:orange;font-size:10px">'.$row["agent_first"].' '.$row["agent_last"].'</td>';

echo'<td>'.$row["requirement_ticket"].'</td>';
IF($row['flight_date']=='1970-01-01' || $row['flight_date']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo"<td>".$row['flight_date']."</td>"; }
echo'<td>'.$row["ticket_plus"].'</td>';


echo'<td>'.$row["employer_name"].'</td>';
echo'<td>
<a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">
<button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:9px">'.$row["status"].'</button></a></td>';

echo'</tr>';
$count++;
}				

?>	

</tbody>
</table>
<div style="clear:both;height:40px"></div>
</div>

</div>
</div>

</div>
</div>
</div>


</div>
</div>
</div>
</div>



</body>
<!--  /Body -->
</html>

<script src="assets/js/jquery-1.12.2.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/propeller.min.js"></script>


