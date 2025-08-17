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

$con = mysql_connect("localhost","iwebfram_land","^1PCS)XlQ{Hx");
mysql_set_charset ( "latin1_swedish_ci", $con );
if (!$con)
{
die('Could not connect: ' . mysql_error());
}
mysql_select_db("iwebfram_landbase", $con);
if($_POST['sub_status']=='1'){
$sub='';

}
if($_POST['sub_status']!='1'){
$sub="AND applicant.sub_status='".$_POST['sub_status']."'";
}



if($_POST['status']=='1'){
$status='';
}

if($_POST['status']!='1'){
$status="AND applicant.status='".$_POST['status']."'";
}

if($_POST['counter_part']=='9999'){
$counter_part="applicant.applicant_employer!=''";
}

if($_POST['counter_part']!='9999'){
$counter_part="applicant.applicant_employer='".$_POST['counter_part']."'";
}





$app = mysql_query("SELECT  applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.applicant_id


LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.applicant_id
where 
$counter_part
$sub
$status
");
$counter = 1; 
?>
<div id="wrapper">
<h1>CB & T - STAFF,INC</h1>
<h2>STATUS REPORT </h2>
<H2><?=$_POST['status']?></H2>

<script>
//code to export html to excel coded by:chachi yr: 2000
//no need to edit bellow
var tableToExcel = (function() {
var uri = 'data:application/vnd.ms-excel;base64,'
, template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
, base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
, format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
return function(table, name) {
if (!table.nodeType) table = document.getElementById(table)
var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
window.location.href = uri + base64(format(template, ctx))
}
})()

</script>

<input type="button" onclick="tableToExcel('testTable', 'Alumni Tracer')" value="Export to Excel" style="background:red;color:white;padding:3px">
<table class="gridtable" id="testTable">
<thead>

<tr>

	<th></th>
	<th>Name</th>
	<th>Date <?=$_POST['status']?></th>
	<th>Sponsor</th>
	<th>City</th>
	<th>VFS</th>
	<th>Sign Contract</th>
	<th>OWWA Schedule</th>
	<th>OWWA Release</th>
	<th>VISA stamp</th>
	<th>OEC Filing</th>
	<th>OEC Release</th>
	
	<th>Flight</th>
	<th>Remarks</th>
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
echo'<td>'.$row["applicant_first"].' '.$row["applicant_last"].'</td>';
IF($row["status_date"]!='0000-00-00'){echo'<td>'.$row["status_date"].'</td>';}
IF($row["status_date"]=='0000-00-00'){echo'<td></td>';}
echo'<td style="color:red;font-size:10px">'.$row["sub_employer"].' </td>';
echo'<td>'.$row["applicant_preferred_country"].'</td>';


IF($row["biometrics"]!='0000-00-00'){echo'<td>'.$row["biometrics"].'</td>';}
IF($row["biometrics"]=='0000-00-00'){echo'<td></td>';}

echo'<td>'.$row["requirement_remarks"].'</td>';
IF($row["certificate_owwa_from"]!='0000-00-00'){echo'<td>'.$row["certificate_owwa_from"].'</td>';}
IF($row["certificate_owwa_from"]=='0000-00-00'){echo'<td></td>';}

echo'<td>'.$row["certificate_owwa_to"].'</td>';
IF($row["requirement_visa_release_date"]!='0000-00-00'){
echo'<td>'.$row["requirement_visa_release_date"].'</td>';}
IF($row["requirement_visa_release_date"]=='0000-00-00'){echo'<td></td>';}

IF($row["requirement_oec_submission_date"]!='0000-00-00'){
echo'<td>'.$row["requirement_oec_submission_date"].'</td>';}
IF($row["requirement_oec_submission_date"]=='0000-00-00'){echo'<td></td>';}


IF($row["requirement_oec_release_date"]!='0000-00-00'){
echo'<td>'.$row["requirement_oec_release_date"].'</td>';}
IF($row["requirement_oec_release_date"]=='0000-00-00'){echo'<td></td>';}
echo'<td>'.$row["flight"].'</td>';
echo'<td style="color:Red"><b style="color:green">'.$row["status"].' </b></br>'.$row["sub_status"].'</td>';

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
