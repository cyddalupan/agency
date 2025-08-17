
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
table{
width:80%;    
}

</style>

<body style="background:white">






<div id="wrapper" style="width:90%">


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


<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:14px;padding:5px">Save my work</a>





<table class="table table-fixed" id="example-checkbox">
<tr>
    
  <td colspn="8"> <h1 style="font-size:30px;color:black;text-align:center;"><?=$row['agent_first']?> <?=$row['agent_last']?> </h1></td>
</tr> 
<tr>
    <td colspn="8"><h3 style="color:Blue">Old Balance (DEBIT ) <small style="color:black">   </small></h3></td>
</tr>    
    
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
<td colspan="8" STYLE="text-align:right;FONT-weight:bold;font-size:16px;background:yellow">TOTAL : <?php echo number_format($oldtotal,2);?></td>
</tr>





<tr>
    <td colspn="8"><h3 style="color:Blue">Released Commission (DEBIT) <small style="color:black">   </small></h3></td>
</tr>  
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
<td style="color:black;text-align:right;font-size:16px;font-weight:bold;background:yellow" colspan="9">TOTAL: <?php echo number_format($totalamountUSD,2);?></td>
</tr>	












    
<tr>
    <td colspn="8"><h3 style="color:Blue">Payables</h3> (DEBIT cash advance,repat,backout ,unfit)<small style="color:black">   </small></h3></td>
</tr>      
    
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
	







    
<tr>
    <td colspn="8"><h3 style="color:Blue"></h3>Total Cash Advance  </small></h3></td>
</tr>       
    
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
<td style="color:black;text-align:right;font-size:16px;font-weight:bold;background:yellow" colspan="11">TOTAL:<?php echo number_format($totalcharge1,2);?></td>
</tr>	





    
<tr>
    <td colspn="8"><h3 style="color:blue">Total Deduction  (DEBIT)<small style="color:black">  </small></h3></td>
</tr>     
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
<td style="color:black;text-align:right;font-size:16px;font-weight:bold;background:yellow" colspan="11"><?php echo number_format($deductiontotal,2);?></td>
</tr>





<tr>
    <td colspn="8"><h3 style="color:blue">Charge Liquidation<small style="color:black">  </small></h3></td>
</tr> 


<th STYLE="width:2%">Status</th>

<th STYLE="width:3%">DATE</th>
<th STYLE="width:5%">Agent </th>
<th STYLE="width:5%">Applicant </th>
<th STYLE="width:5%">Req.By</th>
<th STYLE="width:5%">Account Type</th>
<th STYLE="width:5%">Category</th>
<th STYLE="width:5%">Description</th>
<th STYLE="width:5%">Amount</th>

</tr>


<?PHP

