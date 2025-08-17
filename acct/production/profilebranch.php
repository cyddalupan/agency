
<?php
include'inc/functions.php';
$app = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$_GET['agent']." ");
$row=mysql_fetch_array($app);
?>

<title><?=$row['agent_first']?></title>

<!-- Bootstrap -->
<link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">

<!-- Custom Theme Style -->
<link href="../build/css/custom.min.css" rel="stylesheet">

<style>
#wrapper{
border:0px solid black;
width:90%;
min-height:630px;
height:!important;
margin: 0 auto;	
padding:10px;
font-size:12px;
font-family:calibri;
}
.table-fixed td,th{
font-size:12px;
padding:6px;
color:black;
}gent_i
}gent_i


</style>

<body style="background:white">






<div id="wrapper">
</p>
<div>
<h1 style="font-size:30px;color:black;text-align:center;"><?=$row['agent_first']?> <?=$row['agent_last']?> </h1>

</div>

<div id="mainb" style="height:10px;"></div>

<div class="col-md-12 col-sm-12 col-xs-12">

<?php
$allapplicant = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."'");
$allapplicant1 = mysql_fetch_array($allapplicant);


$onprocess = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."' 
AND applicant_status IN (8,12,4,3,2,5)
");
$onprocess1 = mysql_fetch_array($onprocess);

$deployed = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."' 
AND applicant_status IN (9)
");
$deployed1 = mysql_fetch_array($deployed);


$repat = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."' 
AND applicant_status IN (22)
");
$repat1 = mysql_fetch_array($repat);


$pass = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."' 
AND applicant_status IN (15,0,10)
");
$pass1 = mysql_fetch_array($pass);


