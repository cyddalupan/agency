<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<!-- Meta, title, CSS, favicons, etc. -->
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Accounting System</title>
</head>

<body>
<style>
#wrapper{
border:0px solid black;
width:1200px;
min-height:630px;
height:!important;
margin: 0 auto;	
padding:20px;
font-size:12px;
font-family:calibri;
}
#clear{
clear:both;
height:5px;	
}
table{
border:0px solid black;	
border-collapse: collapse;
width:100%;
margin: 0 auto;	
}
table td{
border:1px solid black;
padding:3px;
font-size:12px;
}
table th{
border:1px solid black;
font-weight:bold;
font-size:11px;
}
h2{
text-align:center;
font-size:16px;
}
h3{
text-align:center;
font-size:14px;
MARGIN-TOP:-10px;
}
h1{
text-align:center;
MARGIN-TOP:-10px;
font-size:20px;
}
</style>




<?PHP

include'db.php';


$app = mysql_query("SELECT * FROM applicant
LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.applicant_id


LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.applicant_id
where 
applicant.status='".$_POST['status']."'
AND applicant.applicant_employer='".$_POST['counter_part']."'
AND applicant.flight between '".$_POST['start']."' AND '".$_POST['end']."'
ORDER BY applicant.status_date DESC
");
$counter = 1; 
?>
<div id="wrapper">
<h1>CB & T - STAFF,INC </h1>
<h2>STATUS REPORT  (<?=$_POST['status']?>)</h2>





<table class="table table-bordered">
<thead>

<tr>
	<th></th>	

	<th STYLE="width:10%">DATE APPLIED</th>
	<th STYLE="width:10%">#</th>
	<th>Name</th>
	<th>Country</th>
	
	<th>Agent</th>
	<th>Agency</th>
	<th>Date Deployed</th>
	<th>User</th>
	</tr>
</thead>
<tbody>
<?php	
$counter1 = 1; 
while($row=mysql_fetch_array($app))
{
$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$row1=mysql_fetch_array($app1);

$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$emprow=mysql_fetch_array($emp);




echo'<tr>';
echo'<td>'.$counter.'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';
echo'<td>'.$row["applicant_id"].'</td>';
echo'<td>'.$row["applicant_first"].' '.$row["applicant_last"].'</td>';
echo'<td>'.$row["applicant_preferred_country"].'</td>';

echo'<td style="color:orange;font-size:10px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$emprow["employer_name"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['flight']));echo'</td>';
echo'<td>'.$row["user"].'</td>';
echo'</tr>';
$counter++;
}
?>
</tbody>
</table>






<div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div>
</DIV>


</body>
</html>
