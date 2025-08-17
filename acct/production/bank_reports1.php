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
//$app = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$_GET['agent']." ");
//$row=mysql_fetch_array($app);
?>

<title>Accounts</title>

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
$fafa = mysql_query("SELECT * FROM bank_accounts where bank_id=".$_GET['accounts']." ");
$fafaemployer=mysql_fetch_array($fafa);
include'tracking.php';
?>
<form id="demo-form2"  method="post">
<table style="width:500px;border:none">
	<tr>
		<td style="border:none">Date From: <input type="date"  class="form-control" name="datefrom" required  value="<?=$date2?>"></td>
		<td style="border:none">Date To: <input type="date"  class="form-control" name="dateto" required   value="<?=$date2?>"></td>
		<td style="border:none"><button type="submit" class="btn btn-info"  name="filter" >Filter</button></td>
	</tr>
</table>
</form>
<div id="clear"></div></br>
<?php
if(isset($_POST['filter']))
{
echo 'Date From:'; echo date('Y-M-d', strtotime($_POST['datefrom'])); echo'&nbsp;&nbsp;&nbsp;&nbsp;';
echo 'Date To:'; echo date('Y-M-d', strtotime($_POST['dateto']));  
}

if(isset($_POST['filter']))
{	
	$cashindate="AND cash_date between '".$_POST['datefrom']."' AND '".$_POST['dateto']."'";
	
	$collecteddate="AND date between '".$_POST['datefrom']."' AND '".$_POST['dateto']."'";
	$fundate="AND date_ad between '".$_POST['datefrom']."' AND '".$_POST['dateto']."'";
	$cashoutdate="AND cash_date between '".$_POST['datefrom']."' AND '".$_POST['dateto']."'";
	//echo'test';
}
else {
$cashindate="";
$collecteddate="";
$fundate="";
$cashoutdate="";		
}
?>





<table id="customers">
<tr>
<td STYLE="font-size:16px"><b>Account # :</b> 000<?=$fafaemployer["bank_num"]?> </td>
<td STYLE="font-size:16px"><b>Bank & ACCOUNT Name :  </b><?=$fafaemployer["bank_name"]?></td>

</tr>
</table>



</div>






<div id="wrapper">
<h2 style="color:blue;font-size:20px"><u>Collected <small  style="color:black">(DEBIT)</small></u></h2>
<table class="table table-fixed">
<thead>
<tr>
<th>Code #</th>
<th>DATE</th>
<th>Reference Number:</th>
<th>FRA</th>
<th>Applicant</th>
<th>Account</th>
<th>Description</th>
<th>Currency</th>
<th>Amount</th>
</tr>
</thead>
<tbody>

<?php	
//AND collect_status=1
$collected = mysql_query("SELECT * FROM email_address6
Where collect_bank_id=".$_GET['accounts']."
$collecteddate
ORDER BY tr_id desc");
while($collected1=mysql_fetch_array($collected))
{
//$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
//$row1=mysql_fetch_array($app1);
$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$collected1['app_id']." ");
$rowp=mysql_fetch_array($applicant);

$emp = mysql_query("SELECT * FROM employer where employer_id=".$collected1['emp_id']." ");
$emprow=mysql_fetch_array($emp);

if($collected1["currency"]=="USD"){$myamt=$collected1["amount"]*1;}
if($collected1["currency"]=="PHP"){$myamt=$collected1["amount"];}
$totalreceived=$collected1["amount"]+$totalreceived;

if($collected1["collect_status"]==0){$request='Pending';$rcolor='black';}
if($collected1["collect_status"]==1){$request='Approved';$rcolor='green';}
if($collected1["collect_status"]==2){$request='Return to maker';$rcolor='red';}

echo'<tr>';
echo'<td style="font-size:10px">000'.$collected1["tr_id"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($collected1['date']));echo'</td>';
echo'<td>'.$collected1["or_num"].'</td>';
echo'<td>'.$emprow["employer_name"].'</td>';
echo'<td>
'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$collected1["account"].'</td>';
echo'<td>'.$collected1["particular"].'</td>';
echo'<td> ₱</td>';
echo'<td> '.$myamt.'</td>';
echo'</tr>';
}
$finalcashins=$totalreceived;
?>
<tr>
<td colspan="12" STYLE="text-align:right;FONT-weight:bold;font-size:16px;color:blue">TOTAL.: <?=$_GET['cur']?>  <?php echo' '.number_format($finalcashins, 2).''; ?></td>
</tr>
</tbody>
</table>
</div>


<div style='page-break-before: always;'></div>



