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
<h3><span> Repat Applicants</span></h3>
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
<p class="date-filter">Date: <?php echo $_POST['start']; ?> &minus; <?php echo $_POST['end']; ?></p>

<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:9px;width:99%" >
<thead>
<tr>
<th STYLE="WIDTH:10px;color:white">.</th>
<th STYLE="WIDTH:10px;color:white">.</th>
<th style="font-size:10px;width:20%">Name</th>
<th style="font-size:10px;width:20%">CONTACT #</th>	
<th style="font-size:10px;width:20%">FRA</th>
<th style="font-size:10px">PASSPORT</th>
<th  style="font-size:10px">MEDICAL </th>	
<th  style="font-size:10px">CLINIC</th>	
<th  style="font-size:10px">RESULT</th>		
<th style="font-size:10px">TESDA</th>
<th style="font-size:10px">SELECTED </th>	
<th style="font-size:10px">AGENT</th>	
<th style="font-size:10px">C. RECEIVED</th>
<th style="font-size:10px">C. SIGNED</th>	
<th style="font-size:10px">OWWA</th>	
<th style="font-size:10px">STAMP</th>	
<th style="font-size:10px">OEC</th>	
<th style="font-size:10px">STATUS</th>
<th style="font-size:10px;width:30px">DATE REPAT</th>
									
</tr>
</thead>
<tbody>

<?php
include'db.php';
if($_POST['employer']=='99999'){
	
	$employer="";	
}
if($_POST['employer']!='99999'){
$employer="applicant_employer=".$_POST['employer']." AND";	
}

if($_POST['agent']=='99999'){

	$agent="";	
}
if($_POST['agent']!='99999'){
$agent="applicant_source=".$_POST['agent']." AND";	
}


$app = mysql_query("SELECT 
statuses.*,
applicant_passport.*,
applicant.applicant_id,
applicant.applicant_first,
applicant.applicant_contacts,
applicant.applicant_employer,
applicant.repat_date,
applicant.applicant_source,
applicant.applicant_status,
applicant.applicant_last
 FROM applicant


LEFT JOIN applicant_passport
ON applicant.applicant_id = applicant_passport.passport_applicant


LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number


where
$employer
$agent
repat_date between '".$_POST['start']."' AND '".$_POST['end']."'
AND applicant_status=22
order by repat_date desc
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



$number=str_pad($row['applicant_id'], 10, '0', STR_PAD_LEFT);
echo"<td></td>  ";	
echo"<td></td>  ";
echo"<td><a target='_blank'  href='http://stepupmanpower.com/admin/admin/applicants/review_single/".$row['applicant_id']."'>
".$row['applicant_first']."  ".$row['applicant_last']."</a></td>";
echo"<td>".$row['applicant_contacts']."</td>";
echo"<td>".$employer['employer_name']."</td>";
echo"<td>".$row['passport_number']."</td>";
echo"<td>".$row1['certificate_medical_exam_date']."</td>";
echo"<td>".$row1['certificate_medical_clinic']."</td>";
echo"<td>".$row1['certificate_medical_result']."</td>";

echo"<td>".$row1['certificate_tesda_date']."</td>";
echo"<td>".$row['applicant_updated']."</td>";

echo"<td>".$agentrow1['agent_first']." ".$agentrow1['agent_last']."</td>";
IF($row2['requirement_contract']=='1970-01-01' || $row2['requirement_contract']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo"<td>".$row2['requirement_contract']."</td>"; }

IF($row2['requirement_contract_sign']=='1970-01-01' || $row2['requirement_contract_sign']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo"<td>".$row2['requirement_contract_sign']."</td>"; }

echo"<td>".$row1['owwa_number']."</td>";
IF($row2['requirement_visa_stamp']=='1970-01-01' || $row2['requirement_visa_stamp']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo"<td>".$row2['requirement_visa_stamp']."</td>"; }
echo"<td>".$row2['requirement_oec_number']."</td>";
echo"<td>".$row['statusText']."</td>";
echo"<td>".$row['repat_date']."</td>";
$counter++;
echo"</tr>";
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


<!-- Datatable js -->
