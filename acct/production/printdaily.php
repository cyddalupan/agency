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


<?PHP
include'inc/functions.php';



IF($_POST['account']==1){

$app = mysql_query("SELECT email_address.*
,applicant.applicant_first
,applicant.applicant_middle
,applicant.applicant_last
FROM email_address
LEFT JOIN  	applicant
ON email_address.app_id = applicant.applicant_id

where 
email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.request_payment=".$_POST['request']."
AND email_address.mineme=0
AND email_address.account  IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')");
}


IF($_POST['account']==2){

$app = mysql_query("SELECT email_address.*
,applicant.applicant_first
,applicant.applicant_middle
,applicant.applicant_last
FROM email_address
LEFT JOIN  	applicant
ON email_address.app_id = applicant.applicant_id


where 
email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.request_payment=".$_POST['request']."
AND email_address.mineme=0
AND email_address.account  NOT IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT','PERSONAL HOUSE RENUVATION','PERSONAL COMMUTER','PERSONAL -KERWIN')
order by date_ad desc");

}



IF($_POST['account']==4){

$app = mysql_query("SELECT email_address.*
,applicant.applicant_first
,applicant.applicant_middle
,applicant.applicant_last
FROM email_address
LEFT JOIN  	applicant
ON email_address.app_id = applicant.applicant_id


where 
email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.request_payment=".$_POST['request']."
AND email_address.mineme=0
AND email_address.account   IN ('PERSONAL HOUSE RENUVATION','PERSONAL COMMUTER','PERSONAL -KERWIN')
order by date_ad desc");

}

IF($_POST['account']==3){

$app = mysql_query("SELECT email_address.*
,applicant.applicant_first
,applicant.applicant_middle
,applicant.applicant_last
FROM email_address
LEFT JOIN  	applicant
ON email_address.app_id = applicant.applicant_id


where 
email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.request_payment=".$_POST['request']."
AND email_address.mineme=0
order by date_ad desc");

}




?>
<div id="wrapper">
<h1>Expenses AS OF </h1>
<div id="clear"></div>
<p style="color:BLACK;margin-top:-10px;text-align:center;font-size:14px">Start: <?php echo date('Y-M-d', strtotime($_POST['start'])); ?>  
</p>
<div id="clear"></div>
<p style="color:BLACK;margin-top:-18px;text-align:center;font-size:14px">End: <?php echo date('Y-M-d', strtotime($_POST['end'])); ?></p>
<div id="clear"></div>











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



</br>
<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:14px;padding:5px">Export to excel</a>


<table id="customers">
<thead>
<tr>
<th STYLE="">#</th>
<th style="text-align:right">Request</th>
<th>Ref#</th>
<th>DATE</th>
<th>PAYEE</th>
<th>AGENT</th>
<th>APPLICANT</th>
<th>ACCOUNT</th>
<th>C/O Staff</th>
<th >PARTICULAR</th>
<th>CURRENCY</th>
<th STYLE="width:5%">AMOUNT</th>
<th>TOTAL ₱</th>
</tr>
</thead>
<tbody>
<?php	
$counter1 = 1; 
while($row=mysql_fetch_array($app))
{
	
	$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);

$app2 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row2=mysql_fetch_array($app2);
$app3 = mysql_query("SELECT * FROM applicant where applicant_id=".$row['app_id']." ");
$row3=mysql_fetch_array($app3);

$USER = mysql_query("SELECT * FROM  user where user_id=".$row['staff_id']." ");
$rowu=mysql_fetch_array($USER);

if($row["request_payment"]==0){$request='For Approval';$rcolor='black';}
if($row["request_payment"]==1){$request='Approved';$rcolor='blue';}
if($row["request_payment"]==2){$request='For Release';$rcolor='orange';}
if($row["request_payment"]==3){$request='Released';$rcolor='green';}
if($row["request_payment"]==4){$request='Decline';$rcolor='red';}
if($row["request_payment"]==5){$request='Return to maker';$rcolor='red';}



if($row["currency"]=="USD"){$total_reim=$row['amount']*50;}
if($row["currency"]=="PHP"){$total_reim=$row['amount'];}
$mytotal=$total_reim+$mytotal;

$totalamountPHP=$total_reim+$totalamountPHP;

echo'<tr>';
echo'<td style="font-weight:bold;font-size:14px">'.$counter1.'</td>';
echo'<td>'.$row["ref"].'</td>';
echo'<td style="font-size:12px;color:white;">
<i style="background:'.$rcolor.';padding:2px;margin:2px">'.$request.'</i>
</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['date_ad']));echo'</td>';

echo'<td>'.$row2["agent_first"].' '.$row2["agent_last"].'</td>';
echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row["applicant_first"].' '.$row["applicant_last"].'</td>';

echo'<td>'.$row["account"].'</td>';
echo'<td STYLE="color:green"><a href="view_liq.php?e_id='.$row["e_id"].' && name='.$rowu["user_fullname"].'" target="_blank" ">'.$rowu["user_fullname"].'</td>';
echo'<td>'.$row["description"].'</td>';
echo'<td>'.$row["currency"].'</td>';
echo'<td> '.number_format($row['amount'], 0).'</td>';
echo'<td  style="font-size:18px;font-weight:bold"> '.number_format($total_reim, 0).'</td>';
echo'</tr>';
$counter1++;
}
$php = number_format($totalamountPHP, 2);
?>

<tr>
<td  colspan="12"  style="font-weight:bold;text-align:right;font-size:18px;color:black">TOTAL <?PHP ECHO $_POST['currency'];?></td>
<td  style="font-weight:bold;text-align:right;font-size:18px;color:black"> <?=$php;?> </td>
</TR>



</tbody>


</table>











</body>
</html>