<div id="wrapper">
<h2 style="color:blue;font-size:20px"><u>Cash Expense <small  style="color:black">(CREDIT)</small></u></h2>
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
WHERE fund_source_id=".$_GET["accounts"]." AND request_payment=3 and currency='".$_GET["cur"]."'
$fundate ");


while($fund188=mysql_fetch_array($fund111119))
{
$tot=$fund188["amount"];
$fundsssss88=$fund188["amount"]+$fundsssss88;

echo'<tr>';
echo'<td style="font-size:10px">000'.$fund188["ref"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($fund188['date_ad']));echo'</td>';
echo'<td>'.$fund188["amount"].'</td>';
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


<h2 style="color:blue;font-size:20px"><u>Cash OUT <small  style="color:black">(CREDIT)</small></u></h2>
<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>
    <th STYLE="width:2%">#</th>
<th STYLE="width:10%">DATE</th>
<th>Type</th>
<th STYLE="width:15%">Cash OUT Bank</th>
<th STYLE="width:15%">Cash IN Bank</th>
<th>Amount</th>
<th>Conversion Rate</th>
<th>Total  <?=$_GET['cur']?> </th>


</tr>
</thead>
<tbody>
<?php


$cashout = mysql_query("SELECT * FROM  cash_transaction WHERE cash_bank_out=".$_GET['accounts']."
$cashoutdate
order by cash_id desc  ");


while($cashout1=mysql_fetch_array($cashout))
{
$bank_out = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$cashout1['cash_bank_out']." ");
$bank_out1=mysql_fetch_array($bank_out);

$bank_in = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$cashout1['cash_bank_in']." ");
$bank_in1=mysql_fetch_array($bank_in);

//total cash in
if($_GET['cur']=='USD'){
$cashouttotal=$cashout1["cash_amount"];
}
if($_GET['cur']=='PHP'){
$cashouttotal=$cashout1["cash_amount"]*$cashout1["cash_convertion"];
}
$grandcashouttotal=$cashouttotal+$grandcashouttotal;

if($cashout1["cash_type"]==1){$requestrr='Cash IN Only';$rcolor='blue';}
if($cashout1["cash_type"]==2){$requestrr='Cash IN & OUT';$rcolor='orange';}


echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$cashout1['cash_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($cashout1['cash_date']));echo'</td>';
echo'<td style="font-size:12px;color:BLACK;">'.$requestrr.'</td>';
echo'<td>BankName:'.$bank_out1["bank_name"].' <br> BankAccount:'.$bank_out1["bank_num"].' ('.$bank_out1["bank_currency"].')</td>';
echo'<td>BankName:'.$bank_in1["bank_name"].' <br> BankAccount:'.$bank_in1["bank_num"].' ('.$bank_in1["bank_currency"].')</td>';


echo'<td>'.$cashout1["cash_amount"].'</td>';
echo'<td>'.$cashout1["cash_convertion"].'</td>';
echo'<td> '.number_format($cashouttotal, 2).'</td>';

echo'</tr>';
}
$tot = number_format($total, 2);
?>
</tbody>
<tr>
<td colspan="12" STYLE="text-align:right;FONT-weight:bold;font-size:16px;color:blue">TOTAL.: <?=$_GET['cur']?> <?php echo' '.number_format($grandcashouttotal, 2).''; ?></td>
</tr>
</table>


</div>






<?php
$mycredits=$fundsssss88+$grandcashouttotal;
$lahatna=$finalcashins-$mycredits;
?>
<div style='page-break-before: always;'></div>

<div id="wrapper">
<h2 style="color:BLACK;font-size:20px;float:right"><u>SUMMARY REPORTS </u></h2>
<div style="clear:both;"></div>
<table class="table table-fixed" style="width:500px;float:right">
<tbody>

<TR>
<td style="font-size:16px"><b>Total Collected ( Debit)</b></td>
<td  style="font-size:16px"><?php echo' '.number_format($finalcashins, 2).''; ?></td>
</tr> 
<TR>
<td style="font-size:16px"><b>Total Collectables (Credit)</b></td>
<td  style="font-size:16px"><?php echo' '.number_format($mycredits, 2).''; ?></td>
</tr>





<TR>
<td style="font-size:16px" colspan="2"><b>_________________________________________________________________</b></td>
</tr>

<TR>
<td style="font-size:16px"><b>Balance</b></td>
<td  style="font-size:16px;color:BLACK;text-align:right;FONT-WEIGHT:bold"><?=$_GET['cur']?> <?php echo' '.number_format($lahatna, 2).''; ?></td>
</tr>
</tbody>
</table>

</DIV>






</body>