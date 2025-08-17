<table  cellspacing="0" id="example-checkbox1">
<thead>
<tr>
<th>View DEBIT/CREDIT</th>
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


<th></th>

</tr>
</thead>
<tbody>
<?php	

$appemp = mysql_query("SELECT * FROM employer
where  employer_id=".$_POST['fra']."");






while($rowman=mysql_fetch_array($appemp))
{

$aff = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed+fra_sent+fra_before) as singils ,applicant.* FROM applicant
WHERE applicant_employer=".$rowman["employer_id"]."

");
$rowre=mysql_fetch_array($aff);	

$app1 = mysql_query("SELECT SUM(amount) as USD ,emp_id,currency FROM email_address
WHERE emp_id=".$rowman["employer_id"]." AND currency='USD'");
$usd=mysql_fetch_array($app1);


$unfit = mysql_query("SELECT SUM(receivable) as receive ,emp_id,receivable FROM email_address4
WHERE emp_id='".$rowman["employer_id"]."'  AND currency='PHP'");
$unfitrow=mysql_fetch_array($unfit);

$CON = mysql_query("SELECT SUM(receivable) as receiveusd ,emp_id,receivable FROM email_address4
WHERE emp_id='".$rowman["employer_id"]."'  AND  currency='USD' ");
$contract=mysql_fetch_array($CON);

//$result1 = mysql_query("SELECT count(emp_id) as alls FROM  email_address2 where emp_id='".$row['employer_id']."'");
//$all = mysql_fetch_array($result1);


$col = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
WHERE emp_id='".$rowman["employer_id"]."'  AND  currency='USD' and status_collect=0");
$colusd=mysql_fetch_array($col);


$deduction = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
WHERE emp_id='".$rowman["employer_id"]."'  AND  currency='USD' and status_collect=1");
$deduction1=mysql_fetch_array($deduction);

$oldcollect = mysql_query("SELECT SUM(amount) as amountusd ,emp_id,amount FROM email_address6
WHERE emp_id='".$rowman["employer_id"]."'  AND  currency='USD' and status_collect=2");
$oldcollect1=mysql_fetch_array($oldcollect);


$expnese88= mysql_query("SELECT SUM(amount) as totalamount ,email_address.* FROM email_address
WHERE emp_id='".$rowman["employer_id"]."'  AND  currency='PHP' AND request_payment=3");
$expnese881=mysql_fetch_array($expnese88);




$oldme8 = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed+fra_sent+fra_before) AS amount,applicant.* FROM applicant
WHERE  applicant_employer=".$rowman['employer_id']." AND   applicant_status NOT IN (25,1,21,14,22)   ");
$oldme81=mysql_fetch_array($oldme8);


//$colS = mysql_query("SELECT SUM(amount) as amounPHP ,emp_id,amount FROM email_address6
//WHERE emp_id='".$row["employer_id"]."'  AND  currency='PHP' ");
//$colphp=mysql_fetch_array($colS);

$backout = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed+fra_sent+fra_before) AS amount,applicant.*  FROM applicant
WHERE applicant_status IN (25,1,21,14,22) AND applicant_employer=".$rowman['employer_id']." ");
$backout1=mysql_fetch_array($backout);


$repatresult = mysql_query("SELECT SUM(fra_ftw+fra_visa+fra_deployed+fra_sent+fra_before) AS amount,applicant.*,deployed.* FROM applicant
LEFT JOIN deployed
ON applicant.applicant_id = deployed.deployed_applicant
WHERE applicant_status  IN (22) AND applicant_employer=".$rowman['employer_id']."
AND deployed_date between date_sub(now(), interval 91 day)  AND date_add(now(), interval 1 day)");
$repatresult1=mysql_fetch_array($repatresult);

$chargejayven=$expnese881["totalamount"]/50;
$singil=$chargejayven+$oldcollect1["amountusd"]+$oldme81["amount"]+$repatresult1["amount"]+$backout1["amount"];
$alldeduct=$colusd["amountusd"]+$deduction1["amountusd"];
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
AND applicant_employer=".$_POST["fra"]." 
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
AND applicant_employer=".$_POST["fra"]." 
AND requirement_visa_stamp NOT IN ('1970-01-01','0000-00-00')
");
$visa = mysql_fetch_array($visas);




$sign = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  NOT IN (9,21,22,1,14,25,5)
AND applicant_employer=".$_POST["fra"]." 
AND  requirement_visa_stamp  IN ('0000-00-00','1970-01-01','')
AND requirement_contract NOT IN ('1970-01-01','0000-00-00') ");
$signrow = mysql_fetch_array($sign);


$nocontract = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  NOT IN (9,21,22,1,14,25,5)
AND applicant_employer=".$_POST["fra"]." 
AND requirement_contract  IN ('1970-01-01','0000-00-00')
 ");
$nocontract1 = mysql_fetch_array($nocontract);



$sign = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status  NOT IN (9,21,22,1,14,25,5)
AND applicant_employer=".$_POST["fra"]." 
AND  requirement_visa_stamp  IN ('0000-00-00','1970-01-01','')
AND requirement_contract NOT IN ('1970-01-01','0000-00-00') ");
$signrow = mysql_fetch_array($sign);


$onprocess = mysql_query("SELECT 
count(requirement_contract) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status    NOT IN (9,21,22,1,14,25,5)
AND applicant_employer=".$_POST["fra"]." ");
$onprocess1 = mysql_fetch_array($onprocess);



$oec = mysql_query("SELECT 
count(	requirement_oec_release_date) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status   NOT IN (9,21,22,1,14,25,5)
AND applicant_employer=".$_POST["fra"]." 
AND requirement_oec_release_date NOT IN ('1970-01-01','0000-00-00') ");
$oec1 = mysql_fetch_array($oec);



$fit2 = mysql_query("SELECT 
count(applicant_id) as alls,
applicant.applicant_id as adonis, applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant_status     IN (5)
AND applicant_employer=".$_POST["fra"]." ");
$fitt = mysql_fetch_array($fit2);


$percent=($deployedw['alls']+$onprocess1['alls']);
$percent1=($deployedw['alls']/$percent)*100;
$totalsaudi=($deployedw['alls']+$onprocess1['alls']);
echo'<tr>';


if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){
echo'<td>
<a href="employer_reports.php?emp_id='.$rowman["employer_id"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Debit/Credit</button></a>

<a href="employer_applicants.php?emp_id='.$rowman["employer_id"].'&&name='.$rowman["employer_name"].'" target="_blank">
<button type="button" class="btn btn-default btn-xs" style="font-size:11px;margin:3px">Applicants</button></a>


</td>';

}
Else{
echo'<td>
<a href="employer_reports.php?emp_id='.$rowman["employer_id"].'" target="_blank">
<button type="button" class="btn btn-danger btn-xs" style="font-size:11px;margin:3px">Debit/Credit</button></a>
</td>';	
}



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

echo'<td style="color:black;background:#F08080"> </td>';

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