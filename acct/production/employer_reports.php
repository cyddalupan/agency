<!DOCTYPE html>
<html lang="en">
<head>
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">



</head>
<?php
include'inc/functions.php';
include'styles.php';
include'tracking.php';
//$app = mysql_query("SELECT * FROM apllicant where applicant_employer=".$_GET['emp_id']." ");
//$row=mysql_fetch_array($app);
?>

<title>Principal Summary</title>

<!-- Bootstrap -->
<link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
<!-- NProgress -->
<link href="../vendors/nprogress/nprogress.css" rel="stylesheet">
<!-- iCheck -->
<link href="../vendors/iCheck/skins/flat/green.css" rel="stylesheet">

<!-- bootstrap-progressbar -->
<link href="../vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
<!-- JQVMap -->
<link href="../vendors/jqvmap/dist/jqvmap.min.css" rel="stylesheet"/>
<!-- bootstrap-daterangepicker -->
<link href="../vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">

<!-- Custom Theme Style -->
<link href="../build/css/custom.min.css" rel="stylesheet">
<style>
#wrapper{
border:0px solid black;
width:90%;
min-height:130px;
height:!important;
margin: 0 auto;	
padding:1px;
font-size:12px;
font-family:calibri;
PADDING:10PX;
}
.table-fixed{
width:100%;	
}
.table-fixed td,th{
font-size:12px;
padding:1px;
color:black;
}gent_i


</style>

<body style="background:white">
<div id="wrapper">
<?php
$fafa = mysql_query("SELECT * FROM employer where employer_id=".$_GET['emp_id']." ");
$fafaemployer=mysql_fetch_array($fafa);
?>


<table id="customers">
<tr>
<td STYLE="font-size:16px"><b>Code # :</b> 000<?=$fafaemployer["employer_id"]?> </td>
<td STYLE="font-size:16px"><b>FRA Name :  </b><?=$fafaemployer["employer_name"]?></td>
<td STYLE="font-size:16px"><b>Agreement :  </b><?=$fafaemployer["employer_remarks"]?></td>

</tr>
</table>















<table  cellspacing="0" id="example-checkbox1">
<thead>
<tr>

<th STYLE="width:2%">Code #</th>
<th >Principal</th>

<th >Visa Stamp</th>
<th >w/ Contract</th>
<th >No Contract</th>
<th >Available</th>
<th >Deployed</th>
<th >Selected</th>
<th >Total</th>
<th>%</th>


<th>BALANCE</th>

</tr>
</thead>
<tbody>
<?php	

