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
<?php include'menu.php';?>


<ul id="menu">
<li><a   class="active" href="dashboard.php">Dashboard</a></li>
<li><a href="preselected.php"> Pre Applicants</a></li>
<li><a href="passporting.php">Passporting</a></li>
<li><a href="line.php">Line Up</a></li>
<li><a href="med.php">Medical</a></li>
<li><a   href="all-app.php">On Process</a></li>
<li><a   href="tesdanew.php">TESDA</a></li>
<li><a   href="musaned.php">MUSANED</a></li>
<li><a   href="owwanew.php">OWWA</a></li>
<li><a   href="stampnew.php">Visa Stamp</a></li>
<li><a   href="oecnew.php">OEC</a></li>
<li><a href="booking.php">For Deployment</a></li>
<li><a href="deployednew.php">Deployed</a></li>
<li><a href="repatnew.php">Repat</a></li>

</ul>


<div style="clear:both;height:10px"></div>

<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">


<div style="clear:both"></div>

<?php
//include'schedule.php';
echo'<div style="clear:both;height:20px"></div>';

?>
<?php
include'accounts.php';
?>

<style>
#example-checkbox td{
text-align:center;
font-size:15px;	
}
</style>

<div style="clear:both;height:30px"></div>


<h2 style="color:black">FRA Summary Reports</h2>
<table  cellspacing="0" id="example-checkbox" style="border:1px solid black">
<thead>
<tr>
<th>View DEBIT/CREDIT</th>
<th STYLE="width:2%">Code #</th>
<th >Principal</th>

<th ># Deployed</th>
<th ># Oec</th>
<th ># Visa Stamp</th>
<th ># w/ Contract</th>
<th >Pre Selected</th>
<th >On Process</th>

<th>%</th>

</tr>
</thead>
<tbody>
<?php	

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
where applicant_status  NOT IN (9)
AND applicant_employer=".$row["employer_id"]." 
AND requirement_visa_stamp NOT IN ('1970-01-01','0000-00-00')
");
$visa = mysql_fetch_array($visas);




$sign = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  NOT IN (9)
AND applicant_employer=".$row["employer_id"]." 
AND requirement_contract NOT IN ('1970-01-01','0000-00-00') ");
$signrow = mysql_fetch_array($sign);



$onprocess = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  IN (4,2,3,5,8,12,26,27,28,29,30,31,32,33)
AND applicant_employer=".$row["employer_id"]." ");
$onprocess1 = mysql_fetch_array($onprocess);



$oec = mysql_query("SELECT 
count(	requirement_oec_release_date) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  IN (4,2,3,5,8,12,26,27,28,29,30,31,32,33)
AND applicant_employer=".$row["employer_id"]." 
AND requirement_oec_release_date NOT IN ('1970-01-01','0000-00-00') ");
$oec1 = mysql_fetch_array($oec);


$fit2 =mysql_query("SELECT 
count(DISTINCT applicants_logs_view.log_applicant) as alls,
applicant.applicant_id as adonis,applicant.*,applicants_logs_view.*
FROM applicants_logs_view
LEFT JOIN applicant
ON applicants_logs_view.log_applicant = applicant.applicant_id
where
log_status=5
AND log_employer=".$row["employer_id"]."
order by log_date desc");
$fitt = mysql_fetch_array($fit2);
//log_date between date_sub(now(), interval 200 day)  AND date_add(now(), interval 1 day)




$percent=($deployedw['alls']+$onprocess1['alls']);
$percent1=($deployedw['alls']/$percent)*100;

echo'<tr>';


if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){
echo'<td>
<a href="employer_reports.php?emp_id='.$row["employer_id"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Debit/Credit</button></a>

</td>';

}
Else{
echo'<td>
<a href="employer_reports.php?emp_id='.$row["employer_id"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Debit/Credit</button></a>
</td>';	
}



echo'<td>000'.$row["employer_id"].'</td>';
echo'<td><b>'.$row["employer_name"].'</b></td>';


echo'<td><b>'.$deployedw['alls'].'</b></td>';
echo'<td><b>'.$oec1['alls'].'</b></td>';
echo'<td><b>'.$visa['alls'].'</b></td>';
echo'<td><b>'.$signrow['alls'].'</b></td>';
echo'<td><b>'.$fitt['alls'].'</b></td>';
echo'<td style="text-align:center;color:black;font-weight:bold"><b>'.$onprocess1['alls'].'</b></td>';



if($percent1!=0){ echo'<td style="color:blue;font-weight:bold">'.number_format($percent1,1).' %</td>'; }
if($percent1==0){ echo'<td style="color:blue;font-weight:bold">--</td>'; }



$deployedall=$deployedw['alls']+$deployedall;
$oecall=$oec1['alls']+$oecall;
$visaall=$visa['alls']+$visaall;
$signall=$signrow['alls']+$signall;
$fitall=$fitt['alls']+$fitall;
$onprocessall=$onprocess1['alls']+$onprocessall;
$compute=$onprocessall+$deployedall;
$percentall=($deployedall/$compute)*100;
$grandbalance=$balancesheet+$grandbalance;

echo'</tr>';
}
?>
</tbody>
<tr>
<td colspan="3" style="text-align:right;color:black">Total: </td>
<td style="text-align:center;color:black"> <?=$deployedall?></td>
<td style="text-align:center;color:black"> <?=$oecall?></td>
<td style="text-align:center;color:black"> <?=$visaall?></td>
<td style="text-align:center;color:black"> <?=$signall?></td>
<td style="text-align:center;color:black"> <?=$fitall?></td>
<td style="text-align:center;color:black;font-weight:bold"> <?=$onprocessall?></td>
<td  style="text-align:center;color:black"> <?=number_format($percentall,1)?>%</td>

</tr>
</table>





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

