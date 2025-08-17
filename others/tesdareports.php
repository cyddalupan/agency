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
font-size:8px;
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


<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3><span> Deployed Applicants</span></h3>
<!-- para -->
</div> 

<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">
<?php
$counter=1;
?>

<p class="date-filter">Date: <?php echo $_POST['start']; ?> &minus; <?php echo $_POST['end']; ?></p>
<a id="downloadLink" onclick="exportF(this)" style="color:Red">Export to excel</a>

<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:9px;width:98%" >
<thead>
<tr>
<th STYLE="WIDTH:10px;">#</th>	
<th>Applicant #</th>
<th STYLE="WIDTH:120px">Applicant Name</th>
<th STYLE="WIDTH:120px">Contact #</th>	
<th STYLE="WIDTH:220px">Training Center</th>
<th>Training Date</th>
<th >Release Date of Cert</th>			
</tr>
</thead>
<tbody>

<?php
include'db.php';
$counter=1;

if($_POST['status_medical']=='99999'){
$medicalstatus="";	
$titi='ALL Status';
}
if($_POST['status_medical']!='99999'){
$medicalstatus="applicant_status IN (8,12)";
$medicalstatus="AND certificate_tesda_date!='".$_POST['status_medical']."'";		
$titi=$_POST['status_medical'];
}

 $app = mysql_query("SELECT applicant_certificate.*,applicant.*,statuses.*


 FROM applicant
LEFT JOIN applicant_certificate ON 
applicant.applicant_id = applicant_certificate.certificate_applicant


LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

where certificate_tesda_date between '".$_POST['start']."' AND '".$_POST['end']."'
order by applicant_certificate.certificate_tesda_date desc
");
 
 
?>   		
<?php

while($row=mysql_fetch_array($app))
{			
$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$employer=mysql_fetch_array($emp);

$cert = mysql_query("SELECT * FROM applicant_certificate where certificate_applicant=".$row['applicant_id']." ");
$row1=mysql_fetch_array($cert);

$req = mysql_query("SELECT * FROM applicant_requirement where requirement_applicant=".$row['applicant_id']." ");
$row2=mysql_fetch_array($req);

$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$agentrow1=mysql_fetch_array($agent);

$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);

$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);


$number=str_pad($row['applicant_id'], 10, '0', STR_PAD_LEFT);
IF($row['certificate_medical_exam_date']=='1970-01-01' || $row['certificate_medical_exam_date']=='0000-00-00' || $row['certificate_medical_exam_date']=='' ) {
$effectiveDate = '';
}
else{$effectiveDate=strtotime("+3 months", strtotime($row['certificate_medical_exam_date']));}


echo"<tr>";	
echo"<td>".$counter."</td>";
echo"<td>000000".$row['applicant_id']."</td>";
echo"<td>".$row['applicant_first']."  ".$row['applicant_last']."</td>";
echo"<td>".$row['applicant_contacts']."</td>";
echo"<td>".$row1['certificate_tesda_name']."</td>";
echo"<td>".$row1['certificate_tesda_date']."</td>";


IF($row['certificate_tesda_release']=='1970-01-01' || $row['certificate_tesda_release']=='0000-00-00' || $row['certificate_tesda_release']=='' ) {
echo"<td>--</td>";
}
else{ 
echo"<td >".$row['certificate_tesda_release']."</td>";
}


echo"</tr>";
$counter++;
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