$appemp = mysql_query("SELECT * FROM employer
where  employer_id=".$_GET['emp_id']."
");






while($rowman=mysql_fetch_array($appemp))
{

$aff = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed+fra_sent+fra_before) as singils ,applicant.* FROM applicant
WHERE applicant_employer=".$_GET['emp_id']."");
$rowre=mysql_fetch_array($aff);	

$app1 = mysql_query("SELECT SUM(amount) as USD ,emp_id,currency FROM email_address
WHERE emp_id=".$_GET['emp_id']." AND currency='USD'");
$usd=mysql_fetch_array($app1);


$unfit = mysql_query("SELECT SUM(receivable) as receive ,emp_id,receivable FROM email_address4
WHERE emp_id='".$_GET['emp_id']."'  AND currency='PHP'");
$unfitrow=mysql_fetch_array($unfit);

$CON = mysql_query("SELECT SUM(receivable) as receiveusd ,emp_id,receivable FROM email_address4
WHERE emp_id='".$_GET['emp_id']."'  AND  currency='USD' ");
$contract=mysql_fetch_array($CON);

//$result1 = mysql_query("SELECT count(emp_id) as alls FROM  email_address2 where emp_id='".$row['employer_id']."'");
//$all = mysql_fetch_array($result1);


$col = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
WHERE emp_id='".$_GET['emp_id']."'  AND  currency='USD' and status_collect=0");
$colusd=mysql_fetch_array($col);


$deduction = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
WHERE emp_id='".$_GET['emp_id']."'  AND  currency='USD' and status_collect=1");
$deduction1=mysql_fetch_array($deduction);

$oldcollect = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
WHERE emp_id='".$_GET['emp_id']."'  AND  currency='USD' and status_collect=2");
$oldcollect1=mysql_fetch_array($oldcollect);


$expnese88= mysql_query("SELECT SUM(amount) as totalamount ,email_address.* FROM email_address
WHERE emp_id='".$_GET['emp_id']."'  AND  currency='PHP' AND request_payment=3");
$expnese881=mysql_fetch_array($expnese88);



$oldme8 = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed+fra_sent+fra_before) AS amount,applicant.* FROM applicant
WHERE  applicant_employer=".$_GET['emp_id']." AND   applicant_status NOT IN (25,1,21,14,22)   ");
$oldme81=mysql_fetch_array($oldme8);


//$colS = mysql_query("SELECT SUM(amount) as amounPHP ,emp_id,amount FROM email_address6
//WHERE emp_id='".$row["employer_id"]."'  AND  currency='PHP' ");
//$colphp=mysql_fetch_array($colS);

$backout = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed+fra_sent+fra_before) AS amount,applicant.*  FROM applicant
WHERE applicant_status IN (25,1,21,14,22) AND applicant_employer=".$_GET['emp_id']." ");
$backout1=mysql_fetch_array($backout);


$repatresult = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed+fra_sent+fra_before) AS amount,applicant.*,deployed.* FROM applicant
LEFT JOIN deployed
ON applicant.applicant_id = deployed.deployed_applicant
WHERE applicant_status  IN (22) AND applicant_employer=".$_GET['emp_id']."AND deployed_date between date_sub(now(), interval 91 day)  AND date_add(now(), interval 1 day)GROUP BY applicant_id ");
$repatresult1=mysql_fetch_array($repatresult);
$chargejayven=$expnese881["totalamount"]/50;$singil=$chargejayven+$oldcollect1["amountusd"]+$oldme81["amount"]+$repatresult1["amount"]+$backout1["amount"];$alldeduct=$colusd["amountusd"]+$deduction1["amountusd"];$balancesheet=$singil-$alldeduct;




$deployed1 =
mysql_query("SELECT count(DISTINCT deployed.deployed_applicant) as alls,
applicant.applicant_id as adonis,deployed.*,
applicant.*
FROM deployed
LEFT JOIN applicant
ON deployed.deployed_applicant = applicant.applicant_id
where
applicant.applicant_status IN (9)
AND applicant_employer=".$_GET['emp_id']." 
order by deployed_date desc");
$deployedw = mysql_fetch_array($deployed1);







$visas =  mysql_query("SELECT 
count(requirement_visa_stamp) as alls,
applicant.applicant_id as adonis, 
applicant_requirement.*,
applicant.* 
FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status  NOT IN (9,21,22,1,14,25)
AND applicant_employer=".$_GET['emp_id']." 
AND requirement_visa_stamp NOT IN ('1970-01-01','0000-00-00')
");
$visa = mysql_fetch_array($visas);




$sign = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  NOT IN (9,21,22,1,14,25,5)
AND applicant_employer=".$_GET['emp_id']." 
AND  requirement_visa_stamp  IN ('0000-00-00','1970-01-01','')
AND requirement_contract NOT IN ('1970-01-01','0000-00-00') ");
$signrow = mysql_fetch_array($sign);


$nocontract = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  NOT IN (9,21,22,1,14,25,5)
AND applicant_employer=".$_GET['emp_id']." 
AND requirement_contract  IN ('1970-01-01','0000-00-00')
 ");
$nocontract1 = mysql_fetch_array($nocontract);



$sign = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  NOT IN (9,21,22,1,14,25,5)
AND applicant_employer=".$_GET['emp_id']." 
AND  requirement_visa_stamp  IN ('0000-00-00','1970-01-01','')
AND requirement_contract NOT IN ('1970-01-01','0000-00-00') ");
$signrow = mysql_fetch_array($sign);


$onprocess = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status    NOT IN (9,21,22,1,14,25,5)
AND applicant_employer=".$_GET['emp_id']." ");
$onprocess1 = mysql_fetch_array($onprocess);



$oec = mysql_query("SELECT 
count(	requirement_oec_release_date) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status   NOT IN (9,21,22,1,14,25,5)
AND applicant_employer=".$_GET['emp_id']." 
AND requirement_oec_release_date NOT IN ('1970-01-01','0000-00-00') ");
$oec1 = mysql_fetch_array($oec);



$fit2 = mysql_query("SELECT 
count(applicant_id) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status     IN (5)
AND applicant_employer=".$_GET['emp_id']." ");
$fitt = mysql_fetch_array($fit2);


$percent=($deployedw['alls']+$onprocess1['alls']);
$percent1=($deployedw['alls']/$percent)*100;
$totalsaudi=($deployedw['alls']+$onprocess1['alls']);
echo'<tr>';




echo'<td>000'.$rowman["employer_id"].'</td>';
echo'<td style="text-align:left"><b>'.$rowman["employer_name"].'</b></td>';



echo'<td><b>'.$visa['alls'].'</b></td>';
echo'<td><b>'.$signrow['alls'].'</b></td>';
echo'<td><b>'.$nocontract1['alls'].'</b></td>';
echo'<td style="background:#D3D3D3"><b>'.$fitt['alls'].'</b></td>';
echo'<td  style="text-align:center;color:black;font-weight:bold;background:yellow"><b>'.$deployedw['alls'].'</b></td>';
echo'<td style="text-align:center;color:black;font-weight:bold;background:yellow"><b>'.$onprocess1['alls'].'</b></td>';
echo'<td style="background:yellow">'.$totalsaudi.'</td>';


if($percent1!=0){ echo'<td style="color:black;background:#F08080">'.number_format($percent1,1).' %</td>'; }
if($percent1==0){ echo'<td style="color:blue;font-weight:bold">--</td>'; }

echo'<td style="color:black;background:#F08080">$'.number_format($balancesheet,2).' </td>';

//echo'<td style="color:orange">'.number_format($colusd["amountusd"],2).'</td>';
//echo'<td style="color:orange">'.number_format($colphp["amounPHP"],2).'</td>';
/*
echo'<td>
<a href="summary_employer.php?emp_id='.$row["employer_id"].'" target="_blank" style="color:black;font-size:11px">

<button type="button" class="btn btn-danger" btn-xs" style="font-size:11px">Summary Reports</button>
</A>
</td>';
*/
$deployedall=$deployedw['alls']+$deployedall;
$oecall=$oec1['alls']+$oecall;
$visaall=$visa['alls']+$visaall;
$signall=$signrow['alls']+$signall;
$fitall=$fitt['alls']+$fitall;
$onprocessall=$onprocess1['alls']+$onprocessall;
$compute=$onprocessall+$deployedall;
$percentall=($deployedall/$compute)*100;
$grandbalance=$balancesheet+$grandbalance;
$totalsaudi1=$totalsaudi+$totalsaudi1;
$nocontractsaudi=$nocontract1['alls']+$nocontractsaudi;

echo'</tr>';
}
?>
</tbody>
</table>
</br></br></br>











<h2 style="color:Red;font-size:20px"><u>Old Collectables <small  style="color:black">(DEBIT)</small></u></h2>
<table class="table table-fixed">
<thead>
<tr>
<th>Code #</th>
<th>DATE</th>
<th>Reference Number:</th>
<th>Applicant</th>
<th>Account</th>
<th>Description</th>
<th>Amount</th>
</tr>
</thead>
<tbody>

<?php	
//AND collect_status=1
$collectables = mysql_query("SELECT * FROM email_address6
Where collect_type=1 and status_collect=2 AND currency='USD'
and emp_id=".$_GET['emp_id']."
ORDER BY tr_id desc");
while($collectables1=mysql_fetch_array($collectables))
{
//$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
//$row1=mysql_fetch_array($app1);
$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$collectables1['app_id']." ");
$rowp=mysql_fetch_array($applicant);

$totalcollectables=$collectables1["amount"]+$totalcollectables;

if($collectables1["collect_status"]==0){$request='Pending';$rcolor='black';}
if($collectables1["collect_status"]==1){$request='Approved';$rcolor='green';}
if($collectables1["collect_status"]==2){$request='Return to maker';$rcolor='red';}

echo'<tr>';
echo'<td style="font-size:10px">000'.$collectables1["tr_id"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($collectables1['date']));echo'</td>';
echo'<td>'.$collectables1["or_num"].'</td>';
echo'<td>
'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$collectables1["account"].'</td>';
echo'<td>'.$collectables1["particular"].'</td>';
echo'<td>$ '.$collectables1["amount"].'</td>';
echo'</tr>';
}
?>
<tr>
<td colspan="12" STYLE="text-align:right;FONT-weight:bold;font-size:16px">TOTAL.: $ 
 <?php echo' '.number_format($totalcollectables, 2).''; ?></td>
</tr>
</tbody>
</table>


<div id="home" class="tab-pane fade in active">
<div class="col-xs-12 table">
<h2 style="color:Red;font-size:20px"><u>Collectables <small  style="color:black">(DEBIT per applicants)</small></u></h2>
<table class="table table-fixed">
<thead>
<tr>
<th STYLE="width:2%"></th>
<th>DATE</th>
<th>Name</th>

<th>Passport</th>
<th>Country</th>
<th>Principal</th>
<th>Status</th>

<th>CV Sent</th>
<th>Visa Stamped</th>
<th>Contract</th>
<th>before Deployed</th>
<th>Deployed</th>
</tr>
</thead>
<tbody>

<?php	
//AND collect_status=1
$applicant = mysql_query("SELECT * FROM applicant
WHERE applicant_paid!=1
AND   applicant_status NOT IN (25,1,21,14,22)  
AND applicant_employer=".$_GET['emp_id']."
AND (fra_ftw!=0 OR fra_visa!=0 OR fra_deployed!=0 OR fra_sent!=0 OR fra_before!=0)ORDER BY applicant_id desc limit 100");

while($applicant1=mysql_fetch_array($applicant))
{
$status = mysql_query("SELECT * FROM statuses where number=".$applicant1['applicant_status']." ");
$srow=mysql_fetch_array($status);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$applicant1['applicant_source']." ");
$row1=mysql_fetch_array($app1);

$emp = mysql_query("SELECT * FROM employer where employer_id=".$applicant1['applicant_employer']." ");
$emprow=mysql_fetch_array($emp);

$passport = mysql_query("SELECT * FROM applicant_passport where passport_applicant=".$applicant1['applicant_id']." ");
$passportrow=mysql_fetch_array($passport);

$country = mysql_query("SELECT * FROM country where country_id=".$applicant1['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);

$LIQ = mysql_query("SELECT sum(amount) as cashfund FROM  email_address6 
where  app_id=".$applicant1['applicant_id']."");
$cashliq=mysql_fetch_array($LIQ);

$balancecollection=$applicant1['singil']-$cashliq['cashfund'];

if($applicant1['applicant_paid']!=1)
{ $paids='UNPAID'; }  

if($applicant1['applicant_paid']==1)
{ $paids='PAID'; } 

$totalnew1=($applicant1["fra_ftw"]+$applicant1["fra_visa"]+$applicant1["fra_sent"]+$applicant1["fra_before"]+$applicant1["fra_deployed"]);

$totalnew=($applicant1["fra_ftw"]+$applicant1["fra_visa"]+$applicant1["fra_sent"]+$applicant1["fra_before"]+$applicant1["fra_deployed"])+$totalnew;


echo'<tr>';
echo "<td><input type='hidden' name='applicant_id[]' value='".$applicant1['applicant_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($applicant1['applicant_date_applied']));echo'</td>';
echo'<td><b>'.$applicant1["applicant_first"].' '.$applicant1["applicant_last"].'</b></td>';
echo'<td>'.$passportrow["passport_number"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';
echo'<td>'.$emprow["employer_name"].'</td>';
echo "<td>".$paids."</td> ";
echo "<td>$ ".$applicant1['fra_sent']."</td> ";
echo "<td>$ ".$applicant1['fra_ftw']."</td> ";
echo "<td>$ ".$applicant1['fra_visa']."</td> ";
echo "<td>$ ".$applicant1['fra_before']."</td> ";
echo "<td>$ ".$applicant1['fra_deployed']."</td> ";
echo "<td>$ ".$totalnew1."</td> ";


}
?>
<tr>
<td colspan="12" STYLE="text-align:right;FONT-weight:bold;font-size:16px">TOTAL.:  <?php echo' '.number_format($totalnew, 2).''; ?></td>
</tr>
</tbody>
</table>
</div>
</div>
<DIV style="page-break-after:always"></DIV>












<div id="home" class="tab-pane fade in active">
<div class="col-xs-12 table">
<h3 style="color:black;font-size:20px">Total Backout/Cancelled/Repat(Charge Back)</h5>
<table class="table table-fixed">
<thead>
<tr>
<th STYLE="width:2%"></th>
<th>DATE</th>
<th>Name</th>
<th>Source</th>
<th>Country</th>
<th>Principal</th>
<th>Status</th>
<th>CV Sent</th>
<th>Visa Stamped</th>
<th>Contract</th>
<th>Before Deployed</th>
<th>Deployed</th>
<th>Date Deployed</th>
</tr>
</thead>
<tbody>

<?php	
//AND collect_status=1
$backout = mysql_query("SELECT * FROM applicant
WHERE applicant_status IN (25,1,21,14) AND applicant_employer=".$_GET['emp_id']."
 AND (fra_ftw!=0 OR fra_visa!=0 OR fra_deployed!=0 OR fra_sent!=0 OR fra_before!=0) GROUP BY applicant_id 
");

while($backout1=mysql_fetch_array($backout))
{
$status = mysql_query("SELECT * FROM statuses where number=".$backout1['applicant_status']." ");
$srow=mysql_fetch_array($status);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$backout1['applicant_source']." ");
$row1=mysql_fetch_array($app1);

$cert1 = mysql_query("SELECT * FROM applicant_certificate where certificate_applicant=".$backout1['applicant_id']." ");
$row1cert=mysql_fetch_array($cert1);

$req = mysql_query("SELECT * FROM applicant_requirement where requirement_applicant=".$backout1['applicant_id']." ");
$row2=mysql_fetch_array($req);


$emp = mysql_query("SELECT * FROM employer where employer_id=".$backout1['applicant_employer']." ");
$emprow=mysql_fetch_array($emp);

$country = mysql_query("SELECT * FROM country where country_id=".$backout1['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);

$charge1all=($backout1["fra_ftw"]+$backout1["fra_visa"]+$backout1["fra_deployed"]+$backout1["fra_sent"]+$backout1["fra_before"])+$charge1all;

if($backout1["applicant_status"]==1){$anoba='Cancelled';}
if($backout1["applicant_status"]==25){$anoba='Backout';}
if($backout1["applicant_status"]==21){$anoba='Unfit';}
if($backout1["applicant_status"]==14){$anoba='A to A';}

echo'<tr>';
echo "<td><input type='hidden' name='applicant_id[]' value='".$backout1['applicant_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($backout1['applicant_date_applied']));echo'</td>';
echo'<td><b>'.$backout1["applicant_first"].' '.$backout1["applicant_last"].'</b></td>';
echo'<td>'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';
echo'<td>'.$emprow["employer_name"].'</td>';
echo'<td>'.$anoba.'</td>';
echo "<td>$ ".$backout1['fra_sent']."</td> ";
echo "<td>$ ".$backout1['fra_ftw']."</td> ";
echo "<td>$ ".$backout1['fra_visa']."</td> ";
echo "<td>$ ".$backout1['fra_before']."</td> ";
echo "<td>$ ".$backout1['fra_deployed']."</td> ";
echo "<td></td> ";
}
echo'<tr>
<td colspan="10" style="color:red;text-align:center">"Repat"</td>
<tr>';



$repatresult = mysql_query("SELECT applicant.*,deployed.* FROM applicant

LEFT JOIN deployed
ON applicant.applicant_id = deployed.deployed_applicant

WHERE applicant_status  IN (22) AND applicant_employer=".$_GET['emp_id']."
AND deployed_date between date_sub(now(), interval 91 day)  AND date_add(now(), interval 1 day)
GROUP BY applicant_id 
 ");
//$rowcount=mysql_fetch_array($repatresult);
while($repatresult1=mysql_fetch_array($repatresult))
{
$status = mysql_query("SELECT * FROM statuses where number=".$repatresult1['applicant_status']." ");
$srow=mysql_fetch_array($status);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$repatresult1['applicant_source']." ");
$row1=mysql_fetch_array($app1);

$cert1 = mysql_query("SELECT * FROM applicant_certificate where certificate_applicant=".$repatresult1['applicant_id']." ");
$row1cert=mysql_fetch_array($cert1);

$req = mysql_query("SELECT * FROM applicant_requirement where requirement_applicant=".$repatresult1['applicant_id']." ");
$row2=mysql_fetch_array($req);


$emp = mysql_query("SELECT * FROM employer where employer_id=".$repatresult1['applicant_employer']." ");
$emprow=mysql_fetch_array($emp);

$country = mysql_query("SELECT * FROM country where country_id=".$repatresult1['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);


$deployed = mysql_query("SELECT * FROM deployed where deployed_applicant=".$repatresult1['applicant_id']." order by deployed_id desc");
$deployed1=mysql_fetch_array($deployed);



$date_deployed=date_create("".$deployed1['deployed_date']."");
$datecur1=date_create("".$row['repat_date']."");
$diff1=date_diff($date_deployed,$datecur1);
$dayone=$diff1->format("%r%a");


$charge1allrepat=($repatresult1["fra_ftw"]+$repatresult1["fra_visa"]+$repatresult1["fra_deployed"])+$charge1allrepat;

if($backout1["applicant_status"]==22){$anoba='Repat';}

echo'<tr>';
echo "<td><input type='hidden' name='applicant_id[]' value='".$repatresult1['applicant_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($repatresult1['applicant_date_applied']));echo'</td>';
echo'<td><b>'.$repatresult1["applicant_first"].' '.$repatresult1["applicant_last"].'</b></td>';
echo'<td>'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';
echo'<td>'.$emprow["employer_name"].'</td>';
echo'<td>'.$anoba.'</td>';
echo "<td>$ ".$repatresult1['fra_ftw']."</td> ";
echo "<td>$ ".$repatresult1['fra_visa']."</td> ";
echo "<td>$ ".$repatresult1['fra_deployed']."</td> ";
echo "<td>".$repatresult1['deployed_date']." ".$row['repat_date'].""; echo $diff1->format("%r%a days");   echo" </i></td>";
}


$allcharges=$charge1all+$charge1allrepat;



?>





<tr>
<td colspan="12" STYLE="text-align:right;FONT-weight:bold;font-size:16px">TOTAL : $ <?php echo' '.number_format($allcharges, 2).''; ?></td>
</tr>

</tbody>
</table>
</div>
</div>










<DIV style="page-break-after:always"></DIV>


<div id="wrapper">
<h2 style="color:blue;font-size:20px"><u>Received / Collected <small  style="color:black">(CREDIT)</small></u></h2>
<table class="table table-fixed">
<thead>
<tr>
<th>Code #</th>
<th>DATE</th>
<th>Reference Number:</th>
<th>Applicant</th>
<th>Account</th>
<th>Description</th>
<th>Amount</th>
</tr>
</thead>
<tbody>

<?php	
//AND collect_status=1
$collected = mysql_query("SELECT * FROM email_address6
Where collect_type=1 
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
echo'<td style="font-size:10px">000'.$collected1["tr_id"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($collected1['date']));echo'</td>';
echo'<td>'.$collected1["or_num"].'</td>';
echo'<td>
'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$collected1["account"].'</td>';
echo'<td>'.$collected1["particular"].'</td>';
echo'<td> '.$collected1["amount"].'</td>';
echo'</tr>';
}
?>
<tr>
<td colspan="12" STYLE="text-align:right;FONT-weight:bold;font-size:16px;color:blue">TOTAL.:  <?php echo' '.number_format($totalreceived, 2).''; ?></td>
</tr>
</tbody>
</table>



































<h2 style="color:blue;font-size:20px"><u>Payable / Charge <small style="color:black">(CREDIT repatration,bakcout etc.)</small></u></h2>
<table class="table table-fixed">
<thead>
<tr>
<th>Code #</th>
<th>DATE</th>
<th>Reference Number:</th>
<th>Applicant</th>
<th>Account</th>
<th>Description</th>
<th>Amount</th>
</tr>
</thead>
<tbody>

<?php	
//AND collect_status=1
$payable = mysql_query("SELECT * FROM email_address6
Where collect_type=1 and status_collect=1 AND currency='USD'
and emp_id=".$_GET['emp_id']."
ORDER BY tr_id desc");
while($payable1=mysql_fetch_array($payable))
{
//$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
//$row1=mysql_fetch_array($app1);
$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$payable1['app_id']." ");
$rowp=mysql_fetch_array($applicant);

$totalpayable=$payable1["amount"]+$totalpayable;

if($payable1["collect_status"]==0){$request='Pending';$rcolor='black';}
if($payable1["collect_status"]==1){$request='Approved';$rcolor='green';}
if($payable1["collect_status"]==2){$request='Return to maker';$rcolor='red';}

echo'<tr>';
echo'<td style="font-size:10px">000'.$payable1["tr_id"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($payable1['date']));echo'</td>';
echo'<td>'.$payable1["or_num"].'</td>';
echo'<td>
'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$payable1["account"].'</td>';
echo'<td>'.$payable1["particular"].'</td>';
echo'<td>'.$payable1["currency"].' '.$payable1["amount"].'</td>';
echo'</tr>';
}
?>
<tr>
<td colspan="12" STYLE="text-align:right;FONT-weight:bold;font-size:16px;color:blue">TOTAL.: $  <?php echo' '.number_format($totalpayable, 2).''; ?></td>
</tr>
</tbody>
</table>
</DIV>




















<div style='page-break-before: always;'></div>



<div id="wrapper">

<h2 style="color:RED;font-size:20px"><u>OTHER CHARGES<small  style="color:black">(DEBIT)</small></u></h2>
<table class="table table-fixed">
<thead>
<tr>
<th>Code #</th>
<th>DATE</th>
<th>currency</th>

<th>Account</th>
<th>Description</th>
<th>amount</th>

</tr>
</thead>
<tbody>

<?php	
//AND collect_status=1


$fund111119 = mysql_query("SELECT * FROM email_address
WHERE   request_payment=3 and currency='PHP' AND  emp_id=".$_GET['emp_id']."  ");



while($fund188=mysql_fetch_array($fund111119))
{
$tot=$fund188["amount"]/50;
$fundsssss88=($fund188["amount"]/50)+$fundsssss88;

echo'<tr>';
echo'<td style="font-size:10px">000'.$fund188["ref"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($fund188['date_ad']));echo'</td>';
echo'<td>'.$fund188["currency"].'</td>';
echo'<td>'.$fund188["account"].'</td>';
echo'<td>'.$fund188["description"].'</td>';


echo'<td>'.$tot.'</td>';
echo'</tr>';
}
?>
<tr>
<td colspan="12" STYLE="text-align:right;FONT-weight:bold;font-size:16px;color:blue">TOTAL.: <?=$_GET['cur']?>   <?php echo' '.number_format($fundsssss88, 2).''; ?></td>
</tr>
</tbody>
</table>

</DIV>





<?php
$grandtotalcollectable=$totalcollectables+$totalnew+$fundsssss88+$allcharges;
$collectefee=$totalreceived+$totalpayable;
$totalcre=$collectefee;
$lahatna=$grandtotalcollectable-$totalcre;
?>







<div id="wrapper">
<h2 style="color:BLACK;font-size:20px;float:right"><u>SUMMARY REPORTS </u></h2>
<div style="clear:both;"></div>
<table class="table table-fixed" style="width:500px;float:right">
<tbody>
<TR>
<td style="font-size:16px"><b>Total Collectables (Debit)</b></td>
<td  style="font-size:16px"><?php echo' '.number_format($grandtotalcollectable, 2).''; ?></td>
</tr>

<TR>
<td style="font-size:16px"><b>Total Collected (Credit)</b></td>
<td  style="font-size:16px"><?php echo' '.number_format($collectefee, 2).''; ?></td>
</tr>



<TR>
<td style="font-size:16px" colspan="2"><b>_________________________________________________________________</b></td>
</tr>

<TR>
<td style="font-size:16px"><b>Balance</b></td>
<td  style="font-size:16px;color:BLACK;text-align:right;FONT-WEIGHT:bold"><?php echo' '.number_format($lahatna, 2).''; ?></td>
</tr>
</tbody>
</table>

</DIV>






</body>