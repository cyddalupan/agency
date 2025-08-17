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
<?php
$counter=1;
include'db.php';
include'styles.php';
?>
<style>
body{
font-family:calibri;	
}
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





<a id="downloadLink" onclick="exportF(this)" style="color:Red;text-align:right">Export to excel</a>

<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:9px;width:88%;margin:0 auto" >

<thead>
<tr>
<th STYLE="WIDTH:10px;color:black">#</th>
<th style="font-size:10px">Name</th>
<th style="font-size:10px">FRA</th>
<th style="font-size:10px">CONTRACT RECEIVED</th>
<th style="font-size:10px">VISA STAMP</th>	
<th style="font-size:10px;width:30px">FLIGHT DATE</th>								
</tr>
</thead>
<tbody>

<tr>
<TD colspan="6"><h3 style="text-align:center"><h2 style="text-align:left"><?=$_GET['name']?></h2></TD>
</tr>
<tr>
<TD colspan="6"><h3 style="text-align:center"><h3 style="text-align:center">Visa Stamp</h3></TD>
</tr>

<?php
$counteronprocess=1;
$onprocess = mysql_query("SELECT * FROM applicant
LEFT JOIN applicant_passport
ON applicant.applicant_id = applicant_passport.passport_applicant
LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

LEFT JOIN  applicant_requirement
ON applicant.applicant_id = applicant_requirement.requirement_applicant

WHERE applicant_status NOT IN (9,21,22,1,14,25)
AND applicant_employer=".$_GET['emp_id']."
AND requirement_visa_stamp NOT IN ('0000-00-00','1970-01-01','')
order by requirement_visa_stamp desc");
?>   		
<?php

while($onprocess1=mysql_fetch_array($onprocess))
{			
$emp = mysql_query("SELECT * FROM employer where employer_id=".$onprocess1['applicant_employer']." ");
$employer=mysql_fetch_array($emp);
$cert = mysql_query("SELECT * FROM applicant_certificate where certificate_applicant=".$onprocess1['applicant_id']." ");
$row1=mysql_fetch_array($cert);

$req = mysql_query("SELECT * FROM applicant_requirement where requirement_applicant=".$onprocess1['applicant_id']." ");
$row2=mysql_fetch_array($req);

$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$onprocess1['applicant_source']." ");
$agentrow1=mysql_fetch_array($agent);


$number=str_pad($onprocess1['applicant_id'], 10, '0', STR_PAD_LEFT);
echo"<td>".$counteronprocess."</td>";
echo"<td><a target='_blank'  href='".$review."".$onprocess1['applicant_id']."'>
".$onprocess1['applicant_first']."  ".$row['applicant_last']."</a></td>";
echo"<td>".$employer['employer_name']."</td>";

IF($row2['requirement_contract']=='1970-01-01' || $row2['requirement_contract']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo"<td>With Contract</td>"; }

IF($row2['requirement_visa_stamp']=='1970-01-01' || $row2['requirement_visa_stamp']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo"<td>With Stamp</td>"; }

echo"<td></td>";
$counteronprocess++;
echo"</tr>";
}				

?>





<tr>
<TD colspan="6"><h3 style="text-align:center"><h3 style="text-align:center">On Process Applicants</h3></TD>
</tr>

