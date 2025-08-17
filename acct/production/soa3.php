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
	font-size:14px;
	PADDING:3PX;

}

table {
    border-collapse: collapse;
    width: 100%;
}

th {
    height: 30px;
}
</style>

<div style="width:1100px;padding:20px;border:1px solid black;margin:0 auto;font-family:calibri;">


<h2 STYLE="MARGIN-TOP:-10PX;TEXT-ALIGN:CENTER;FONT-SIZE:48PX"><img src="pjvbanner.png"> </h2>


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



</br>
<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:14px;padding:5px">Save my work</a>

<?php
IF($_POST['type_applicant']==2){ $ftwme="AND e_liq.type_applicant  IN ('FTW 1','FTW 2','FTW 3')"; $title="FTW";}
IF($_POST['type_applicant']==3){ $ftwme="AND e_liq.type_applicant  IN ('SIGN CONTRACT')"; $title="SIGN CONTRACT";}
IF($_POST['type_applicant']==4){ $ftwme="AND e_liq.type_applicant  IN ('DEPLOYED 1','DEPLOYED 2','DEPLOYED 3')";  $title="DEPLOYED"; }
IF($_POST['type_applicant']==5){ $ftwme="AND e_liq.type_applicant  IN ('BONUS')"; $title="BONUS";}
IF($_POST['type_applicant']==0){ $ftwme="	"; $title="ALL";}
		

?>

<table class="table table-fixed"style="width:99%;PADDING:5PX" id="example-checkbox">
<thead>

<tr  style="border:none;text-align:center">
<td colspan="10" style="border:none;text-align:center"> <h2><?=$title?>  EXPENSES </h2> </td>
</tr>
<tr>
<td colspan="11"> Date From:  <b><?php echo date('Y-M-d', strtotime($_POST['start'])); ?></b>    <br>   
 Date To: &nbsp;&nbsp;&nbsp;&nbsp; <b><?php echo date('Y-M-d', strtotime($_POST['end'])); ?> </b> </td>
</tr>


<tr>
<th> </th>
<th style="width:100px">Date </th>
<th style="text-align:center">Ref #</th>
<th style="text-align:center">Status</th>
<th style="text-align:center;width:180px">Applicant</th>
<th style="text-align:center">Agent</th>
<th style="text-align:center">Country</th>
<th style="text-align:center">Account Type</th>
<th style="text-align:center">Description</th>
<th style="text-align:center">Currency</th>
<th style="text-align:center">Amount </th>
</tr>

</thead>
<tbody>
<?PHP


