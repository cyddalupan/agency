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
width:1100px;
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
include'inc/functions.php';
$app = mysql_query("SELECT email_address2.app_id as adonis, applicant_certificate.*,applicant_requirement.*,email_address2.* FROM email_address2
LEFT JOIN applicant_certificate
ON email_address2.app_id=applicant_certificate.app_id

LEFT JOIN 	applicant_requirement
ON email_address2.app_id=applicant_requirement.app_id

where email_address2.status='Selected'
AND email_address2.counter_part='4'
ORDER BY email_address2.app_id desc limit 50");

?>
<div id="wrapper">
<h1>NETWORK AGENCY </h1>
<h2>STATUS REPORT </h2>





<table class="table table-bordered">
<thead>

<tr>

	<th STYLE="width:10%">DATE</th>
	<th STYLE="width:10%">#</th>
	<th>Name</th>
	<th>Passport</th>
	<th>Date EXPIRED</th>
	<th>Country</th>
	<th>PEOS</th>
	<th>TESDA</th>
	<th>PDOS</th>
	<th>BIOMETRICS</th>
	<th>OWWA</th>
	<th>Agent</th>
	<th>Agency</th>
	<th>Status</th>
	<th>ACTION</th>
	</tr>
</thead>
<tbody>
<?php	
$counter1 = 1; 
while($row=mysql_fetch_array($app))
{
$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);

$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['counter_part']." ");
$emprow=mysql_fetch_array($emp);




echo'<tr>';

echo'<td>'; echo date('Y-M-d', strtotime($row['date_applied']));echo'</td>';
echo'<td>'.$row["adonis"].'</td>';
echo'<td>'.$row["name"].' '.$row["lname"].'</td>';
echo'<td>'.$row["passport"].'</td>';
echo'<td>'.$row["ppt_expired"].'</td>';
echo'<td>'.$row["country"].'</td>';
echo'<td>'.$row["peos"].'</td>';
echo'<td>'.$row["certificate_tesda"].'</td>';
echo'<td>'.$row["certificate_pdos"].'</td>';
echo'<td>'.$row["biometrics"].'</td>';
echo'<td>'.$row["certificate_owwa"].'</td>';
echo'<td style="color:orange;font-size:10px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$emprow["employer_name"].'</td>';
echo'<td style="color:Red"><b style="color:green">'.$row["status"].' </b>-'.$row["sub_status"].'</td>';

echo'<td><a href="profile_applicants.php?appid='.$row['adonis'].'" STYLE="COLOR:BLUE" target="_blank">View</A></td>';
echo'</tr>';
$counter++;
}
?>
</tbody>
</table>





</BR></BR></BR>

<table class="table table-bordered">
<thead>
<tr>
<th>#</th>
<th STYLE="width:10%">DATE</th>
<th STYLE="width:10%">REF NO.</th>
<th >DESCRIPTION</th>
<th STYLE="width:5%">CURRENCY</th>
<th STYLE="width:5%">AMOUNT</th>

</tr>
</thead>
<tbody>
<?php	
$counter = 1; 
while($row1=mysql_fetch_array($app1))
{
$totalamountUSD=$row1['amount']+$totalamountUSD;
echo'<tr>';
echo'<td>'.$counter.'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row1['date_ad']));echo'</td>';

echo'<td>'.$row1["ref"].'</td>';
echo'<td>'.$row1["description"].'</td>';
echo'<td>'.$row1["currency"].'</td>';
echo'<td>'.$row1["amount"].'</td>';
echo'</tr>';
$counter++;
}

$usd = number_format($totalamountUSD, 2);
?>
<tr>
<td  colspan="5"  style="font-weight:bold;text-align:right;font-size:14px;color:red">USD</td>
<td  style="font-weight:bold;text-align:right;font-size:14px;color:red"> <?=$usd;?> </td>
</TR>
</tbody>
</table>

<div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div>
</DIV>


</body>
</html>