<?php
$counteronprocess=1;
$onprocess = mysql_query("SELECT * FROM applicant
LEFT JOIN applicant_passport
ON applicant.applicant_id = applicant_passport.passport_applicant
LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

LEFT JOIN  applicant_requirement
ON applicant.applicant_id = applicant_requirement.requirement_applicant

WHERE applicant_status NOT IN (9,21,22,1,14,25)
AND  requirement_contract  NOT  IN ('0000-00-00','1970-01-01','')
AND  requirement_visa_stamp  IN ('0000-00-00','1970-01-01','')
AND applicant_employer=".$_GET['emp_id']."");
?>   		
<?php

while($onprocess1=mysql_fetch_array($onprocess))
{			
$emp = mysql_query("SELECT * FROM employer where employer_id=".$onprocess1['applicant_employer']." ");
$employer=mysql_fetch_array($emp);
$cert = mysql_query("SELECT * FROM applicant_certificate where certificate_applicant=".$onprocess1['applicant_id']." ");
$row1=mysql_fetch_array($cert);

$req = mysql_query("SELECT * FROM applicant_requirement where requirement_applicant=".$onprocess1['applicant_id']." ");
$row2=mysql_fetch_array($req);

$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$onprocess1['applicant_source']." ");
$agentrow1=mysql_fetch_array($agent);


$number=str_pad($onprocess1['applicant_id'], 10, '0', STR_PAD_LEFT);
echo"<td>".$counteronprocess."</td>";
echo"<td><a target='_blank'  href='".$review."".$onprocess1['applicant_id']."'>
".$onprocess1['applicant_first']."  ".$onprocess1['applicant_last']."</a></td>";
echo"<td>".$employer['employer_name']."</td>";

IF($row2['requirement_contract']=='1970-01-01' || $row2['requirement_contract']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo"<td>With Contract</td>"; }

IF($row2['requirement_visa_stamp']=='1970-01-01' || $row2['requirement_visa_stamp']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo"<td>with stamp</td>"; }

IF($row2['flight_date']=='1970-01-01' || $row2['flight_date']=='0000-00-00' || $row2['flight_date']=='' ) { echo"<td></td>"; }
else{ echo"<td>".$row2['flight_date']."</td>"; }

$counteronprocess++;
echo"</tr>";
}				

?>










<tr>
<TD colspan="6"><h3 style="text-align:center"><h3 style="text-align:center">Deployed Applicants</h3></TD>
</tr>
<?php

$app = mysql_query("SELECT deployed.*,
statuses.*,
applicant_passport.*,
applicant.applicant_id,
applicant.applicant_first,
applicant.applicant_contacts,
applicant.applicant_employer,
applicant.applicant_source,
applicant.applicant_last
 FROM deployed
LEFT JOIN applicant
ON deployed.deployed_applicant = applicant.applicant_id


LEFT JOIN applicant_passport
ON deployed.deployed_applicant = applicant_passport.passport_applicant


LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number


where
applicant_employer=".$_GET['emp_id']."
AND applicant_status=9
group by deployed_applicant
order by deployed_date desc");
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
echo"<td>".$counter."</td>";
echo"<td><a target='_blank'  href='".$review."".$row['applicant_id']."'>
".$row['applicant_first']."  ".$row['applicant_last']."</a></td>";
echo"<td>".$employer['employer_name']."</td>";

echo"<td colspan='4' style='text-align:right'>".$row['deployed_date']."</td>";
$counter++;
echo"</tr>";
}				

?>	






<tr>
<TD colspan="6"><h3 style="text-align:center"><h3 style="text-align:center;color:Red">Repat Applicants</h3></TD>
</tr>

<?php
$repatsss=1;
$repat = mysql_query("SELECT 
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
applicant_status=22
AND applicant_employer=".$_GET['emp_id']." order by repat_date desc");
?>   		
<?php

while($repat1=mysql_fetch_array($repat))
{			
$emp = mysql_query("SELECT * FROM employer where employer_id=".$repat1['applicant_employer']." ");
$employer=mysql_fetch_array($emp);
$cert = mysql_query("SELECT * FROM applicant_certificate where certificate_applicant=".$repat1['applicant_id']." ");
$row1=mysql_fetch_array($cert);

$req = mysql_query("SELECT * FROM applicant_requirement where requirement_applicant=".$repat1['applicant_id']." ");
$row2=mysql_fetch_array($req);

$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$repat1['applicant_source']." ");
$agentrow1=mysql_fetch_array($agent);


$number=str_pad($repat1['applicant_id'], 10, '0', STR_PAD_LEFT);
echo"<td>".$repatsss."</td>";
echo"<td><a target='_blank'  href='".$review."".$repat1['applicant_id']."'>
".$repat1['applicant_first']."  ".$repat1['applicant_last']."</a></td>";
echo"<td><a target='_blank'  href='".$review."".$row['applicant_id']."'>
".$row['applicant_first']."  ".$row['applicant_last']."</a></td>";
echo"<td>".$employer['employer_name']."</td>";


IF($row2['flight_date']=='1970-01-01' || $row2['flight_date']=='0000-00-00' ) { echo"<td></td>"; }
else{ echo"<td colspan='4'>".$row2['flight_date']."</td>"; }

$repatsss++;
echo"</tr>";
}				

?>







</tbody>
</table>





</body>
<!--  /Body -->
</html>