$counter=1;
IF($_POST['account']==1){

$liqq = mysql_query("SELECT * FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
where l_status=0
AND date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND currency='".$_POST['currency']."'
AND request_payment=".$_POST['request']."
AND email_address.account  IN ('Commission','Cash Advance agent')
$ftwme

order by liq_app,date_ad desc ");

$countme = mysql_query("SELECT count(DISTINCT liq_app)as totalme,email_address.*,e_liq.* FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
where l_status=0
AND date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND currency='".$_POST['currency']."'
AND request_payment=".$_POST['request']."
AND email_address.account  IN ('Commission','Cash Advance agent')
$ftwme ");
$countme1=mysql_fetch_array($countme);
}






while($liqrow=mysql_fetch_array($liqq))
{
$statys = mysql_query("SELECT * FROM  email_address where e_id=".$liqrow['l_e_d']." ");
$statys1=mysql_fetch_array($statys);		
$liqcharge=$liqrow['l_amount']+$liqcharge;  	
$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$liqrow['liq_app']." ");
$rowp=mysql_fetch_array($applicant);


$country = mysql_query("SELECT * FROM country where country_id=".$rowp['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$liqrow['agent_id']." ");
$row1=mysql_fetch_array($app1);



if($statys1["request_payment"]==0){$request='Pending';$rcolor='black';$disme='';}
if($statys1["request_payment"]==1){$request='Approved';$rcolor='blue';$disme='';}
if($statys1["request_payment"]==2){$request='For Release';$rcolor='orange';$disme='';}
if($statys1["request_payment"]==3){$request='Released';$rcolor='green';$disme='disabled';}
if($statys1["request_payment"]==4){$request='Decline';$rcolor='red';$disme='';}
if($statys1["request_payment"]==5){$request='Return to maker';$rcolor='red';$disme='';}	
	
echo'<tr>';
echo'<td></td>';
echo'<td>'; echo date('M-d-Y', strtotime($liqrow['l_date']));echo'</td>';
echo'<td>000'.$liqrow["l_e_d"].'</td>';
echo'<td style="font-size:12px;color:white;">
<i>'.$request.'</i>
</td>';
echo'<td style="font-size:16px;font-weight:bold">'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td font-size:16px;font-weight:bold>'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';
echo'<td>'.$liqrow["type_applicant"].'</td>';
echo'<td>'.$liqrow["l_remarks"].'</td>';
echo'<td style="text-align:center">$</td>';
echo'<td style="text-align:center;font-weight:bold" font-size:16px;font-weight:bold">'.$liqrow["l_amount"].'</td>';

echo'</tr>';
$counter++;
}
?>		
<tr>
<td style="FONT-WEIGHT:bold;text-align:LEFT;font-size:16px;background:lightblue" colspan="11">TOTAL APPLICANTS: <?=$countme1['totalme']?> </td>
</tr>
<tr>
<td style="FONT-WEIGHT:bold;text-align:right;font-size:22px;background:lightblue" colspan="11">GRAND TOTAL AMOUNT : &nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo number_format($liqcharge,2);?></td>
</tr>


<?php

$ksaftw = mysql_query("SELECT 
count(liq_app) as alls
,e_liq.*
,email_address.*
FROM e_liq
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where e_liq.l_status=0
AND e_liq.liq_app!=0
AND email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.currency='".$_POST['currency']."'
AND email_address.request_payment=".$_POST['request']."
AND e_liq.type_applicant  IN ('FTW 1','FTW 2','FTW 3')
AND applicant_preferred_country not IN (5,11)");
$ksaftw1 = mysql_fetch_array($ksaftw);


$ksacs = mysql_query("SELECT 
count(liq_app) as alls
,e_liq.*
,email_address.*
FROM e_liq
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where e_liq.l_status=0
AND e_liq.liq_app!=0
AND email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.currency='".$_POST['currency']."'
AND email_address.request_payment=".$_POST['request']."
AND e_liq.type_applicant  IN ('SIGN CONTRACT')
AND applicant_preferred_country not IN (5,11)");
$ksacs1 = mysql_fetch_array($ksacs);

$ksadep = mysql_query("SELECT 
count(liq_app) as alls
,e_liq.*
,email_address.*
FROM e_liq
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where e_liq.l_status=0
AND e_liq.liq_app!=0
AND email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.currency='".$_POST['currency']."'
AND email_address.request_payment=".$_POST['request']."
AND e_liq.type_applicant  IN ('DEPLOYED 1','DEPLOYED 2','DEPLOYED 3s')
AND applicant_preferred_country not IN (5,11)");
$ksadep1 = mysql_fetch_array($ksadep);



$qarftw = mysql_query("SELECT 
count(liq_app) as alls
,e_liq.*
,email_address.*
FROM e_liq
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where e_liq.l_status=0
AND e_liq.liq_app!=0
AND email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.currency='".$_POST['currency']."'
AND email_address.request_payment=".$_POST['request']."
AND e_liq.type_applicant  IN ('FTW 1','FTW 2','FTW 3')
AND applicant_preferred_country  IN (11)");
$qarftw1 = mysql_fetch_array($qarftw);

$qarcs = mysql_query("SELECT 
count(liq_app) as alls
,e_liq.*
,email_address.*
FROM e_liq
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where e_liq.l_status=0
AND e_liq.liq_app!=0
AND email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.currency='".$_POST['currency']."'
AND email_address.request_payment=".$_POST['request']."
AND e_liq.type_applicant  IN ('SIGN CONTRACT')
AND applicant_preferred_country  IN (11)");
$qarcs1 = mysql_fetch_array($qarcs);



$kuwftw = mysql_query("SELECT 
count(liq_app) as alls
,e_liq.*
,email_address.*
FROM e_liq
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where e_liq.l_status=0
AND e_liq.liq_app!=0
AND email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.currency='".$_POST['currency']."'
AND email_address.request_payment=".$_POST['request']."
AND e_liq.type_applicant  IN ('FTW 1','FTW 2','FTW 3')
AND applicant_preferred_country  IN (5)");
$kuwftw1 = mysql_fetch_array($kuwftw);


$kuwcs = mysql_query("SELECT 
count(liq_app) as alls
,e_liq.*
,email_address.*
FROM e_liq
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where e_liq.l_status=0
AND e_liq.liq_app!=0
AND email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.currency='".$_POST['currency']."'
AND email_address.request_payment=".$_POST['request']."
AND e_liq.type_applicant  IN ('SIGN CONTRACT')
AND applicant_preferred_country  IN (5)");
$kuwcs1 = mysql_fetch_array($kuwcs);


$kuwdep = mysql_query("SELECT 
count(liq_app) as alls
,e_liq.*
,email_address.*
FROM e_liq
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where e_liq.l_status=0
AND e_liq.liq_app!=0
AND email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.currency='".$_POST['currency']."'
AND email_address.request_payment=".$_POST['request']."
AND e_liq.type_applicant  IN ('DEPLOYED 1','DEPLOYED 2','DEPLOYED 3')
AND applicant_preferred_country  IN (5)");
$kuwdep1 = mysql_fetch_array($kuwdep);


$qardep = mysql_query("SELECT 
count(liq_app) as alls
,e_liq.*
,email_address.*
FROM e_liq
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where e_liq.l_status=0
AND e_liq.liq_app!=0
AND email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.currency='".$_POST['currency']."'
AND email_address.request_payment=".$_POST['request']."
AND e_liq.type_applicant  IN ('DEPLOYED 1','DEPLOYED 2','DEPLOYED 3')
AND applicant_preferred_country  IN (11)");
$qardep1 = mysql_fetch_array($qardep);

$cashadvance = mysql_query("SELECT 
count(liq_app) as alls
,e_liq.*
,email_address.*
FROM e_liq
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where e_liq.l_status=0
AND e_liq.liq_app=0
AND email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.currency='".$_POST['currency']."'
AND email_address.request_payment=".$_POST['request']."
AND e_liq.type_applicant  IN ('CASH ADVANCE')
AND e_liq.l_type  IN ('Agent')
");
$cashadvance1 = mysql_fetch_array($cashadvance);

// compute ftw
$ftwamt = mysql_query("SELECT 
SUM(l_amount) as alls
,e_liq.*
,email_address.*
FROM e_liq
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where e_liq.l_status=0
AND e_liq.liq_app!=0
AND email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.currency='".$_POST['currency']."'
AND email_address.request_payment=".$_POST['request']."
AND e_liq.type_applicant  IN ('FTW 1','FTW 2','FTW 3')");
$ftwamt1 = mysql_fetch_array($ftwamt);



// compute cs
$cswamt = mysql_query("SELECT 
SUM(l_amount) as alls
,e_liq.*
,email_address.*
FROM e_liq
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where e_liq.l_status=0
AND e_liq.liq_app!=0
AND email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.currency='".$_POST['currency']."'
AND email_address.request_payment=".$_POST['request']."
AND e_liq.type_applicant  IN ('SIGN CONTRACT')");
$csamt1 = mysql_fetch_array($cswamt);

// compute cs
$depamt = mysql_query("SELECT 
SUM(l_amount) as alls
,e_liq.*
,email_address.*
FROM e_liq
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where e_liq.l_status=0
AND e_liq.liq_app!=0
AND email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.currency='".$_POST['currency']."'
AND email_address.request_payment=".$_POST['request']."
AND e_liq.type_applicant  IN ('DEPLOYED 1','DEPLOYED 2','DEPLOYED 3')");
$depamt1 = mysql_fetch_array($depamt);


// compute ca

$caamt = mysql_query("SELECT 
SUM(l_amount)  as alls
,e_liq.*
,email_address.*
FROM e_liq
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
LEFT JOIN applicant
ON e_liq.liq_app = applicant.applicant_id
where e_liq.l_status=0
AND e_liq.liq_app=0
AND email_address.date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND email_address.currency='".$_POST['currency']."'
AND email_address.request_payment=".$_POST['request']."
AND e_liq.type_applicant  IN ('CASH ADVANCE')
AND e_liq.l_type  IN ('Agent')
");
$caamt1 = mysql_fetch_array($caamt);
?>




<tr>
<td COLSPAN="6"></td>
<td style="text-align:center;font-weight:bold;font-size:16px">KSA</td>
<td  style="text-align:center;font-weight:bold;font-size:16px">KUWAIT</td>
<td  style="text-align:center;font-weight:bold;font-size:16px">QATAR</td>
<td  style="text-align:center;font-weight:bold;font-size:16px">C/A</td>
<td  style="text-align:center;font-weight:bold;font-size:16px">AMOUNT</td>
</tr>

<tr>
<td COLSPAN="6" style="font-weight:bold;font-size:16px">FTW</td>
<td style="text-align:center;font-weight:bold;font-size:16px"><?=$ksaftw1['alls']?></td>
<td  style="text-align:center;font-weight:bold;font-size:16px"><?=$kuwftw1['alls']?></td>
<td  style="text-align:center;font-weight:bold;font-size:16px"><?=$qarftw1['alls']?></td>
<td style="text-align:center;font-weight:bold;font-size:16px"></td>
<td style="text-align:center;font-size:16px;font-weight:bold"><?php echo' '.number_format($ftwamt1['alls'], 2).''; ?></td>
</tr>

<tr>
<td COLSPAN="6"  style="font-weight:bold;font-size:16px">CS</td>
<td style="text-align:center;font-weight:bold;font-size:16px"><?=$ksacs1['alls']?></td>
<td  style="text-align:center;font-weight:bold;font-size:16px"><?=$kuwcs1['alls']?></td>
<td  style="text-align:center;font-weight:bold;font-size:16px"><?=$qarcs1['alls']?></td>
<td style="text-align:center;font-weight:bold;font-size:16px"></td>
<td style="text-align:center;font-weight:bold;font-size:16px"><?php echo' '.number_format($csamt1['alls'], 2).''; ?></td>
</tr>


<tr>
<td COLSPAN="6"  style="font-weight:bold;font-size:16px">DEPLOYED</td>
<td style="text-align:center;font-weight:bold;font-size:16px"><?=$ksadep1['alls']?></td>
<td style="text-align:center;font-weight:bold;font-size:16px"><?=$kuwdep1['alls']?></td>
<td style="text-align:center;font-weight:bold;font-size:16px"><?=$qardep1['alls']?></td>
<td style="text-align:center;font-weight:bold;font-size:16px"></td>
<td style="text-align:center;font-weight:bold;font-size:16px"><?php echo' '.number_format($depamt1['alls'], 2).''; ?></td>
</tr>

<tr>
<td COLSPAN="6"  style="font-weight:bold;font-size:16px">CASH ADVANCE</td>
<td></td>
<td></td>
<td></td>
<td style="text-align:centerfont-weight:bold;font-size:16px"><?=$cashadvance1['alls']?></td>
<td style="text-align:center;font-weight:bold;font-size:16px"><?php echo' '.number_format($caamt1['alls'], 2).''; ?></td>

</tr>

<?php
$totalko=$caamt1['alls']+$depamt1['alls']+$csamt1['alls']+$ftwamt1['alls'];
?>

<tr>
<td COLSPAN="10"  style="font-weight:bold;TEXT-align:right;font-size:26px">TOTAL</td>

<td style="text-align:center;font-size:26px;font-weight:bold"><?php echo' '.number_format($totalko, 2).''; ?></td>

</tr>




</tbody>
</table>


</br></br>












	

</br>




</div>