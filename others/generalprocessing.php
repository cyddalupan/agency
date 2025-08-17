<?php include'review.php';?>
<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<!-- Head -->
<head>
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<title></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../assets/styles/admin/bootstrap.min.css" rel="stylesheet" />
<link href="../assets/styles/admin/font-awesome.min.css" rel="stylesheet" />


<!--Beyond styles-->
<link id="beyond-link" href="../assets/styles/admin/beyond.min.css" rel="stylesheet" type="text/css" />
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

 
</head>
<style>
th{
font-size:12px;
font-weight:none;
color:blue;	
padding:1px;
}
tD{
padding:1px;
}
</style>

<!-- /Head -->
<!-- Body -->
<body style="background:white">

<div class="col-md-14 col-sm-14" style="margin-top:-1px">
<div class="row">
<div class="col-md-12">

<div class="clients-page">


<div class="row" style="margin:30px">
<div class="col-md-12"  style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC;border:1px solid #DCDCDC;width:120%">



<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3 style="color:red"><span> Dashboard</span></h3>
<!-- para -->
</div> 

<?php
include'menuchange.php';
include'menu.php';
?>
<ul id="menu">
<li><a   class="active" href="dashboard.php"><?=$dashboard?></a></li>

</ul>


<div style="clear:both;height:10px"></div>

<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;margin:5px">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">


<div style="clear:both"></div>

<?php
//include'schedule.php';
echo'<div style="clear:both;height:20px"></div>';

?>

<style>
#example-checkbox table{
width:100%;	
}
#example-checkbox td{
font-size:14px;
text-align:center;	
}
</style>





<h2 style="color:black"> <b style="color:red">FRA SUMMARY</b></h2>
<table  cellspacing="0" id="example-checkbox" style="border:1px solid black">
<thead>
<tr>
<th>View DEBIT/CREDIT</th>
<th STYLE="width:2%">Code #</th>
<th   >Principal</th>
<th >Available</th>
<th >Selected</th>
<th >Transmittal</th>
<th >Contract</th>
<th >Signed</th>
<th >OWWA</th>
<th >Visa</th>
<th >Oec</th>
<th >Booking</th>
<th >Deployment</th>

