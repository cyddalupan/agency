<?php include'db.php';
include'styles.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

</head>


<div style="width:90%;margin:20px">
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

<?php $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>


</br>
<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:16px;padding:5px;font-weight:bold">EXPORT EXCEL</a>



<table  cellspacing="0" id="example-checkbox" STYLE="BACKGROUND:WHITE;margin:30px">
<thead>
<tr>
<th>Visa #</th>

<th>DATE</th>
<th>Name</th>
<th>Position</th>
<th>Country</th>
<th>Religion</th>
<th>Airport</th>
<th>NOTI</th>
<th>Agent</th>
<th>FRA</th>
<th>Status</th>

<th style="COLOR:BLACK">TOTAL COMM</th>

</tr>
</thead>
<tbody>
<?php
$count=1;	
	$app = mysql_query("SELECT applicant.applicant_id
	,applicant.applicant_first
	,applicant.applicant_last
	,applicant.applicant_employer
	,applicant.applicant_source
	,employer.employer_id
	,employer.employer_name
	,applicant.applicant_status
	,applicant.applicant_preferred_position
	,statuses.*
	,recruitment_agent.*
	,applicant.applicant_date_applied
	,applicant.applicant_preferred_country
	,applicant.applicant_remarks
	,applicant.applicant_employer
	,applicant.applicant_religion
	,applicant.applicantNumber
	
	FROM applicant
	LEFT JOIN  statuses
	ON applicant.applicant_status = statuses.number



	LEFT JOIN  	employer
	ON applicant.applicant_employer =employer.employer_id

	LEFT JOIN  	recruitment_agent
	ON applicant.applicant_source =recruitment_agent.agent_id
	where 
	 applicant.applicant_employer=".$_GET['emp_id']."

	");








while($row=mysql_fetch_array($app))
{


$status = mysql_query("SELECT * FROM statuses where number=".$row['applicant_status']." ");
$srow=mysql_fetch_array($status);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$row1=mysql_fetch_array($app1);



$cert = mysql_query("SELECT certificate_applicant,certificate_medical_result FROM applicant_certificate where certificate_applicant=".$row['applicant_id']." ");
$cert1=mysql_fetch_array($cert);


$req = mysql_query("SELECT requirement_applicant,requirement_ticket FROM applicant_requirement where requirement_applicant=".$row['applicant_id']." ");
$req1=mysql_fetch_array($req);

$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);



$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);



$app2 = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id

LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id

where  liq_app=".$row['applicant_id']."  AND   l_type NOT IN ('Agent')
and l_status=0");
$php=mysql_fetch_array($app2);