$not = mysql_query("SELECT count(applicant_source) as countme FROM  applicant where applicant_source='".$_GET['agent']."' 
AND applicant_status IN (1,14,21,7)
");
$not1 = mysql_fetch_array($not);


?>


<ul class="stats-overview">
<li>
<span class="name">Total Applicants</span>
<span class="value text-success"><?=$allapplicant1['countme']?>  </span>
</li>

<li>
<span class="name">Available / Passporting</span>
<span class="value text-success"><?=$pass1['countme']?>  </span>
</li>

<li>   
<span class="name"> Backout/Cancelled/Unfit</span>
<span class="value text-success"> <?=$not1['countme']?> </span>
</li>


</ul>



<ul class="stats-overview">
<li>   
<span class="name"> On Process Applicants</span>
<span class="value text-success"> <?=$onprocess1['countme']?> </span>
</li>

<li class="hidden-phone">
<span class="name"> Deployed Applicants</span>
<span class="value text-success"> <?=$deployed1['countme']?> </span>
</li>

<li>
<span class="name"># Repat Applicants</span>
<span class="value text-success"> <?=$repat1['countme']?> </span>
</li>

</ul>









<br />

<div id="mainb" style="height:10px;"></div>
</div>







<div class="col-sm-2 invoice-col pull-right" style="">
<a href="exp_agent.php?agent=<?=$_GET['agent']?>"><button type="button" class="btn btn-round btn-DANGER pull-right btn-sm">Add Charge / Repat</button></a>
<a href="exp_agent1.php?agent=<?=$_GET['agent']?>"><button type="button" class="btn btn-round btn-SUCcESS pull-right btn-sm">Add OLD Commission</button></a>
</div>





<div id="home" class="tab-pane fade in active">
<div class="col-xs-12 table">


<h3 style="color:Blue">Old Balance (DEBIT ) <small style="color:black">   </small></h3>
<table class="table table-fixed">
<thead>
<tr>
<th style="width: 20%">Applicant</th>
<th>Date</th>
<th style="width: 20%">Agent</th>
<th style="">Type</th>
<th style="width: 20%">Description</th>
<th style="text-align:right"></th>
<th style="text-align:right">Currency</th>
<th style="text-align:right">Amount</th>
</tr>
</thead>
<tbody>
<?PHP
$old = mysql_query("SELECT * FROM email_address4 where  agent_id=".$_GET['agent']."
 AND status_me=1
order by date_transaction desc");
while($old1 =mysql_fetch_array($old ))
{
$app1old = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$old1['agent_id']." ");
$row1old=mysql_fetch_array($app1old);

$app12old = mysql_query("SELECT * FROM applicant where applicant_id=".$old1['app_id']." ");
$app12old=mysql_fetch_array($app12old);	


$oldtotal=($old1['receivable']+$old1["othercharge"])+$oldtotal;
if($old1["status_paid"]==0){$status1='Not Paid';}
if($old1["status_paid"]==1){$status1='Paid';}


echo'<tr>';
echo'<td>'.$app12old["applicant_first"].' '.$app12old["applicant_last"].'</td>';			
echo'<td>'.$old1["date_transaction"].'</td>';
echo'<td>'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$old1["exp_type"].'</td>';
echo'<td>'.$old1["findings"].'</td>';
echo'<td style="text-align:right">'.$old1["type_status"].'</td>';

echo'<td style="text-align:right">'.$old1["currency"].'</td>';
echo'<td style="text-align:right">'.$old1["receivable"].'</td>';

echo'</tr>';
}
?>	


<tr>
<td colspan="12" STYLE="text-align:right;FONT-weight:bold;font-size:16px">TOTAL : <?php echo number_format($oldtotal,2);?></td>
</tr>	
</tbody>
</table>

</br></br>





<div id="home" class="tab-pane fade in active">
<div class="col-xs-12 table">
</br></br>

<h3 style="color:blue">Released Commission (DEBIT)</h5>
<table class="table table-fixed">
<thead>
<tr>
<th>Date Transaction</th>
<th>VOUCHER NO.</th>
<th style="width: 26%">Applicant</th>
<th>Method</th>
<th>Account</th>
<th >Description</th>
<th>Currency</th>
<th>Amount</th>
</tr>

</thead>
<tbody>
<?PHP
$app = mysql_query("SELECT * FROM email_address where  agent_id=".$_GET['agent']."
AND request_payment=3
AND account='Commission'
order by date_ad desc");
while($row=mysql_fetch_array($app))
{
$totalamountUSD=$row['amount']+$totalamountUSD;
echo'<tr>';
echo'<td>'; echo date('Y-M-d', strtotime($row['date_ad']));echo'</td>';
echo'<td>'.$row["ref"].'</td>';
echo'<td>';
$list = mysql_query("SELECT * FROM e_liq where l_status=0
AND l_e_d=".$row['e_id']."
and l_type='Agent'
order by l_id desc");
while($select=mysql_fetch_array($list))

{
$app1 = mysql_query("SELECT * FROM applicant where applicant_id=".$select['liq_app']." ");
$row1=mysql_fetch_array($app1);
echo'<p><div  style="width:210px;border:0px solid black;float:left">'.$row1["applicant_first"].' '.$row1["applicant_last"].' </div>
<div style="width:100px;float:left;color:blue;FONT-SIZE:11PX">'.$select2222["l_currency"].''.$select["l_amount"].'  <small>('.$select["type_applicant"].') </small></div></p>
';
}
echo' </td>';
echo'<td>'.$row["method"].'</td>';
echo'<td>'.$row["account"].'</td>';
echo'<td>'.$row["description"].'</td>';
echo'<td  style="text-align:right">'.$row["currency"].'</td>';
echo'<td  style="text-align:right">'.$row["amount"].'</td>';
echo'</tr>';

}
?>	
<tr>
<td style="color:black;text-align:right;font-size:16px;font-weight:bold" colspan="9">TOTAL: <?php echo number_format($totalamountUSD,2);?></td>
</tr>	
</tbody>
</table>
</div>
</div>











<DIV style="page-break-after:always"></DIV>




<div class="col-xs-12 table">
</br></br>
<h3 style="color:blue">Payables <small style="color:black"> (DEBIT cash advance,repat,backout ,unfit)  </small></h3>
<table class="table table-fixed">
<thead>
<tr>
<th style="width: 20%">Applicant</th>
<th>Date</th>
<th style="width: 20%">Agent</th>
<th style="">Type</th>
<th style="width: 20%">Description</th>
<th style="text-align:right">Charge To</th>
<th style="text-align:right">Currency</th>
<th style="text-align:right">Amount</th>
</tr>
</thead>
<tbody>
<?PHP
$app11 = mysql_query("SELECT * FROM email_address4 where  agent_id=".$_GET['agent']."
 AND status_me=0
order by date_transaction desc");
while($row2=mysql_fetch_array($app11))
{
$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row2['agent_id']." ");
$row1=mysql_fetch_array($app1);

$app12 = mysql_query("SELECT * FROM applicant where applicant_id=".$row2['app_id']." ");
$row12=mysql_fetch_array($app12);	


$totalamountex=($row2['receivable']+$row2["othercharge"])+$totalamountex;
if($row2["status_paid"]==0){$status1='Not Paid';}
if($row2["status_paid"]==1){$status1='Paid';}


echo'<tr>';
echo'<td>'.$row12["applicant_first"].' '.$row12["applicant_last"].'</td>';			
echo'<td>'.$row2["date_transaction"].'</td>';
echo'<td>'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$row2["exp_type"].'</td>';
echo'<td>'.$row2["findings"].'</td>';
echo'<td style="text-align:right">'.$row2["type_status"].'</td>';

echo'<td style="text-align:right">'.$row2["currency"].'</td>';
echo'<td style="text-align:right">'.$row2["receivable"].'</td>';

echo'</tr>';
}
?>	
	
</tbody>
</table>







<div class="col-xs-12 table">

<h5 style="color:BLACK">Total Cash Advance </h5>
<table class="table table-fixed">
<thead>
<tr>
<th STYLE="width:1%"></th>
<th STYLE="width:2%">#</th>
<th STYLE="width:10%">DATE</th>
<th STYLE="width:10%">VOUCHER NO.</th>
<th STYLE="width:10%">Applicant</th>
<th STYLE="width:5%">Type</th>
<th STYLE="width:10%">Type of Expenses</th>
<th STYLE="width:15%">Description</th>
<th STYLE="width:5%">CURRENCY</th>
<th STYLE="width:5%">AMOUNT</th>
</tr>
</thead>
<tbody>
<?PHP



$app33 = mysql_query("SELECT * FROM email_address
where account  IN ('Cash Advance agent')
AND agent_id=".$_GET['agent']."
order by date_ad,e_id asc");

while($row222=mysql_fetch_array($app33))
{
$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row222['agent_id']." ");
$row1=mysql_fetch_array($app1);

$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$row222['app_id']." ");
$rowp=mysql_fetch_array($applicant);

$cashadvance=$row222['amount']+$cashadvance;



echo'<tr>';
echo'<td></td>';
echo'<td>000'.$row222["e_id"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row222['date_ad']));echo'</td>';

echo'<td>'.$row222["ref"].'</td>';
echo'<td>';
$listrrrr = mysql_query("SELECT * FROM e_liq where l_status=0
AND l_e_d=".$row222['e_id']."
and l_type='Agent'
order by l_id desc");
while($select2222=mysql_fetch_array($listrrrr))

{
$app1222 = mysql_query("SELECT * FROM applicant where applicant_id=".$select2222['liq_app']." ");
$row122=mysql_fetch_array($app1222);
echo'<p><div  style="width:210px;border:0px solid black;float:left">'.$row122["applicant_first"].' '.$row122["applicant_last"].' </div>
<div style="width:100px;float:left;color:blue;FONT-SIZE:11PX">'.$select2222["l_currency"].''.$select2222["l_amount"].' </div> <small>('.$select2222["type_applicant"].') </small></p>
';
}
echo' </td>';
echo'<td>'.$row222["method"].'</td>';
echo'<td>'.$row222["account"].'</td>';
echo'<td>'.$row222["description"].'</td>';
echo'<td>'.$row222["currency"].'</td>';
echo'<td>'.$row222['amount'].'</td>';
echo'</tr>';

}
$totalcharge1=$totalamountex+$cashadvance;

?>	
<tr>
<td style="color:black;text-align:right;font-size:16px;font-weight:bold" colspan="11">TOTAL:<?php echo number_format($totalcharge1,2);?></td>
</tr>	
</tbody>
</table>




<div id="home" class="tab-pane fade in active">
<div id="home" class="tab-pane fade in active">
<div class="col-xs-12 table">
<h3 style="color:blue">Total Deduction  (DEBIT)<small style="color:black">  </small></h3>
<table class="table table-fixed">
<thead>
<tr>
<th>#</th>
<th>DATE</th>
<th>Agent</th>
<th>Remarks</th>
<th>Amount</th>

</tr>
</thead>
<tbody>
<?PHP
$deduction = mysql_query("SELECT * FROM deduction
where agent_id=".$_GET['agent']."
ORDER BY deduction_id desc");

while($deduction1=mysql_fetch_array($deduction))
{


$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$deduction1['agent_id']." ");
$row1=mysql_fetch_array($app1);

$deductiontotal=$deduction1['deduction_amount']+$deductiontotal;



echo'<tr>';
echo "<td>".$deduction1['deduction_id']."</td> ";
echo'<td>'; echo date('Y-M-d', strtotime($deduction1['deduction_date']));echo'</td>';
echo'<td style="font-size:10px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$deduction1["deduction_remarks"].'</td>';
echo'<td> '.number_format($deduction1["deduction_amount"], 2).'</td>';
echo'</tr>';

}
?>	
<tr>
<td style="color:black;text-align:right;font-size:16px;font-weight:bold" colspan="11"><?php echo number_format($deductiontotal,2);?></td>
</tr>	
</tbody>
</table>
</div>
</div>



</div>
</div>







<h3 style="color:red">Total Commission (CREDIT)</h5>
<table class="table table-fixed">
<thead>
<tr>
<th STYLE="width:2%"></th>
<th>DATE</th>
<th>Name</th>
<th>Source</th>
<th>Passport</th>
<th>Country</th>
<th>Principal</th>
<th>Status</th>
<th>FTW</th>
<th>Sign Contract</th>
<th>Deployed</th>
<th>Total</th>
<th>Amount Paid</th>
</tr>
</thead>
<tbody>

<?php	
//AND collect_status=1
$applicantftw = mysql_query("SELECT * FROM applicant
WHERE applicant_source=".$_GET['agent']." AND ( agent_ftw!=0 OR agent_contract!=0 OR agent_deployed!=0)
  
");

while($applicantftw1=mysql_fetch_array($applicantftw))
{
$status = mysql_query("SELECT * FROM statuses where number=".$applicantftw1['applicant_status']." ");
$srow=mysql_fetch_array($status);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$applicantftw1['applicant_source']." ");
$row1=mysql_fetch_array($app1);

$emp = mysql_query("SELECT * FROM employer where employer_id=".$applicantftw1['applicant_employer']." ");
$emprow=mysql_fetch_array($emp);

$passport = mysql_query("SELECT * FROM applicant_passport where passport_applicant=".$applicantftw1['applicant_id']." ");
$passportrow=mysql_fetch_array($passport);

$country = mysql_query("SELECT * FROM country where country_id=".$applicantftw1['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);

$LIQ = mysql_query("SELECT sum(amount) as cashfund FROM  email_address6 
where  app_id=".$applicantftw1['applicant_id']."");
$cashliq=mysql_fetch_array($LIQ);

//TOTAL PAID

$paidamount = mysql_query("SELECT SUM(l_amount) AS totalpaidme, e_liq.*, email_address.* FROM  e_liq 

LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id


where  liq_app=".$applicantftw1['applicant_id']."
AND l_status=0 
AND request_payment=3
");
$paidamount1=mysql_fetch_array($paidamount);

//total paids
$metotal=$paidamount1['totalpaidme']+$metotal;

$balancecollection=$applicantftw1['agentcom']-$cashliq['cashfund'];

if($applicantftw1['applicant_paid1']!=1)
{ $paids='UNPAID'; }  

if($applicantftw1['applicant_paid1']==1)
{ $paids='PAID'; } 


$totalnew1=($applicantftw1["agent_ftw"]+$applicantftw1["agent_contract"]+$applicantftw1["agent_deployed"]);
$totalnew=($applicantftw1["agent_ftw"]+$applicantftw1["agent_contract"]+$applicantftw1["agent_deployed"])+$totalnew;


echo'<tr>';
echo "<td><input type='hidden' name='applicant_id[]' value='".$applicantftw1['applicant_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($applicantftw1['applicant_date_applied']));echo'</td>';
echo'<td><b>'.$applicantftw1["applicant_first"].' '.$applicantftw1["applicant_last"].'</b></td>';
echo'<td>'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$passportrow["passport_number"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';
echo'<td>'.$emprow["employer_name"].'</td>';
echo "<td>".$paids."</td> ";
echo "<td> ".$applicantftw1['agent_ftw']."</td> ";
echo "<td> ".$applicantftw1['agent_contract']."</td> ";
echo "<td> ".$applicantftw1['agent_deployed']."</td> ";
echo "<td> ".$totalnew1."</td> ";
echo "<td> ".$paidamount1['totalpaidme']."</td> ";


}
?>
<tr>
<td colspan="12" STYLE="text-align:right;FONT-weight:bold;font-size:16px">TOTAL COMMISSION: <?php echo number_format($totalnew,2);?></td>
<td colspan="1" STYLE="text-align:right;FONT-weight:bold;font-size:16px">TOTAL PAID:   <?php echo number_format($metotal,2);?></td>

</tr>




</tbody>
</table>
</div>
</div>




















<div id="home" class="tab-pane fade in active">
<div class="col-xs-12 table">
<h3 style="color:red">Total Backout/Cancelled/UNFIT (CREDIT)</h5>
<table class="table table-fixed">
<thead>
<tr>
<th STYLE="width:2%"></th>
<th>DATE</th>
<th>Name</th>
<th>Source</th>
<th>Passport</th>
<th>Country</th>
<th>Principal</th>
<th>Status</th>
<th><div  style="width:150px;border:0px solid black;float:left">Charges</div>  Amount</th>
<th>Charge Commission</th>
</tr>
</thead>
<tbody>

<?php	
//AND collect_status=1
$backout = mysql_query("SELECT * FROM applicant
WHERE applicant_status IN (25,1,21,14,22) AND applicant_source=".$_GET['agent']."   ");

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

$passport = mysql_query("SELECT * FROM applicant_passport where passport_applicant=".$backout1['applicant_id']." ");
$passportrow=mysql_fetch_array($passport);

$country = mysql_query("SELECT * FROM country where country_id=".$backout1['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);

$LIQ = mysql_query("SELECT sum(amount) as cashfund FROM  email_address6 
where  app_id=".$backout1['applicant_id']."");
$cashliq=mysql_fetch_array($LIQ);



$chargesmatic = mysql_query("SELECT * FROM applicant_charges where charge_id=".$backout1['applicant_preferred_country']." 
AND  charge_country=".$row['applicant_preferred_country']." 
 ");
$chargesmatic=mysql_fetch_array($chargesmatic);


if($row1cert['certificate_medical_result']!=''){
$medical=",'MEDICAL'";
}
if($row1cert['certificate_medical_result']==''){ $medical='';  }

if($row1cert['swab']!=''){
$swab=",'SWAB TEST'";
}
else { $swab='';  }

if($row1cert['certificate_mmr']!=''){
$vaccine=",'VACCINE'";
}
else { $vaccine='';  }

if($row1cert['insurance_no']!=''){
$INSURANCE=",'INSURANCE'";
}
else { $INSURANCE='';  }


IF($row1cert['certificate_pt_result_date']=='1970-01-01' || $row1cert['certificate_pt_result_date']=='0000-00-00' || $row1cert['certificate_pt_result_date']=='' ) {
 $certificate_pt_result_date=''; 
}
else{ 
$certificate_pt_result_date=",'PREGNANCY TEST'";
}


IF($row2['requirement_visa_stamp']=='1970-01-01' || $row2['requirement_visa_stamp']=='0000-00-00' || $row2['requirement_visa_stamp']=='' ) {
 $requirement_visa_stamp=''; 
}
else{ 
$requirement_visa_stamp=",'VISA STAMPING'";
}

IF($row2['requirement_oec_release_date']=='1970-01-01' || $row2['requirement_oec_release_date']=='0000-00-00' || $row2['requirement_oec_release_date']=='' ) {
 $requirement_oec_release_date=''; 
}
else{ 
$requirement_oec_release_date=",'OEC'";
}

$totalcommission=($backout1["agent_ftw"]+$backout1["agent_contract"]+$backout1["agent_deployed"])+$totalcommission;


if($backout1["applicant_status"]==1){$anoba='Cancelled';}
if($backout1["applicant_status"]==25){$anoba='Backout';}
if($backout1["applicant_status"]==21){$anoba='Unfit';}
if($backout1["applicant_status"]==14){$anoba='A to A';}
if($backout1["applicant_status"]==22){$anoba='Repat';}
echo'<tr>';
echo "<td><input type='hidden' name='applicant_id[]' value='".$backout1['applicant_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($backout1['applicant_date_applied']));echo'</td>';
echo'<td><b>'.$backout1["applicant_first"].' '.$backout1["applicant_last"].'</b></td>';
echo'<td>'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$passportrow["passport_number"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';
echo'<td>'.$emprow["employer_name"].'</td>';
echo'<td>'.$anoba.'</td>';

			echo'<td>';
			$charger1 = mysql_query("SELECT * FROM applicant_charges where charge_country=".$backout1['applicant_preferred_country']." AND charge_name  IN ('meme' ".$medical."".$swab."".$vaccine."".$INSURANCE."
			".$INSURANCE."".$certificate_pt_result_date."".$requirement_visa_stamp."".$requirement_oec_release_date.") ");
			while($select=mysql_fetch_array($charger1))

			{
			echo'<p><div  style="width:150px;border:0px solid black;float:left">'.$select["charge_name"].'</div><b>'.$select["charge_amount"].'</b></p>';
			$charge1all=$select["charge_amount"]+$charge1all;
			}
			echo' </td>';
}
echo'<td>'.$totalcommission.'</td>';
$total_com=$charge1all+$totalcommission;
?>
<tr>
<td colspan="12" STYLE="text-align:right;FONT-weight:bold;font-size:16px">TOTAL : <?php echo number_format($total_com,2);?></td>

</tr>




</tbody>
</table>
</div>
</div>




















<div id="home" class="tab-pane fade in active">
<div class="col-xs-12 table">
<h3 style="color:red">Total Collection  (CREDIT)<small style="color:black">  </small></h3>
<table class="table table-fixed">
<thead>
<tr>
<th STYLE="width:1%"></th>
<th STYLE="width:2%">#</th>
<th STYLE="width:10%">DATE</th>

<th>OR #</th>
<th>Applicant</th>
<th>Particular</th>
<th>account</th>

<th>Paid Through </th>
<th>Remarks </th>
<th>Currency</th>
<th>Amount</th>
</tr>
</thead>
<tbody>
<?PHP
$colec = mysql_query("SELECT * FROM email_address6
where agent_id=".$_GET['agent']."
ORDER BY tr_id desc");

while($rcolec=mysql_fetch_array($colec))
{


$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$row222['app_id']." ");
$rowp=mysql_fetch_array($applicant);

$totalcollections=$rcolec['amount']+$totalcollections;



echo'<tr>';
echo'<td></td>';
echo'<td style="font-size:1px">'.$rcolec["tr_id"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($rcolec['date']));echo'</td>';
echo'<td>'; echo date('md', strtotime($rcolec['date']));echo'</td>';
echo'<td>
'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$rcolec["particular"].'</td>';
echo'<td>'.$rcolec["account"].'</td>';
echo'<td>'.$rcolec["source_m"].'</td>';
echo'<td>'.$rcolec["particular"].'</td>';
echo'<td>'.$rcolec["currency"].'</td>';
echo'<td>'.$rcolec["amount"].'</td>';

echo'</tr>';

}
?>	
<tr>
<td style="color:black;text-align:right;font-size:16px;font-weight:bold" colspan="11"><?php echo number_format($totalcollections,2);?></td>
</tr>	
</tbody>
</table>
</div>
</div>

</div>
</div>











<?php
$debit=$oldtotal+$totalamountUSD+$totalcharge1+$deductiontotal;
$credit=$totalnew+$totalcollections+$total_com;
$lahatna=$debit-$credit;
?>
<div style='page-break-before: always;'></div>

<div id="wrapper">
<h2 style="color:BLACK;font-size:20px;float:right"><u>SUMMARY REPORTS </u></h2>
<div style="clear:both;"></div>
<table class="table table-fixed" style="width:500px;float:right">
<tbody>
<TR>
<td style="font-size:16px"><b>Total Debit</b></td>
<td  style="font-size:16px"><?php echo' '.number_format($debit, 2).''; ?></td>
</tr>

<TR>
<td style="font-size:16px"><b>Total Credit</b></td>
<td  style="font-size:16px"><?php echo' '.number_format($credit, 2).''; ?></td>
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