<th>%</th>
<th >Deployed</th>
<th >Total Reported</th>
</tr>
</thead>
<tbody>
<?php	
include'db.php';
include'tracking.php';
$app = mysql_query("SELECT * FROM employer
order by employer_name desc");



if(isset($_POST['Search']))
{
$app = mysql_query("SELECT * FROM employer
where
employer_name LIKE '%".$_POST['search']."%' limit 20
");
}


while($row=mysql_fetch_array($app))
{

$aff = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed) as singils ,applicant.* FROM applicant
WHERE applicant_employer=".$row["employer_id"]."");
$rowre=mysql_fetch_array($aff);	

$app1 = mysql_query("SELECT SUM(amount) as USD ,emp_id,currency FROM email_address
WHERE emp_id=".$row["employer_id"]." AND currency='USD'");
$usd=mysql_fetch_array($app1);


$unfit = mysql_query("SELECT SUM(receivable) as receive ,emp_id,receivable FROM email_address4
WHERE emp_id='".$row["employer_id"]."'  AND currency='PHP'");
$unfitrow=mysql_fetch_array($unfit);

$CON = mysql_query("SELECT SUM(receivable) as receiveusd ,emp_id,receivable FROM email_address4
WHERE emp_id='".$row["employer_id"]."'  AND  currency='USD' ");
$contract=mysql_fetch_array($CON);

//$result1 = mysql_query("SELECT count(emp_id) as alls FROM  email_address2 where emp_id='".$row['employer_id']."'");
//$all = mysql_fetch_array($result1);


$col = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
WHERE emp_id='".$row["employer_id"]."'  AND  currency='USD' and status_collect=0");
$colusd=mysql_fetch_array($col);


$deduction = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
WHERE emp_id='".$row["employer_id"]."'  AND  currency='USD' and status_collect=1");
$deduction1=mysql_fetch_array($deduction);

$oldcollect = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
WHERE emp_id='".$row["employer_id"]."'  AND  currency='USD' and status_collect=2");
$oldcollect1=mysql_fetch_array($oldcollect);


$expnese88= mysql_query("SELECT SUM(amount/50) as totalamount ,email_address.* FROM email_address
WHERE emp_id='".$row["employer_id"]."'  AND  currency='PHP' AND request_payment=3");
$expnese881=mysql_fetch_array($expnese88);



$oldme8 = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed) AS amount,applicant.* FROM applicant
WHERE  applicant_employer=".$row['employer_id']." AND   applicant_status NOT IN (25,1,21,14,22)   ");
$oldme81=mysql_fetch_array($oldme8);


//$colS = mysql_query("SELECT SUM(amount) as amounPHP ,emp_id,amount FROM email_address6
//WHERE emp_id='".$row["employer_id"]."'  AND  currency='PHP' ");
//$colphp=mysql_fetch_array($colS);

$backout = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed) AS amount,applicant.*  FROM applicant
WHERE applicant_status IN (25,1,21,14) AND applicant_employer=".$row['employer_id']." ");
$backout1=mysql_fetch_array($backout);


$repatresult = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed) AS amount,applicant.*,deployed.* FROM applicant
LEFT JOIN deployed
ON applicant.applicant_id = deployed.deployed_applicant
WHERE applicant_status  IN (22) AND applicant_employer=".$_GET['emp_id']."
AND deployed_date between date_sub(now(), interval 91 day)  AND date_add(now(), interval 1 day)");
$repatresult1=mysql_fetch_array($repatresult);

$singil=$expnese881["totalamount"]+$oldcollect1["amountusd"]+$oldme81["amount"];
$alldeduct=$colusd["amountusd"]+$deduction1["amountusd"]+$backout1["amount"]+$repatresult1["amount"];
$balancesheet=$singil-$alldeduct;




$deployed1 =
mysql_query("SELECT count(DISTINCT deployed.deployed_applicant) as alls,
applicant.applicant_id as adonis,deployed.*,
applicant.*
FROM deployed
LEFT JOIN applicant
ON deployed.deployed_applicant = applicant.applicant_id
where
applicant.applicant_status IN (9)
AND applicant_employer=".$row["employer_id"]." 
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
where applicant_status  NOT IN (9,21,22,1,14,25)
AND applicant_employer=".$row["employer_id"]." 
AND requirement_visa_stamp NOT IN ('1970-01-01','0000-00-00')
");
$visa = mysql_fetch_array($visas);



$sign = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  NOT IN (9,21,22,1,14,25)
AND applicant_employer=".$row["employer_id"]." 
AND requirement_contract NOT IN ('1970-01-01','0000-00-00') ");
$signrow = mysql_fetch_array($sign);

$contractsign = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  NOT IN (9,21,22,1,14,25)
AND applicant_employer=".$row["employer_id"]." 
AND requirement_contract NOT IN ('1970-01-01','0000-00-00')
AND requirement_contract_sign NOT IN ('1970-01-01','0000-00-00') ");
$contractsign1 = mysql_fetch_array($contractsign);





$transmittal = mysql_query("SELECT 
count(transnum) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  NOT IN (9,21,22,1,14,25)
AND applicant_employer=".$row["employer_id"]." 
AND transnum NOT IN ('1970-01-01','0000-00-00','') ");
$transmittal1 = mysql_fetch_array($transmittal);


$onprocess = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status    NOT IN (9,21,22,1,14,25,5)
AND applicant_employer=".$row["employer_id"]." ");
$onprocess1 = mysql_fetch_array($onprocess);


$oec = mysql_query("SELECT 
count(	requirement_oec_release_date) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  NOT IN (9,21,22,1,14,25)
AND applicant_employer=".$row["employer_id"]." 
AND requirement_oec_release_date NOT IN ('1970-01-01','0000-00-00') ");
$oec1 = mysql_fetch_array($oec);




$fit2 = mysql_query("SELECT 
count(applicant_id) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status     IN (5)
AND applicant_employer=".$row["employer_id"]." ");
$fitt = mysql_fetch_array($fit2);


$ticket = mysql_query("SELECT 
count(requirement_applicant) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status   IN (12,8)
AND flight_date  IN ('1970-01-01','0000-00-00','')
AND applicant_employer=".$row["employer_id"]." ");
$ticket1 = mysql_fetch_array($ticket);


$ticketwith = mysql_query("SELECT 
count(requirement_applicant) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status   IN (12,8)
AND flight_date  NOT IN ('1970-01-01','0000-00-00','')
AND applicant_employer=".$row["employer_id"]." ");
$ticketwith1 = mysql_fetch_array($ticketwith);



$owwa = mysql_query("SELECT 
count(certificate_applicant) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant.* FROM applicant
LEFT JOIN 	applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant
where applicant_status NOT IN (9,21,22,1,14,25)
AND certificate_owwa_to NOT IN ('1970-01-01','0000-00-00','')
AND applicant_employer=".$row["employer_id"]." ");
$owwa1 = mysql_fetch_array($owwa);


$medicals = mysql_query("SELECT 
count(certificate_applicant) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant.* FROM applicant
LEFT JOIN 	applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant
where applicant_status NOT IN (9,21,22,1,14,25)
AND certificate_owwa_to NOT IN ('1970-01-01','0000-00-00','')
AND applicant_employer=".$row["employer_id"]." ");
$medicals1 = mysql_fetch_array($medicals);


$percent=($deployedw['alls']+$onprocess1['alls']);
$percent1=($deployedw['alls']/$percent)*100;
$totalsaudi=($deployedw['alls']+$onprocess1['alls']);


echo'<tr>';


if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){
echo'<td>
<a href="employer_reports.php?emp_id='.$row["employer_id"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Debit/Credit</button></a>

</td>';

}
Else{
echo'<td>
</td>';	
}



echo'<td>000'.$row["employer_id"].'</td>';
echo'<td style="text-align:left"><b>'.$row["employer_name"].'</b></td>';
echo'<td><a href="myreport/line.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$fitt['alls'].'</a></td>';
echo'<td style="text-align:center;color:black;background:yellow""><a href="myreport/undrprocess.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$onprocess1['alls'].'</a></td>';
echo'<td><a href="myreport/trans.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$transmittal1['alls'].'</a></td>';
echo'<td><a href="myreport/contract.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$signrow['alls'].'</a></td>';
echo'<td><a href="myreport/notsigned.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$contractsign1['alls'].'</a></td>';

echo'<td><a href="myreport/owwa.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$owwa1['alls'].'</a></td>';
echo'<td><a href="myreport/visa.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$visa['alls'].'</a></td>';
echo'<td><a href="myreport/oec.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$oec1['alls'].'</a></td>';
echo'<td><a href="myreport/ticket.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$ticket1['alls'].'</a></td>';
echo'<td><a href="myreport/ticket1.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$ticketwith1['alls'].'</a></td>';





if($percent1!=0){ echo'<td style="BACKGROUND:#F08080">'.number_format($percent1,1).' %</td>'; }
if($percent1==0){ echo'<td style="">--</td>'; }
echo'<td  style="background:yellow"><a href="myreport/deployed.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$deployedw['alls'].'</a></td>';
echo'<td style="background:yellow"><a href="myreport/all.php?empname='.$row["employer_name"].'&&empid='.$row["employer_id"].'">'.$totalsaudi.'</a></td>';


$deployedall=$deployedw['alls']+$deployedall;
$oecall=$oec1['alls']+$oecall;
$visaall=$visa['alls']+$visaall;
$signall=$signrow['alls']+$signall;
$fitall=$fitt['alls']+$fitall;
$onprocessall=$onprocess1['alls']+$onprocessall;
$compute=$onprocessall+$deployedall;
$percentall=($deployedall/$compute)*100;
$grandbalance=$balancesheet+$grandbalance;
$ticketsaudi=$ticket1['alls']+$ticketsaudi;
$ticketwithsaudi=$ticketwith1['alls']+$ticketwithsaudi;
$owwasaudi=$owwa1['alls']+$owwasaudi;
$medicalsaudi=$medicals1['alls']+$medicalsaudi;
$totalsaudi1=$totalsaudi+$totalsaudi1;
$transmitalsaudi=$transmittal1['alls']+$transmitalsaudi;
$contractsignsaudi=$contractsign1['alls']+$contractsignsaudi;


$perselect=($onprocessall/$totalsaudi1)*100;
$transper=($transmitalsaudi/$totalsaudi1)*100;
$persign=($signall/$totalsaudi1)*100;
$percontract=($contractsignsaudi/$totalsaudi1)*100;
$perowwa=($owwasaudi/$totalsaudi1)*100;
$pervisa=($visaall/$totalsaudi1)*100;
$peroec=($oecall/$totalsaudi1)*100;
$perticket=($ticketsaudi/$totalsaudi1)*100;
$perticket2=($ticketwithsaudi/$totalsaudi1)*100;
$perdeployed=($deployedall/$totalsaudi1)*100;
echo'</tr>';
}
?>
</tbody>
<tr>
<td colspan="3" style="text-align:right;color:black;font-weight:bold">Total: </td>
<td style="text-align:center;color:black;font-weight:bold"> <?=$fitall?></td>
<td style="text-align:center;color:black;font-weight:bold"> <?=number_format($perselect, 2)?> %</td>
<td style="text-align:center;color:black;font-weight:bold"> <?=number_format($transper, 2)?> %</td>
<td style="text-align:center;color:black;font-weight:bold"> <?=number_format($persign, 2)?> %</td>
<td style="text-align:center;color:black;font-weight:bold"> <?=number_format($percontract, 2)?> %</td>
<td style="text-align:center;color:black;font-weight:bold"> <?=number_format($perowwa, 2)?> %</td>
<td style="text-align:center;color:black;font-weight:bold"> <?=number_format($pervisa, 2)?> %</td>
<td style="text-align:center;color:black;font-weight:bold"> <?=number_format($peroec, 2)?> %</td>
<td style="text-align:center;color:black;font-weight:bold"> <?=number_format($perticket, 2)?> %</td>
<td style="text-align:center;color:black;font-weight:bold"> <?=number_format($perticket2, 2)?> %</td>




<td  style="text-align:center;color:black;font-weight:bold;BACKGROUND:#F08080"> <?=number_format($percentall,1)?>%</td>
<td style="text-align:center;color:black;font-weight:bold;background:yellow">  <?=number_format($perdeployed,1)?>%</td>
<td style="text-align:center;color:black;font-weight:bold;background:yellow"> <?=$totalsaudi1?></td>
</tr>
</table>


<div style="clear:both;height:30px"></div>

















<h2 style="color:black"> <b style="color:red">MEDICAL SUMMARY</b></h2>
<table  cellspacing="0" id="example-checkbox" style="border:1px solid black">
<thead>
<tr>

<th STYLE="width:2%">Code #</th>
<th   >Principal</th>
<th   >New</th>
<th>FTW</th>
<th>PENDING</th>
<th>UNFIT</th>
<th>TOTAL</th>
</tr>
</thead>
<tbody>
<?php	


$meds = mysql_query("SELECT * FROM  country
order by country_name ASC");




while($meds1=mysql_fetch_array($meds))
{


$medicalrme = mysql_query("SELECT 
count(applicant_id) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant.* FROM applicant

LEFT JOIN 	applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant

LEFT JOIN 	country
ON applicant.applicant_preferred_country=country.country_id

where applicant_status NOT IN (9,21,22,1,14,25)
AND certificate_medical_result  IN ('FOR MEDICAL')
AND applicant_preferred_country=".$meds1["country_id"]." ");
$medicalrme1 = mysql_fetch_array($medicalrme);


$fitme = mysql_query("SELECT 
count(applicant_id) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant.* FROM applicant

LEFT JOIN 	applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant

LEFT JOIN 	country
ON applicant.applicant_preferred_country=country.country_id

where applicant_status NOT IN (9,21,22,1,14,25)
AND certificate_medical_result  IN ('FIT TO WORK')
AND applicant_preferred_country=".$meds1["country_id"]." ");
$fitme1 = mysql_fetch_array($fitme);

$pendingme = mysql_query("SELECT 
count(applicant_id) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant.* FROM applicant

LEFT JOIN 	applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant

LEFT JOIN 	country
ON applicant.applicant_preferred_country=country.country_id

where applicant_status NOT IN (9,21,22,1,14,25)
AND certificate_medical_result IN ('PENDING','TREATMENT')
AND applicant_preferred_country=".$meds1["country_id"]." ");
$pendingme1 = mysql_fetch_array($pendingme);

$unfitme = mysql_query("SELECT 
count(applicant_id) as alls,
applicant.applicant_id as adonis, applicant_certificate.*,applicant.* FROM applicant

LEFT JOIN 	applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant

LEFT JOIN 	country
ON applicant.applicant_preferred_country=country.country_id

where applicant_status NOT IN (9,21,22,1,14,25)
AND certificate_medical_result  IN ('UNFIT')
AND applicant_preferred_country=".$meds1["country_id"]." ");
$unfitme1 = mysql_fetch_array($unfitme);

$totalme=$medicalrme1['alls']+$fitme1['alls']+$pendingme1['alls']+$unfitme1['alls'];

echo'<tr>';
echo'<td>000'.$meds1["employer_id"].'</td>';
echo'<td style="text-align:left"><b>'.$meds1["country_name"].'</b></td>';
echo'<td><a href="#">'.$medicalrme1['alls'].'</a></td>';
echo'<td><a href="#">'.$fitme1['alls'].'</a></td>';
echo'<td><a href="#">'.$pendingme1['alls'].'</a></td>';
echo'<td><a href="#">'.$unfitme1['alls'].'</a></td>';
echo'<td><a href="#">'.$totalme.'</a></td>';
echo'</tr>';

$newmed=$medicalrme1['alls']+$newmed;
$newfit=$fitme1['alls']+$newfit;
$newpending=$pendingme1['alls']+$newpending;
$newunfit=$unfitme1['alls']+$newunfit;
$newtotalme=$totalme+$newtotalme;
}


?>
</tbody>
<tr>
<td colspan="2" style="text-align:right;color:black;font-weight:bold">Total: </td>
<td style="text-align:center;color:black;font-weight:bold"> <?=$newmed?></td>
<td style="text-align:center;color:black;font-weight:bold"> <?=$newfit?></td>
<td style="text-align:center;color:black;font-weight:bold"> <?=$newpending?></td>
<td style="text-align:center;color:black;font-weight:bold"> <?=$newunfit?></td>
<td style="text-align:center;color:black;font-weight:bold"> <?=$newtotalme?></td>
</tr>

</table>
<div style="clear:both;height:30px"></div>




<?php
include'accounts.php';
?>

</div>
</div>

</div>
</div>
</div>




</div>
</div>
</div>
</div>



</body>
<!--  /Body -->
</html>

