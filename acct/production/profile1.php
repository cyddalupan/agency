
<?php
include'inc/functions.php';
$app = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$_GET['agent']." ");
$row=mysql_fetch_array($app);
include'styles.php';
?>


<link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
<title><?=$row['agent_first']?></title>


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
table{
color:black;	
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


<?php
$oldtotal=0
?>


<table  id="example-checkbox" style="margin:5px;padding:5px;width:90%">
<tr>
    
  <td colspan="15"> <h1 style="font-size:20px;color:black;text-align:center;"><?=$row['agent_first']?> <?=$row['agent_last']?> </h1></td>
</tr> 



<tr>
  <td colspan="15"><h4 style="color:blue">Released Commission (DEBIT)<small style="color:black">  </small></h4></td>
</tr>

<tr>
<th>Date Transaction</th>
<th style="width:5px">VOUCHER NO.</th>
<th style="width: 26%">Applicant</th>
<th>Method</th>
<th>Account</th>
<th colspan="3">Description</th>
<th  colspan="7">Amount</th>
</tr>

</thead>
<tbody>
<?PHP
$app = mysql_query("SELECT * FROM email_address

LEFT JOIN  	applicant
ON email_address.app_id = applicant.applicant_id

 where  agent_id=".$_GET['agent']."
AND request_payment=3
AND account  IN ('Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
order by date_ad desc");
while($row=mysql_fetch_array($app))
{
$totalamountUSD=$row['amount']+$totalamountUSD;
echo'<tr>';
echo'<td>'; echo date('Y-M-d', strtotime($row['date_ad']));echo'</td>';
echo'<td>'.$row["ref"].'</td>';

echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row["applicant_first"].' '.$row["applicant_last"].'</td>';
echo'<td>'.$row["method"].'</td>';
echo'<td>'.$row["account"].'</td>';
echo'<td colspan="3">'.$row["description"].'</td>';

echo'<td  style="text-align:right"  colspan="7">'.$row["amount"].'</td>';
echo'</tr>';

}
?>	
<tr>
<td style="color:black;text-align:right;font-size:16px;font-weight:bold;background:yellow" colspan="13">TOTAL RECEIVED: </td>
<td style="color:black;text-align:right;font-size:16px;font-weight:bold;background:yellow" colspan="2"> <?php echo number_format($totalamountUSD,2);?></td>

</tr>	



    
<tr>
  <td colspan="15"><h4 style="color:blue">Total Deduction  (DEBIT)<small style="color:black">  </small></h4></td>
</tr>     
<tr>
<th style="width:5px">#</th>
<th>DATE</th>
<th>Agent</th>
<th STYLE="width:10%">Applicant</th>
<th  colspan="5">Remarks</th>
<th  colspan="6">Amount</th>

</tr>
</thead>
<tbody>
<?PHP
$deduction = mysql_query("SELECT deduction.*,applicant.* FROM deduction
LEFT JOIN  	applicant
ON deduction.app_id = applicant.applicant_id

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
echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$deduction1["applicant_first"].' '.$deduction1["applicant_last"].'</td>';

echo'<td colspan="5">'.$deduction1["deduction_remarks"].'</td>';
echo'<td  colspan="6"> '.number_format($deduction1["deduction_amount"], 2).'</td>';
echo'</tr>';

}
?>	

<tr>
<td style="color:black;text-align:right;font-size:16px;font-weight:bold;background:yellow" colspan="13">Total Deduction: </td>

<td style="color:black;text-align:right;font-size:16px;font-weight:bold;background:yellow" colspan="2"><?php echo number_format($deductiontotal,2);?></td>
</tr>






<tr>
    <td colspAn="15"><h3 style="color:red">Total Commission (CREDIT)<small style="color:black">  </small></h3></td>
</tr>     
    
<tr>

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
<th>Total Agree</th>
<th>Deduction</th>
<th>Amount Paid</th>
<th style="color:Red">Balance</th>
</tr>
</thead>
<tbody>

<?php	
//AND collect_status=1
$applicantftw = mysql_query("SELECT * FROM applicant
WHERE applicant_source=".$_GET['agent']." 
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

$paidamount = mysql_query("SELECT SUM(amount) AS totalpaidme, email_address.* FROM  email_address 

where  app_id=".$applicantftw1['applicant_id']."
AND request_payment=3
 AND account  IN ('Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
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



$totaldef = mysql_query("SELECT SUM(deduction_amount) AS totaldeduct, deduction.* FROM deduction
where agent_id=".$_GET['agent']." 
AND app_id=".$applicantftw1['applicant_id']." ");
$totaldef1=mysql_fetch_array($totaldef);

$mybalanceitom=$totalnew1-($totaldef1['totaldeduct']+$paidamount1['totalpaidme']);

echo'<tr>';
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
echo "<td style='color:blue'> ".$totalnew1."</td> ";
echo "<td> ".$totaldef1['totaldeduct']."</td> ";
echo "<td> ".$paidamount1['totalpaidme']."</td> ";
echo "<td style='COLOR:rED'> ".$mybalanceitom."</td> ";
}
?>


<tr>
<td colspan="13" STYLE="text-align:right;FONT-weight:bold;font-size:16px;background:orange;COLOR:black">TOTAL COMMISSION: <?php echo number_format($totalnew,2);?></td>
<td colspan="2" STYLE="text-align:right;FONT-weight:bold;font-size:16px;background:orange;COLOR:black"> PAID:   <?php echo number_format($metotal,2);?></td>

</tr>





<?php
$debit=$oldtotal+$totalamountUSD+$totalcharge1+$deductiontotal+$total_com+$liqdeductiontotal;
$credit=$totalnew+$totalcollections;
$lahatna=$credit-$debit;
?>




<tr>
    <td colspan="15"><h2 style="color:BLACK;font-size:20px;float:left"><u>SUMMARY REPORTS </u></h2></td>
</tr>

<TR>
<td style="font-size:20px;color:black;text-align:right;FONT-WEIGHT:bold;BACKGROUND:yellow" colspan="3"><b>Total Debit</b></td>
<td style="font-size:20px;color:black;text-align:right;FONT-WEIGHT:bold;BACKGROUND:yellow" colspan="12"><?php echo' '.number_format($debit, 2).''; ?></td>
</tr>

<TR>
<td  style="font-size:20px;color:black;text-align:right;FONT-WEIGHT:bold;BACKGROUND:orange" colspan="3"><b>Total Credit</b></td>
<td   style="font-size:20px;color:black;text-align:right;FONT-WEIGHT:bold;BACKGROUND:orange" colspan="12"><?php echo' '.number_format($credit, 2).''; ?></td>
</tr>





<TR>
<td style="font-size:20px;color:WHIte;text-align:right;FONT-WEIGHT:bold;BACKGROUND:BLACK"  colspan="3"><b>Balance</b></td>
<td  style="font-size:20px;color:WHIte;text-align:right;FONT-WEIGHT:bold;BACKGROUND:BLACK" colspan="12"><?php echo' '.number_format($lahatna, 2).''; ?></td>
</tr>
</tbody>
</table>



</div>










</body>