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

<h2 STYLE="COLOR:black;MARGIN-TOP:-12PX;TEXT-ALIGN:left;FONT-SIZE:14PX">COMMISSION EXPENSES
</h2>



<table class="table table-fixed"style="width:90%;PADDING:5PX">
<thead>

<tr>
<th>Date</th>
<th style="text-align:right">Status</th>
<th style="text-align:right"> </th>
<th style="text-align:right">Account Type</th>
<th style="text-align:right">Description</th>
<th style="text-align:right">Currency</th>
<th style="text-align:right">Amount </th>

</tr>

</thead>
<tbody>
<?PHP

 
$liqq = mysql_query("SELECT * FROM  e_liq 
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id

where  liq_app=".$_GET['appid']."  AND l_type  IN ('Agent')
AND mineme=1
AND request_payment=3  
and l_status=0"); 
 
while($liqrow=mysql_fetch_array($liqq))
{
	
if($liqrow["mineme"]==0) {$mysource="Main";}	
if($liqrow["mineme"]!=0) {$mysource="Account Manager";}	

	
$statys = mysql_query("SELECT * FROM  email_address where e_id=".$liqrow['l_e_d']." ");
$statys1=mysql_fetch_array($statys);		
$liqcharge=$liqrow['l_amount']+$liqcharge;  	

if($statys1["request_payment"]==0){$request='Pending';$rcolor='black';$disme='';}
if($statys1["request_payment"]==1){$request='Approved';$rcolor='blue';$disme='';}
if($statys1["request_payment"]==2){$request='For Release';$rcolor='orange';$disme='';}
if($statys1["request_payment"]==3){$request='Released';$rcolor='green';$disme='disabled';}
if($statys1["request_payment"]==4){$request='Decline';$rcolor='red';$disme='';}
if($statys1["request_payment"]==5){$request='Return to maker';$rcolor='red';$disme='';}	
	
echo'<tr>';

echo'<td>'.$liqrow["l_date"].'</td>';
echo'<td style="font-size:12px;color:white;">
<i style="background:'.$rcolor.';padding:2px;margin:5px">'.$request.'</i>
</td>';
echo'<td>'.$mysource.'</td>';
echo'<td>'.$liqrow["type_applicant"].'</td>';
echo'<td>'.$liqrow["l_remarks"].'</td>';
echo'<td style="text-align:right">'.$liqrow["l_currency"].'</td>';
echo'<td style="text-align:right">'.$liqrow["l_amount"].'</td>';

echo'</tr>';
}
?>		

<tr>
<td style="FONT-WEIGHT:bold;text-align:right;font-size:16px" colspan="7">TOTAL: <?php echo number_format($liqcharge,2);?></td>
</tr>

</tbody>
</table>


</br></br>












	

</br>




</div>