$appliq = mysql_query("SELECT * FROM e_liq 
LEFT JOIN  applicant
ON e_liq.liq_app = applicant.applicant_id

LEFT JOIN  	recruitment_agent
ON applicant.applicant_source =recruitment_agent.agent_id

where l_status=0
AND applicant_source=".$_GET['agent']."
and type_applicant not IN ('PASSPORT','SIGN CONTRACT','FTW 1','FTW 2','FTW 3','FTW FULL', 'DEPLOYED 1',
'DEPLOYED 2','DEPLOYED 3','DEPLOYED FULL','PASSPORT','FTW FULL PAYMENT','FTW- PARTIAL','DEPLOYED','AGENT PAYMENT','Commission','FTW- FULL PAYMENT','OFFICE DEDUCTION') and l_type IN ('Agent')
order by l_id desc");

while($appliq1=mysql_fetch_array($appliq))
{

$applicant = mysql_query("SELECT applicant_id,applicant_first,applicant_last,applicant_middle,applicant_source FROM applicant where applicant_id=".$appliq1['liq_app']." ");
$rowp=mysql_fetch_array($applicant);


$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$rowp['applicant_source']." ");
$agent1=mysql_fetch_array($agent);

$USER = mysql_query("SELECT * FROM  user where user_id=".$appliq1['l_staff_id']." ");
$rowu=mysql_fetch_array($USER);


$expenses = mysql_query("SELECT * FROM  email_address where e_id=".$appliq1['l_e_d']." ");
$expenses1=mysql_fetch_array($expenses);


$total1=$appliq1['$appliq1']+$total1;
$amt = number_format($appliq1["l_amount"], 2);
if($expenses1["request_payment"]==0){$request='Pending';$rcolor='black';}
if($expenses1["request_payment"]==1){$request='Approved';$rcolor='blue';}
if($expenses1["request_payment"]==2){$request='For Release';$rcolor='orange';}
if($expenses1["request_payment"]==3){$request='Released';$rcolor='green';}
if($expenses1["request_payment"]==4){$request='Decline';$rcolor='red';}
if($expenses1["request_payment"]==5){$request='Return to maker';$rcolor='red';}

echo'<tr>';

echo'<td style="font-size:12px;color:white;">
<i style="background:'.$rcolor.';padding:2px;margin:2px">'.$request.'</i>
</td>';

echo'<td>'; echo date('Y-M-d', strtotime($appliq1['l_date']));echo'</td>';
echo'<td>'.$agent1["agent_first"].' '.$agent1["agent_last"].'</td>';
echo'<td>'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$row['requestby'].'</td>';
echo'<td>'.$appliq1["type_applicant"].'</td>';
echo'<td>'.$appliq1["l_type"].'</td>';
echo'<td>'.$appliq1["l_remarks"].'</td>';
echo'<td>'.$appliq1["l_amount"].'</td>';


echo'</tr>';


$liqdeductiontotal=$appliq1['l_amount']+$liqdeductiontotal;

}
?>	
<tr>
<td style="color:black;text-align:right;font-size:16px;font-weight:bold;background:yellow" colspan="11"><?php echo number_format($liqdeductiontotal,2);?></td>
</tr>









<tr>
    <td colspn="8"><h3 style="color:blue">Total Backout/Cancelled/UNFIT (DEBIT)<small style="color:black">  </small></h3></td>
</tr> 
    
    
    
<tr>
<th STYLE="width:2%"></th>
<th>DATE</th>
<th>Name</th>
<th>Source</th>
<th>Passport</th>
<th>Country</th>
<th>Principal</th>
<th>Status</th>
<th>Charge Commission</th>
<th><div  style="width:150px;border:0px solid black;float:left">Charges</div>  Amount</th>

</tr>
</thead>
<tbody>

