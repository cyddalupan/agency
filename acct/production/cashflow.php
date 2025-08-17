<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<!-- Meta, title, CSS, favicons, etc. -->
      <meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Accounting System</title>
</head>

<body>
<style>
#wrapper{
border:0px solid black;
width:90%;
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

#customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}
</style>

<script>
function exportF(elem) {
  var table = document.getElementById("customers");
  var html = table.outerHTML;
  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
  elem.setAttribute("href", url);
  elem.setAttribute("download", "export.xls"); // Choose the file name
  return false;
}
</script>


<?PHP
include'inc/functions.php';



IF($_POST['emp_id']==99999){
$app = mysql_query("SELECT * FROM email_address6
Where collect_type=1 
and status_collect=0
AND date between '".$_POST['start']."' AND '".$_POST['end']."'
ORDER BY tr_id desc");
}

IF($_POST['emp_id']!=99999){
$app = mysql_query("SELECT * FROM email_address6
Where collect_type=1 
and status_collect=0
AND date between '".$_POST['start']."' AND '".$_POST['end']."'
AND  emp_id=".$_POST['emp_id']."
ORDER BY tr_id desc");
//ECHO $row['emp_id'];
}


$agentcol = mysql_query("SELECT * FROM email_address6
Where collect_type=0 and status_collect=0
AND date between '".$_POST['start']."' AND '".$_POST['end']."'
ORDER BY tr_id desc");




$agent = mysql_query("SELECT * FROM email_address
where account  IN  ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND mineme=0
AND request_payment=3
order by e_id desc");

$office = mysql_query("SELECT * FROM email_address
where account NOT    IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND account_expense=0

AND  date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND mineme=0
AND accountmanager=0
AND request_payment=3
order by e_id desc");


?>
<div id="wrapper">


<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:14px;padding:5px">Export to excel</a>


<table id="customers">
<TR>
<td colspan="6" style="color:black;">Start: <?php echo date('Y-M-d', strtotime($_POST['start'])); ?></td>
<td colspan="6" style="color:black;">End: <?php echo date('Y-M-d', strtotime($_POST['end'])); ?></td>
</TR>


<TR>
<td colspan="12"><h2 style="color:blue;TEXT-align:center">Collection FRA</h2></td>
</TR>

<thead>
<tr>
<th> #</th>
<th>Code #</th>
<th >DATE</th>
<th>Reference Number:</th>
<th>Status</th>

<th>FRA</th>
<th>Account</th>
<th>Description</th>


<th>Deposit Bank</th>
<th>Currency</th>
<th>Amount</th>
<th>Total</th>
</tr>
</thead>



<tbody>

<?php
$count=1;

while($row=mysql_fetch_array($app))
{
//$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
//$row1=mysql_fetch_array($app1);
$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$row['app_id']." ");
$rowp=mysql_fetch_array($applicant);
$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['emp_id']." ");
$emprow=mysql_fetch_array($emp);


$bank = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$row['collect_bank_id']." ");
$bank1=mysql_fetch_array($bank);


if($row["currency"]=="PHP") {$compute=$row["amount"]/54;}
if($row["currency"]=="USD") {$compute=$row["amount"]*1;}

$collect=$compute+$collect;

echo'<tr>';
echo'<td style="font-size:10px">'.$count.'</td>';
echo'<td style="font-size:10px">'.$row["tr_id"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['date']));echo'</td>';
echo'<td>'.$row["or_num"].'</td>';
echo'<td style="font-size:12px;color:white;">
<i style="background:'.$rcolor.';padding:2px;margin:2px">'.$request.'</i>
</td>';

echo'<td  STYLE="FONT-SIZE:14PX;font-weight:bold">'.$emprow["employer_name"].'</td>';
echo'<td>'.$row["account"].'</td>';
echo'<td>'.$row["particular"].'</td>';

echo'<td>'.$bank1["bank_name"].' - Account #: '.$bank1["bank_num"].'</td>';
echo'<td STYLE="FONT-SIZE:14PX;font-weight:bold">'.$row["currency"].'</td>';
echo'<td STYLE="FONT-SIZE:14PX;font-weight:bold">'.$row["amount"].'</td>';
echo'<td STYLE="FONT-SIZE:14PX;font-weight:bold">'.$compute.'</td>';

echo'</tr>';

$count++;
}
$frac = number_format($collect, 2);
?>
</tbody>


<tr>
<td  colspan="11"  style="font-weight:bold;text-align:right;font-size:16px;color:bLUE">Total FRA Collection $ </td>
<td    style="font-weight:bold;text-align:right;font-size:16px;color:bLUE"> <?=$frac;?> </td>
</TR>




<?PHP
IF($_POST['emp_id']==99999){
	
?>


<TR>
<td colspan="10"><h2 style="color:blue;TEXT-align:center">Collection Agent & Applicants</h2></td>
</TR>

<thead>
<tr>
<th> #</th>
<th>Code #</th>
<th >DATE</th>
<th>Applicant</th>
<th>Status</th>

<th>AGENT</th>
<th>Account</th>
<th>Description</th>


<th>Deposit Bank</th>
<th>Currenct</th>
<th>Amount</th>
</tr>
</thead>



<tbody>

<?php
$count=1;

while($agentcol1=mysql_fetch_array($agentcol))
{
$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$agentcol1['agent_id']." ");
$row1=mysql_fetch_array($app1);
$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$agentcol1['app_id']." ");
$rowp=mysql_fetch_array($applicant);
$emp = mysql_query("SELECT * FROM employer where employer_id=".$agentcol1['emp_id']." ");
$emprow=mysql_fetch_array($emp);


$bank = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$agentcol1['collect_bank_id']." ");
$bank1=mysql_fetch_array($bank);

