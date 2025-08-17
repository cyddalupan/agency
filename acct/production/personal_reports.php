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
?>


<table id="customers">
<tr>
<td STYLE="font-size:16px"><b>Account # :</b> 000<?=$fafaemployer["bank_num"]?> </td>
<td STYLE="font-size:16px"><b>Bank & ACCOUNT Name :  </b><?=$fafaemployer["bank_name"]?></td>

</tr>
</table>



<h2 style="color:blue;font-size:20px"><u>Cash IN TO<small  style="color:black">(CREDIT)</small></u></h2>
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
<th>Total ₱</th>


</tr>
</thead>
<tbody>
<?php



$cashin = mysql_query("SELECT * FROM  cash_transaction WHERE cash_bank_out=".$_GET['accounts']."
order by cash_id desc  ");


while($cashin1=mysql_fetch_array($cashin))
{
$bank_out = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$cashin1['cash_bank_out']." ");
$bank_out1=mysql_fetch_array($bank_out);

$bank_in = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$cashin1['cash_bank_in']." ");
$bank_in1=mysql_fetch_array($bank_in);
//total cash in
$totalcashins=$cashin1["cash_amount"]*$cashin1["cash_convertion"];
$grandcashin=$totalcashins+$grandcashin;

if($cashin1["cash_type"]==1){$requestrr='Cash IN Only';$rcolor='blue';}
if($cashin1["cash_type"]==2){$requestrr='Cash IN & OUT';$rcolor='orange';}


echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$cashin1['cash_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($cashin1['cash_date']));echo'</td>';
echo'<td style="font-size:12px;color:BLACK;">'.$requestrr.'</td>';
echo'<td>BankName:'.$bank_out1["bank_name"].' <br> BankAccount:'.$bank_out1["bank_num"].' ('.$bank_out1["bank_currency"].')</td>';
echo'<td>BankName:'.$bank_in1["bank_name"].' <br> BankAccount:'.$bank_in1["bank_num"].' ('.$bank_in1["bank_currency"].')</td>';


echo'<td>'.$cashin1["cash_amount"].'</td>';
echo'<td>'.$cashin1["cash_convertion"].'</td>';
echo'<td> '.number_format($totalcashins, 2).'</td>';

echo'</tr>';
}
$tot = number_format($total, 2);
$finalcashins=$grandcashin;
?>
</tbody>
</table>
<tr>
<td colspan="12" STYLE="text-align:right;FONT-weight:bold;font-size:16px;color:blue">TOTAL.: <?=$_GET['cur']?> <?=$finalcashins?></td>
</tr>
</div>









<div style='page-break-before: always;'></div>



<div id="wrapper">
<h2 style="color:blue;font-size:20px"><u>Cash Payment <small  style="color:black">(DEBIT)</small></u></h2>
<table class="table table-fixed">
<thead>
<tr>
<th>Code #</th>
<th>DATE</th>
<th>currency</th>

<th>Account</th>
<th>amount</th>
</tr>
</thead>
<tbody>

<?php	
//AND collect_status=1


$fund11111 = mysql_query("SELECT * FROM email_address
WHERE account_expense=".$_GET["accounts"]."");



while($fund1=mysql_fetch_array($fund11111))
{
$tot=$fund1["amount"];
$fundsssss=$fund1["amount"]+$fundsssss;

echo'<tr>';
echo'<td style="font-size:10px">000'.$fund1["ref"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($fund1['date_ad']));echo'</td>';
echo'<td>'.$fund1["amount"].'</td>';
echo'<td>'.$fund1["account"].'</td>';



echo'<td>'.$tot.'</td>';
echo'</tr>';
}
?>
<tr>
<td colspan="12" STYLE="text-align:right;FONT-weight:bold;font-size:16px;color:blue">TOTAL.: <?=$_GET['cur']?> <?=$fundsssss?></td>
</tr>
</tbody>
</table>





</div>






<?php

$lahatna=$finalcashins-$fundsssss;
?>
<div style='page-break-before: always;'></div>

<div id="wrapper">
<h2 style="color:BLACK;font-size:20px;float:right"><u>SUMMARY REPORTS </u></h2>
<div style="clear:both;"></div>
<table class="table table-fixed" style="width:500px;float:right">
<tbody>
<TR>
<td style="font-size:16px"><b>Total Collectables (Credit)</b></td>
<td  style="font-size:16px"><?php echo' '.number_format($finalcashins, 2).''; ?></td>
</tr>

<TR>
<td style="font-size:16px"><b>Total Collected ( Debit)</b></td>
<td  style="font-size:16px"><?php echo' '.number_format($fundsssss, 2).''; ?></td>
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