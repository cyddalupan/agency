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
<h3><span> Deployed Applicants (FOR TRACK & TRACE)</span></h3>
<!-- para -->
</div> 

<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">
<?php
$counter=1;
?>
<a id="downloadLink" onclick="exportF(this)" style="color:Red">Export to excel</a>

<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:10px;width:98%" >
<thead>
<tr>
<th style="font-size:10px">Name</th>
<th style="font-size:10px">Contact</th>	
<th style="font-size:10px">Address</th>
<th style="font-size:10px">Birthday</th>
<th style="font-size:10px">PASSPORT</th>
<th style="font-size:10px">Hire Date</th>
<th style="font-size:10px">Deployed date</th>
<th style="font-size:10px">User type</th>
<th style="font-size:10px">country</th>
<th style="font-size:10px">principal</th>
<th style="font-size:10px">agent</th>
<th style="font-size:10px">status</th>
	</tr>
</thead>
<tbody>

<?php
include'db.php';

$app = mysql_query("SELECT deployed.*,
statuses.*,
applicant_passport.*,
applicant.applicant_id,
applicant.applicant_first,
applicant.applicant_contacts,
applicant.applicant_employer,
applicant.applicant_source,
applicant.applicant_date_applied,
applicant.applicant_birthdate,
applicant.applicant_address,
applicant.applicant_preferred_country,
applicant.applicant_last
 FROM deployed
LEFT JOIN applicant
ON deployed.deployed_applicant = applicant.applicant_id


LEFT JOIN applicant_passport
ON deployed.deployed_applicant = applicant_passport.passport_applicant


LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

where
deployed_date between '".$_POST['start']."' AND '".$_POST['end']."'
AND applicant_status=9
group by applicant_id
order by deployed_date desc


 ");
?>   		
<?php

while($row=mysql_fetch_array($app))
{			


echo"<td>".$row['applicant_first']."  ".$row['applicant_last']."</td>";
echo"<td>".$row['applicant_contacts']."</td>";
echo"<td>".$row['applicant_address']."</td>";
echo"<td>".$row['applicant_birthdate']."</td>";
echo"<td>".$row['passport_number']."</td>";
echo"<td>".$row['applicant_date_applied']."</td>";
echo"<td>".$row['deployed_date']."</td>";
echo"<td>4</td>";
echo"<td>".$row['applicant_preferred_country']."</td>";
echo"<td>".$row['applicant_employer']."</td>";
echo"<td>".$row['applicant_source']."</td>";
echo"<td>9</td>";
$counter++;
echo"</tr>";
}				

?>	

</tbody>
</table>
<div style="clear:both;height:40px"></div>

<?=$counter?>

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


