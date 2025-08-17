<?php include'review.php';?>
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


<div class="row" style="margin:30px">
<div class="col-md-12"  style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC;border:1px solid #DCDCDC;width:120%">



<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3><span> All Applicants</span></h3>
<!-- para -->
</div> 
<?php include'menu.php';?>


<ul id="menu">
<li><a   class="active" href="all-applicants.php?id=15">All Status</a></li>
<li><a href="med.php?id=15">Medical</a></li>
<li><a href="passporting.php">Passporting</a></li>
<li><a href="line.php">Line Up</a></li>
<li><a href="preselected.php">Pre-Selected</a></li>
<li><a   href="all-app.php?id=4">On Process</a></li>
<li><a href="booking.php?id=4">For Deployment</a></li>
<li style="float:right">
<form method="post" class="form" id="form2" style="width:300px;float:left">

<select name="status_app" class="form-control" required style="width:190px;float:left">
<option value="">--Select--</option>
<option value="0">All</option>
<option value="5">Available/Passporting/</option>
<option value="1">Line up / Reserved / Pre Selected/Selected</option>
<option value="2">For Deployment</option>
<option value="3">Deployed/finished Contract</option>
<option value="4">Cancelled/unfit/backout/blacklist</option>
<option value="6">Repat</option>
</select>

<input type='submit'class="btn btn-sm btn-danger" name='filter' value='Filter' style="height:34px;float:left;margin-left:10px;border:2px solid white;WIDTH:50PX">
</form>
</li>

</ul>


<div style="clear:both;height:10px"></div>

<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">
<?php
$counter=1;
?>


<div style="clear:both"></div>
<a id="downloadLink" onclick="exportF(this)" style="color:Red">Export to excel</a>




<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="width:85%" >
<thead>
<tr>
<th STYLE="WIDTH:3px;"></th>
<th STYLE="WIDTH:55px"></th>
<th style="font-size:11px;">Name</th>
<th style="font-size:11px;width:250px">CONTACT #</th>	
<th style="font-size:11px;">FRA</th>
<th style="font-size:11px;">AGE</th>
<th style="font-size:11px;">ADDRESS</th>
<th style="font-size:11px;">FB</th>
<th style="font-size:11px">STATUS</th>
<th style="font-size:10px">Deployed</th>
									
</tr>
</thead>
<tbody>

<?php
include'db.php';

$app = mysql_query("SELECT 
statuses.*,
applicant.applicant_id,
applicant.applicant_first,
applicant.applicant_contacts,
applicant.applicant_contacts_2,
applicant.applicant_contacts_3,
applicant.applicant_employer,
applicant.repat_date,
applicant.applicant_source,
applicant.applicant_status,
applicant.applicant_age,
applicant.applicant_photo,
applicant.applicant_address,
applicant.applicant_last
 FROM applicant

LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number
order by applicant_id desc limit 100
");
 
if(isset($_POST['filter'])){

if($_POST['status_app']=='0'){
$stt="";	
}
if($_POST['status_app']=='1'){
$stt="where applicant_status IN (5,4,2,3)";	
}
if($_POST['status_app']=='2'){
$stt="where applicant_status IN (12,8)";		
}
if($_POST['status_app']=='3'){
$stt="where applicant_status IN (9,23)";	
}
if($_POST['status_app']=='4'){
$stt="where applicant_status IN (14,21,1,7)";	
}
if($_POST['status_app']=='5'){
$stt="where applicant_status IN (10,15,0)";	
}
if($_POST['status_app']=='6'){
$stt="where applicant_status IN (22)";	
}


$app = mysql_query("SELECT 
statuses.*,
applicant.applicant_id,
applicant.applicant_first,
applicant.applicant_contacts,
applicant.applicant_contacts_2,
applicant.applicant_contacts_3,
applicant.applicant_employer,
applicant.repat_date,
applicant.applicant_source,
applicant.applicant_status,
applicant.applicant_age,
applicant.applicant_photo,
applicant.applicant_address,
applicant.applicant_last
 FROM applicant

LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number
$stt
");



}	
 
?>   		
<?php

while($row=mysql_fetch_array($app))
{			
$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$employer=mysql_fetch_array($emp);

$deployed = mysql_query("SELECT * FROM deployed where deployed_applicant=".$row['applicant_id']." ");
$deployed1=mysql_fetch_array($deployed);

echo"<td>".$counter."</</td>";
if($row['applicant_photo']==""){echo"<td></td>";}
if($row['applicant_photo']!=""){echo"<td><img src='".$photo."".$row['applicant_photo']."' style='width:50px'></td> ";}

if($row['applicant_contacts_2']!=''){$contact2=",".$row['applicant_contacts_2']."";}
if($row['applicant_contacts_3']!=''){$contact3=",".$row['applicant_contacts_3']."";}
echo"<td>".$row['applicant_last'].", ".$row['applicant_first']."  ".$row['applicant_middle']." </td>";
echo"<td>".$row['applicant_contacts']."  ".$contact2."   ".$contact3."</td>";
echo"<td>".$employer['employer_name']."</td>";
echo"<td>".$row['applicant_age']."</td>";
echo"<td>".$row['applicant_address']."</td>";
echo"<td></td>";
echo'<td><button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:9px">'.$row["status"].'</button></td>';
if($row['applicant_status']==9){echo"<td>".$deployed1['deployed_date']."</td>";}
if($row['applicant_status']!=9){echo"<td><small></smalll></td>";}
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