<?php	
//AND collect_status=1
$backout = mysql_query("SELECT * FROM applicant
WHERE applicant_status IN (25,1,21,14,22) AND applicant_source=".$_GET['agent']."   ");

while($backout1=mysql_fetch_array($backout))
{
    
$LIQ111 = mysql_query("SELECT sum(l_amount) as totalme, e_liq.* FROM  e_liq 
where  liq_app=".$backout1['applicant_id']." AND type_applicant IN ('PASSPORT','FTW- PARTIAL','FTW- FULL PAYMENT','DEPLOYED','FULL PAYMENT')
and l_status=0");
$cashliq999=mysql_fetch_array($LIQ111);
$totalcommission=$cashliq999['totalme']+$totalcommission;
    
    
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
echo'<td>'.$cashliq999['totalme'].'</td>';


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




$total_com=$charge1all+$totalcommission;
?>
<tr>
<td colspan="10" STYLE="text-align:right;FONT-weight:bold;font-size:16px;background:yellow">TOTAL : <?php echo number_format($total_com,2);?></td>

</tr>






















    
<tr>
    <td colspn="8"><h3 style="color:red">Total Commission (CREDIT)<small style="color:black">  </small></h3></td>
</tr>     
    
<tr>
<th STYLE="width:2%"></th>
<th>DATE</th>
<th>Name</th>
<th>Source</th>
<th>Passport</th>
<th>Country</th>
<th>Principal</th>
<th>Status</th>
<th>PPT</th>
<th>FTW</th>
<th>Sign Contract</th>
<th>Deployed</th>
<th>Total</th>
<th>Deduction</th>
<th>Amount Paid</th>
<th style="color:Red">Balance</th>
</tr>
</thead>
<tbody>

<?php	
//AND collect_status=1
$applicantftw = mysql_query("SELECT * FROM applicant
WHERE applicant_source=".$_GET['agent']." AND ( agent_ftw!=0 OR agent_contract!=0 OR agent_deployed!=0 OR agent_ppt!=0)
 AND applicant_status NOT IN (25,1,21,14,22) 
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
 AND l_type  IN ('Agent')
");
$paidamount1=mysql_fetch_array($paidamount);

//total paids
$metotal=$paidamount1['totalpaidme']+$metotal;

$balancecollection=$applicantftw1['agentcom']-$cashliq['cashfund'];

if($applicantftw1['applicant_paid1']!=1)
{ $paids='UNPAID'; }  

if($applicantftw1['applicant_paid1']==1)
{ $paids='PAID'; } 


$totalnew1=($applicantftw1["agent_ftw"]+$applicantftw1["agent_contract"]+$applicantftw1["agent_deployed"]+$applicantftw1["agent_ppt"]);
$totalnew=($applicantftw1["agent_ftw"]+$applicantftw1["agent_contract"]+$applicantftw1["agent_deployed"]+$applicantftw1["agent_ppt"])+$totalnew;



$totaldef = mysql_query("SELECT SUM(amount) AS totaldeduct, email_address6.* FROM email_address6
where agent_id=".$_GET['agent']." AND collect_bank_id!=15
AND app_id=".$applicantftw1['applicant_id']."
ORDER BY tr_id desc");
$totaldef1=mysql_fetch_array($totaldef);

$mybalanceitom=($totaldef1['totaldeduct']+$paidamount1['totalpaidme'])-$totalnew1;

echo'<tr>';
echo "<td><input type='hidden' name='applicant_id[]' value='".$applicantftw1['applicant_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($applicantftw1['applicant_date_applied']));echo'</td>';
echo'<td><b>'.$applicantftw1["applicant_first"].' '.$applicantftw1["applicant_last"].'</b></td>';
echo'<td>'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$passportrow["passport_number"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';
echo'<td>'.$emprow["employer_name"].'</td>';
echo'<td>
<button type="button" class="btn btn-'.$srow["statusColors"].' btn-xs" style="font-size:9px">'.$srow["status"].'</button></td>';
echo "<td> ".$applicantftw1['agent_ppt']."</td> ";
echo "<td> ".$applicantftw1['agent_ftw']."</td> ";
echo "<td> ".$applicantftw1['agent_contract']."</td> ";
echo "<td> ".$applicantftw1['agent_deployed']."</td> ";
echo "<td> ".$totalnew1."</td> ";
echo "<td> ".$totaldef1['totaldeduct']."</td> ";
echo "<td> ".$paidamount1['totalpaidme']."</td> ";
echo "<td style='COLOR:rED'> ".$mybalanceitom."</td> ";
}
?>
<tr>
<td colspan="10" STYLE="text-align:right;FONT-weight:bold;font-size:16px;background:orange">TOTAL COMMISSION: <?php echo number_format($totalnew,2);?></td>
<td colspan="2" STYLE="text-align:right;FONT-weight:bold;font-size:16px;background:orange"> PAID:   <?php echo number_format($metotal,2);?></td>

</tr>






































    
    
<tr>
    <td colspn="8"><h3 style="color:red">Total Deduction  (CREDIT)<small style="color:black">  </small></h3></td>
</tr>     
    
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


$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$rcolec['app_id']." ");
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











<?php
$debit=$oldtotal+$totalamountUSD+$totalcharge1+$deductiontotal+$total_com+$liqdeductiontotal;
$credit=$totalnew+$totalcollections;
$lahatna=$debit-$credit;
?>




<tr>
    <td colspan="8"><h2 style="color:BLACK;font-size:20px;float:left"><u>SUMMARY REPORTS </u></h2></td>
</tr>

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



</div>










</body>