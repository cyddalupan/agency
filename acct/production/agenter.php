<?php
session_start();
if(!isset($_SESSION['staff'])){
header("location:logout.php");
}
include'tracking.php';
include'styles.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
   
    <title>Accounting System</title>

    <!-- Bootstrap -->
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
	<!-- Custom Theme Style -->
    <link href="../build/css/custom.min.css" rel="stylesheet">
	<!-- Datatable js -->

	<!-- jQuery: required (tablesorter works with jQuery 1.2.3+) -->
	<script src="docs/js/jquery-1.2.6.min.js"></script>
<!-- jQuery -->
<script src="../vendors/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>


	

  </head>
<style>
<TABLE>
table td,th{
color:black;	
}
</table>
</style>
<?php
include'r/db.php';
include'r/functions.php';

?>


<div class="x_panel">

<h2>AGENT APPLICANTS<SMALL><a href="paid-app.php" style="color:white;font-size:10px;background:green;padding:4px"></a></SMALL></h2>



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

<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:14px;padding:5px">Export File</a> 




<table  class="tablesorter"  id="example-checkbox">
<thead>
<tr>
<th STYLE="width:2%"></th>
<th>DATE</th>
<th>EX/F</th>
<th>Name</th>

<th>Passport</th>
<th>Country</th>
<th>Principal</th>
<th>Agent</th>
<th>Position</th>
<th>Date Medical</th>	
<th>Result</th>
<th>w/ Contract</th>
<th>Deployed</th>
<th>Status</th>

<th>Total Commission(₱)</th>
<th>Deduction (₱)</th>
<th>Paid (₱)</th>
<th style="color:Red">Balance (₱)</th>
<th>Status</th>


</tr>
</thead>
<tbody>
<?php


$app = mysql_query("SELECT * FROM applicant
WHERE applicant_paid!=1
ORDER BY applicant_id ASC  limit 5");



if(isset($_POST['updatedata1']))
{
$app = mysql_query("SELECT * FROM applicant
where
applicant_source=".$_POST['agent_id']."");


}


if(isset($_POST['Search1']))
{
$app = mysql_query("SELECT * FROM applicant
where
applicant_source=".$_POST['agent_id']."
");

		

}





while($row=mysql_fetch_array($app))
{
$status = mysql_query("SELECT * FROM statuses where number=".$row['applicant_status']." ");
$srow=mysql_fetch_array($status);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$row1=mysql_fetch_array($app1);


$req = mysql_query("SELECT * FROM applicant_requirement where requirement_applicant=".$row['applicant_id']." ");
$row2=mysql_fetch_array($req);


$cert = mysql_query("SELECT * FROM applicant_certificate where certificate_applicant=".$row['applicant_id']." ");
$cert1=mysql_fetch_array($cert);



$deployed = mysql_query("SELECT * FROM deployed where deployed_applicant=".$row['applicant_id']." order by deployed_id desc");
$deployed1=mysql_fetch_array($deployed);



$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$emprow=mysql_fetch_array($emp);

$passport = mysql_query("SELECT * FROM applicant_passport where passport_applicant=".$row['applicant_id']." ");
$passportrow=mysql_fetch_array($passport);

$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);

$LIQ = mysql_query("SELECT sum(amount) as cashfund FROM  email_address6 
where  app_id=".$row['applicant_id']."");
$cashliq=mysql_fetch_array($LIQ);


$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);
$balancecollection=$row['singil']-$cashliq['cashfund'];


$agree = mysql_query("SELECT * FROM agent_agreement where agent_id=".$row['applicant_source']." 
AND agree_country=".$countryrow['country_id']."");
$agree1=mysql_fetch_array($agree);






$totaldef = mysql_query("SELECT SUM(deduction_amount) AS totaldeduct, deduction.* FROM deduction
where app_id=".$row['applicant_id']." ");
$totaldef1=mysql_fetch_array($totaldef);








$totalcom=$row['agent_ppt'] + $row['agent_ftw'] + $row['agent_contract'] + $row['agent_deployed'];

if($row['applicant_paid1']!=1)
{ $paids='UNPAID'; }  

if($row['applicant_paid1']==1)
{ $paids='PAID'; } 

if($row["applicant_ex"]=="FIRSTIMER")
{
    $color111="";
    $color21="#CD5C5C";
}
else{
  $color111="";
    $color21="";   
}

echo'<tr style="background:'.$color111.';color:'.$color21.'">';
echo "<td><input type='hidden' name='applicant_id[]' value='".$row['applicant_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';
echo'<td>'.$row["applicant_ex"].'</td>';
echo'<td><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';
echo'<td>'.$passportrow["passport_number"].'</td>';

echo'<td>'.$countryrow["country_name"].'</td>';
echo'<td>'.$emprow["employer_name"].'</td>';
echo'<td style="color:black;font-size:12px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$position1["position_name"].'</td>';


IF($cert1['certificate_medical_exam_date']=='1970-01-01' || $cert1['certificate_medical_exam_date']=='0000-00-00' || $cert1['certificate_medical_exam_date']=='' ) {
      
echo"<td>--</td>";
}
else{ 
echo"<td >".$cert1['certificate_medical_exam_date']."</td>";
}

if($cert1['certificate_medical_result']=='FIT TO WORK'){
$certmem="";    
echo"<td>".$cert1['certificate_medical_result']."</td>";
}
if($cert1['certificate_medical_result']!='FIT TO WORK'){
    $certmem="";
echo"<td STYLE='color:black;font-size:9px'>--</td>";
}







IF($row2['requirement_contract_sign']=='1970-01-01' || $row2['requirement_contract_sign']=='0000-00-00' || $row2['requirement_contract_sign']=='' ) {
$reqmem="";
echo"<td>--</td>";
}
else{ 
$reqmem="";
echo"<td >".$row2['requirement_contract_sign']."</td>";
}


IF($row['applicant_status']!=9 ) {
$reqdeployed="";
echo"<td>--</td>";
}
else{
 $reqdeployed="";   
echo"<td >".$deployed1['deployed_date']."</td>";
}



$paidamount = mysql_query("SELECT SUM(amount) AS totalpaidme, email_address.* FROM  email_address 
where  app_id=".$row['applicant_id']."
AND request_payment=3
AND account  IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND currency='PHP'
");
$paidamount1=mysql_fetch_array($paidamount);


$paidamountusd = mysql_query("SELECT SUM(amount) AS totalpaidme, email_address.* FROM  email_address 
where  app_id=".$row['applicant_id']."
AND request_payment=3
AND account  IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND currency='USD'
");
$paidamountusd1=mysql_fetch_array($paidamountusd);




$mybalance=$totalcom-($totaldef1['totaldeduct']+$paidamount1['totalpaidme']+$paidamountusd1['totalpaidme']);


echo "<td>
<select name='applicant_paid1[]'>
<option value='".$row["applicant_paid1"]."'>".$paids."</option>
<option value=1>PAID</option>
<option value=0>unpaid</option>
</SELECT>

</td> ";


echo "<td style='color:black'> ".$totalcom."</td> ";
echo "<td> ".$totaldef1['totaldeduct']."</td> ";
echo "<td style='color:black'> ".$paidamount1['totalpaidme']."</td> ";
echo "<td style='color:red'> ".$mybalance."</td> ";
echo'<td>
<a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">
<button type="button" class="btn btn-'.$srow["statusColors"].' btn-xs" style="font-size:9px">'.$srow["status"].'</button></a></td>';
echo'</tr>';


$mytotalcoms=$totalcom+$mytotalcoms;

}
?>
</tbody>




</table>