////FRA VISA
$fravisa = mysql_query("SELECT sum(fra_ftw) as totalamount,applicant_id
,fra_ftw,fra_visa,fra_deployed,fra_sent,fra_before FROM  applicant 
where  applicant_id=".$row['applicant_id']." ");
$fravisa1=mysql_fetch_array($fravisa);

////FRA CONTRACT
$fracontract = mysql_query("SELECT sum(fra_visa) as totalamount,applicant_id
,fra_ftw,fra_visa,fra_deployed,fra_sent,fra_before FROM  applicant 
where  applicant_id=".$row['applicant_id']." ");
$fracontract1=mysql_fetch_array($fracontract);

////FRA DEPLOYED
$fradeployed = mysql_query("SELECT sum(fra_deployed) as totalamount,applicant_id
,fra_ftw,fra_visa,fra_deployed,fra_sent,fra_before FROM  applicant 
where  applicant_id=".$row['applicant_id']." ");
$fradeployed1=mysql_fetch_array($fradeployed);

////FRA ALL
$collectapp = mysql_query("SELECT sum(fra_ftw+fra_visa+fra_deployed+fra_sent+fra_before) as totalamount,applicant_id
,fra_ftw,fra_visa,fra_deployed,fra_sent,fra_before FROM  applicant 
where  applicant_id=".$row['applicant_id']." ");
$collectapp1=mysql_fetch_array($collectapp);


$received = mysql_query("SELECT sum(fra_amount) as totalamount FROM  liq_fra 
where  app_id=".$row['applicant_id']." ");
$received1=mysql_fetch_array($received);


//agent

$agentexpense = mysql_query("SELECT sum(l_amount) as totalamount FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where  liq_app=".$row['applicant_id']."  
AND l_type  IN ('Agent')
AND request_payment=3  
AND mineme=0 
and l_status=0");
$agentexpense1=mysql_fetch_array($agentexpense);

$received = mysql_query("SELECT sum(fra_amount) as totalamount FROM  liq_fra 
where  app_id=".$row['applicant_id']." ");
$received1=mysql_fetch_array($received);


$fraexpense=$totlexpenseper;
$fratotal=	$fravisa1['totalamount'] + $fracontract1["totalamount"]	+ $fradeployed1["totalamount"];
$frabalance=$fratotal	-	$received1["totalamount"];	



								
							
								
								
echo'<tr>';

echo'<td>'.$row["applicantNumber"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';
echo'<td style="color:black;font-size:14px"><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';
echo"<td>".$position1['position_name']."</td>";
echo'<td>'.$countryrow["country_name"].'</td>';
echo'<td>'.$row["applicant_religion"].'</td>';
echo'<td>'.$req1["requirement_ticket"].'</td>';
echo'<td></td>';
echo'<td style="font-size:12px">'.$row["agent_first"].' '.$row["agent_last"].'</td>';

echo'<td>'.$row["employer_name"].'</td>';

echo'<td><button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:12px">'.$row["status"].'</button></a>';




echo'<td style="color:BLACK;font-weight:BOLD;FONT-SIZE:14PX;background:#FFD700">'.number_format($collectapp1["totalamount"],2).'</td>';



echo'</tr>';
$count++;
$fravisaall=$fravisa1["totalamount"]+$fravisaall;
$fracontractall=$fracontract1["totalamount"]+$fracontractall;
$fradeployedall=$fradeployed1["totalamount"]+$fradeployedall;
$FRATOTAL=$collectapp1["totalamount"]+$FRATOTAL;
$agencyreceived=$received1["totalamount"]+$agencyreceived;


$agencybalance=$FRATOTAL-$agencyreceived;
}
?>
</tbody>

<tr>
<td	colspan="11" style="text-align:right;font-size:22px;color:black;background:#F5F5F5">TOTAL COMM: </td>
<td	colspan="1" style="text-align:right;font-size:22px;color:black;background:#F5F5F5">$<?php echo''.number_format($FRATOTAL,2).'';?></td>
<tr>



<tr>
<td	colspan="12" style="text-align:LEFT;font-size:18px;color:ORANGE"><h2> RECEIVED (FOR HIM)</h2></td>
<tr>

<tr>
<th>Code #</th>
<th>DATE</th>
<th>Reference Number:</th>
<th>Applicant</th>
<th>Account</th>
<th colspan="5">Description</th>
<th colspan="2">Amount</th>
</tr>
</thead>
<tbody>

<?php	
//AND collect_status=1
$collected = mysql_query("SELECT * FROM email_address6
Where collect_type=1 and status_collect=0 AND currency='USD'
AND collect_status=1
and emp_id=".$_GET['emp_id']."
ORDER BY tr_id desc");
while($collected1=mysql_fetch_array($collected))
{
//$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
//$row1=mysql_fetch_array($app1);
$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$collected1['app_id']." ");
$rowp=mysql_fetch_array($applicant);

$totalreceived=$collected1["amount"]+$totalreceived;

if($collected1["collect_status"]==0){$request='Pending';$rcolor='black';}
if($collected1["collect_status"]==1){$request='Approved';$rcolor='green';}
if($collected1["collect_status"]==2){$request='Return to maker';$rcolor='red';}

echo'<tr>';
echo'<td>'.$collected1["tr_id"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($collected1['date']));echo'</td>';
echo'<td>'.$collected1["or_num"].'</td>';
echo'<td>
'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$collected1["account"].'</td>';
echo'<td colspan="5">'.$collected1["particular"].'</td>';
echo'<td colspan="2"  style="text-align:right;font-size:22px;color:black;background:#00FF7F"> '.$collected1["amount"].'</td>';
echo'</tr>';
}
?>
<tr>
<td colspan="11" style="text-align:right;font-size:22px;color:black;background:#F5F5F5">TOTAL REVEIVED:</td>
<td    style="text-align:right;font-size:22px;color:black;background:#F5F5F5">$ <?php echo' '.number_format($totalreceived, 2).''; ?></td>
</tr>

<?PHP
$myallbalance=$FRATOTAL-$totalreceived;
?>
<tr>
<td colspan="12" style="text-align:right;font-size:22px;color:black;background:#F5F5F5"></br></br></td>
</tr>
<tr>
<td colspan="11" style="text-align:right;font-size:22px;color:black;background:black;color:white">BALANCE:</td>
<td    style="text-align:right;font-size:22px;color:black;background:black;color:white">$ <?php echo' '.number_format($myallbalance, 2).''; ?></td>
</tr>

</table>






<div class="clearfix"></div>

</div>