if($agentcol1["currency"]=="USD"){$myamt=$agentcol1["amount"]*50;}
if($agentcol1["currency"]=="PHP"){$myamt=$agentcol1["amount"]*1;}
$collect1=$myamt+$collect1;

echo'<tr>';
echo'<td style="font-size:10px">'.$count.'</td>';
echo'<td style="font-size:10px">'.$agentcol1["tr_id"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($agentcol1['date']));echo'</td>';
echo'<td>
'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td style="font-size:12px;color:white;">
<i style="background:'.$rcolor.';padding:2px;margin:2px">'.$request.'</i>
</td>';

echo'<td style="color:black;font-size:16px;font-weight:bold">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$agentcol1["account"].'</td>';
echo'<td>'.$agentcol1["particular"].'</td>';

echo'<td>'.$bank1["bank_name"].' - Account #: '.$bank1["bank_num"].'</td>';
echo'<td STYLE="FONT-SIZE:14PX;font-weight:bold">'.$agentcol1["currency"].'</td>';
echo'<td STYLE="FONT-SIZE:14PX;font-weight:bold">'.$myamt.'</td>';


echo'</tr>';

$count++;
}
$frac1 = number_format($collect1, 2);
?>
</tbody>


<tr>
<td  colspan="9"  style="font-weight:bold;text-align:right;font-size:16px;color:bLUE">Total Agent Collection PHP  </td>
<td    style="font-weight:bold;text-align:right;font-size:16px;color:bLUE"> <?=$frac1;?> </td>
</TR>

























<tr>
<td  colspan="12"  style="font-weight:bold;text-align:center;font-size:22px;color:white;background:#4682B4">SUMMARY REPORTS </td>
</TR>
<?php

$totfra=($collect*54)+$collect1;
$totalfrac = number_format($totfra, 2);



?>

<tr>
<td  colspan="4"  style="font-weight:bold;text-align:center;font-size:22px;color:black;">Collection  </td>
<td  colspan="8"  style="font-weight:bold;text-align:right;font-size:22px;color:black;"> <?=$totalfrac ?> </td>
</TR>


<?PHP } ?>



</table>
</body>
</html>
