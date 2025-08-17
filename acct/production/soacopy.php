<?php
session_start();
include'inc/functions.php';
include'tracking.php';
$app = mysql_query("SELECT * FROM applicant where applicant_id=".$_GET['appid']." ");
$row=mysql_fetch_array($app);




$CERT = mysql_query("SELECT * FROM applicant_certificate where certificate_applicant=".$_GET['appid']." ");
$certrow=mysql_fetch_array($CERT);

$REQ = mysql_query("SELECT * FROM applicant_requirement where requirement_applicant=".$_GET['appid']." ");
$reqrow=mysql_fetch_array($REQ);
$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$row1=mysql_fetch_array($app1);


?>
<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
</head>


<style>
table, td, th {
    border: 1px solid black;
	text-align:left;
	font-size:12px;
	PADDING:1PX;
	
}

table {
    border-collapse: collapse;
    width: 100%;
}

th {
    height: 30px;
}
</style>

<div style="width:700px;padding:20px;border:1px solid black;margin:0 auto;font-family:calibri;">
<?php include'title2.php';?>

<table>
<tr>
<td><b>Date:</b> <?=$date2;?></td>
<td><b>Name:</b> <?=$row['applicant_first']?> <?=$row['applicant_last']?></td>
<td><b>Agent: </b><?=$row1['agent_first']?> <?=$row1['agent_last']?></td>
</tr>
</table>
</br></br>

<h2 STYLE="COLOR:black;MARGIN-TOP:-12PX;TEXT-ALIGN:CENTER;FONT-SIZE:16PX">
Statement Of Account
</h2>

<h2 STYLE="COLOR:black;MARGIN-TOP:-12PX;TEXT-ALIGN:left;FONT-SIZE:14PX">APPLICANT EXPENSES
</h2>



<table class="table table-fixed"style="width:90%;PADDING:5PX">
<thead>
<tr>
<th>Status</th>
<th>Date</th>
<th style="text-align:right">Account Type</th>
<th style="text-align:right">Description</th>
<th style="text-align:right">Currency</th>
<th style="text-align:right">Amount </th>
<th style="text-align:right">Total </th>
</tr>

</thead>
<tbody>
<?PHP

 
$liqq = mysql_query("SELECT * FROM  email_address 

where    account NOT IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND request_payment!=4
AND  app_id=".$_GET['appid']." ");  
 
while($liqrow=mysql_fetch_array($liqq))
{

	if($comn1["request_payment"]==0){$request='Pending';$rcolor='black';$disme='';}
if($liqrow["request_payment"]==1){$request='Approved';$rcolor='blue';$disme='';}
if($liqrow["request_payment"]==2){$request='For Release';$rcolor='orange';$disme='';}
if($liqrow["request_payment"]==3){$request='Released';$rcolor='green';$disme='disabled';}
if($liqrow["request_payment"]==4){$request='Decline';$rcolor='red';$disme='';}
if($liqrow["request_payment"]==5){$request='Return to maker';$rcolor='red';$disme='';}

if($liqrow["currency"]=="USD"){$mytotal=$liqrow["amount"]*50;}	
if($liqrow["currency"]=="PHP"){$mytotal=$liqrow["amount"];}	
$liqcharge=$mytotal+$liqcharge;  	
	

echo'<tr>';


echo'<td style="font-size:12px;color:white;">
<i style="background:'.$rcolor.';padding:2px;margin:5px">'.$request.'</i>
</td>';

echo'<td>'.$liqrow["date_ad"].'</td>';
echo'<td>'.$liqrow["account"].'</td>';
echo'<td>'.$liqrow["description"].'</td>';
echo'<td style="text-align:right">'.$liqrow["currency"].'</td>';
echo'<td style="text-align:right">'.$liqrow["amount"].'</td>';
echo'<td style="text-align:right">'.$mytotal.'</td>';
echo'</tr>';
}
?>		

<tr>
<td style="FONT-WEIGHT:bold;text-align:right;font-size:16px" colspan="7">TOTAL: <?php echo number_format($liqcharge,2);?></td>
</tr>

</tbody>
</table>


</br></br>









<h2 STYLE="COLOR:black;MARGIN-TOP:-12PX;TEXT-ALIGN:left;FONT-SIZE:14PX">COMMISSION EXPENSES
</h2>



<table class="table table-fixed"style="width:90%;PADDING:5PX">
<thead>
<tr>
<th>Date</th>
<th style="text-align:right">Status</th>

<th style="text-align:right">Account Type</th>
<th style="text-align:right">Description</th>
<th style="text-align:right">Currency</th>
<th style="text-align:right">Amount </th>
</tr>

</thead>
<tbody>
<?PHP

 
$comn = mysql_query("SELECT * FROM  email_address 

where    account  IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND request_payment!=4
AND  app_id=".$_GET['appid']."
"); 
 
while($comn1=mysql_fetch_array($comn))
{
$statys = mysql_query("SELECT * FROM  email_address where e_id=".$comn1['l_e_d']." ");
$statys1=mysql_fetch_array($statys);	
$liqchargecomn1=$comn1['amount']+$liqchargecomn1;  	


if($comn1["request_payment"]==0){$request='Pending';$rcolor='black';$disme='';}
if($comn1["request_payment"]==1){$request='Approved';$rcolor='blue';$disme='';}
if($comn1["request_payment"]==2){$request='For Release';$rcolor='orange';$disme='';}
if($comn1["request_payment"]==3){$request='Released';$rcolor='green';$disme='disabled';}
if($comn1["request_payment"]==4){$request='Decline';$rcolor='red';$disme='';}
if($comn1["request_payment"]==5){$request='Return to maker';$rcolor='red';$disme='';}	
echo'<tr>';

echo'<td>'.$comn1["date_ad"].'</td>';
echo'<td style="font-size:12px;color:white;">
<i style="background:'.$rcolor.';padding:2px;margin:5px">'.$request.'</i>
</td>';

echo'<td>'.$comn1["account"].'</td>';
echo'<td>'.$comn1["description"].'</td>';
echo'<td style="text-align:right">PHP</td>';
echo'<td style="text-align:right">'.$comn1["amount"].'</td>';

echo'</tr>';
}
?>		

<tr>
<td style="FONT-WEIGHT:bold;text-align:right;font-size:16px" colspan="7">TOTAL: <?php echo number_format($liqchargecomn1,2);?></td>
</tr>

</tbody>
</table>























	

</br>




</div>