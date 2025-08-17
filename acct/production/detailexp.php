<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<!-- Meta, title, CSS, favicons, etc. -->
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Accounting System</title>
</head>

<body>
<style>
#wrapper{
border:0px solid black;
width:1100px;
min-height:630px;
height:!important;
margin: 0 auto;	
padding:20px;
font-size:12px;
font-family:calibri;
}
#clear{
clear:both;
height:5px;	
}
table{
border:0px solid black;	
border-collapse: collapse;
width:100%;
margin: 0 auto;	
}
table td{
border:1px solid black;
padding:3px;
font-size:12px;
}
table th{
border:1px solid black;
font-weight:bold;
font-size:11px;
}
h2{
text-align:center;
font-size:16px;
}
h3{
text-align:center;
font-size:14px;
MARGIN-TOP:-10px;
}
h1{
text-align:center;
MARGIN-TOP:-10px;
font-size:20px;
}
</style>




<?PHP
include'inc/functions.php';
$PHP = mysql_query("SELECT sum(amount) as PHPTOTAL FROM email_address
where request_paymenT IN (0,1,2,3) AND
date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND currency= '".$_POST['currency']."'");
$phprow=mysql_fetch_array($PHP);




$app = mysql_query("SELECT *FROM email_address
where request_paymenT IN (0,1,2,3) AND
date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND currency='".$_POST['currency']."'");

$app1 = mysql_query("SELECT * FROM email_address4
where  request_paymenT IN (0,1,2,3)  AND
date_transaction between '".$_POST['start']."' AND '".$_POST['end']."'
AND currency='".$_POST['currency']."'
 and type_status!='Repat'
");

$phpfinal = number_format($phprow['PHPTOTAL'], 2);
$usdfinal = number_format($usdrow['USDTOTAL'], 2);

?>
<div id="wrapper">
<h1>Expenses AS OF </h1>
<div id="clear"></div>
<p style="color:BLACK;margin-top:-10px;text-align:center;font-size:14px">Start: <?php echo date('Y-M-d', strtotime($_POST['start'])); ?>  
</p>
<div id="clear"></div>
<p style="color:BLACK;margin-top:-18px;text-align:center;font-size:14px">End: <?php echo date('Y-M-d', strtotime($_POST['end'])); ?></p>
<div id="clear"></div>






<h3>Office/Agent Expense</h3>

<table class="table table-bordered" >
<thead>
<tr>
<th STYLE="">#</th>
<th STYLE="width:3%">Ref#</th>
<th STYLE="width:10%">DATE</th>
<th STYLE="width:10%">PAYEE</th>
<th STYLE="width:10%">APPLICANT</th>
<th STYLE="width:10%">Account</th>
<th STYLE="width:10%">C/O Staff</th>
<th >PARTICULAR</th>
<th STYLE="width:5%">CURRENCY</th>
<th STYLE="width:5%">AMOUNT </th>
<th STYLE="width:5%">Deduction</th>
<th STYLE="width:5%">Allowance</th>
<th STYLE="width:5%">Total</th>
</tr>
</thead>
<tbody>
<?php	
$counter1 = 1; 
while($row=mysql_fetch_array($app))
{
	
$loan = mysql_query("SELECT sum(l_amount) as cashfund FROM e_liq
where l_type  IN ('LOAN','Deduction')
AND l_e_d=".$row['e_id']."
");
$loanr=mysql_fetch_array($loan);


$aa = mysql_query("SELECT sum(l_amount) as cashfund FROM e_liq
where l_type  IN ('Allowance')
AND l_e_d=".$row['e_id']."
");
$aaa=mysql_fetch_array($aa);

	
$ded=($aaa['cashfund']+$row["amount"])-$loanr['cashfund'];	
$totalamountPHP=$ded+$totalamountPHP;
$app2 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row2=mysql_fetch_array($app2);
$app3 = mysql_query("SELECT * FROM applicant where applicant_id=".$row['app_id']." ");
$row3=mysql_fetch_array($app3);

$USER = mysql_query("SELECT * FROM  user where user_id=".$row['staff_id']." ");
$rowu=mysql_fetch_array($USER);


echo'<tr>';
echo'<td>'.$counter1.'</td>';
echo'<td>000'.$row["e_id"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['date_ad']));echo'</td>';

echo'<td>'.$row2["agent_first"].' '.$row2["agent_last"].'</td>';
echo'<td>'.$row3["applicant_first"].' '.$row3["applicant_last"].'</td>';
echo'<td>'.$row["account"].'</td>';
echo'<td STYLE="color:green"><a href="view_liq.php?e_id='.$rowu["e_id"].' && name='.$rowu["user_fullname"].'" target="_blank" ">'.$rowu["user_fullname"].'</td>';
echo'<td>'.$row["description"].'</td>';
echo'<td>'.$row["currency"].'</td>';
echo'<td>'.$row["amount"].'</td>';
echo'<td> '.$loanr['cashfund'].'</td>';
echo'<td> '.$aaa['cashfund'].'</td>';
echo'<td> '.$ded.'</td>';
echo'</tr>';
$counter1++;
}
$php = number_format($totalamountPHP, 2);
?>

<tr>
<td  colspan="12"  style="font-weight:bold;text-align:right;font-size:14px;color:red"><?PHP ECHO $_POST['currency'];?></td>
<td  style="font-weight:bold;text-align:right;font-size:14px;color:red"> <?=$php;?> </td>
</TR>
</tbody>
</table>





</BR></BR></BR>
<h3>Appllicant Expense</h3>
<table class="table table-bordered">
<thead>
<tr>
<th>#</th>
<th STYLE="width:3%">Ref#</th>
<th STYLE="width:10%">DATE</th>
<th >PAYEE</th>
<th >Candidate</th>
<th >Account</th>
<th >DESCRIPTION</th>
<th STYLE="width:5%">status</th>
<th STYLE="width:5%">CURRENCY</th>
<th STYLE="width:5%">Total</th>

</tr>
</thead>
<tbody>
<?php	
$counter = 1; 
while($row1=mysql_fetch_array($app1))
{
$app2 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row1['agent_id']." ");
$row2=mysql_fetch_array($app2);
$app3 = mysql_query("SELECT * FROM applicant where applicant_id=".$row1['app_id']." ");
$row3=mysql_fetch_array($app3);
$totalamountUSD=$row1['receivable']+$totalamountUSD;
echo'<tr>';
echo'<td>'.$counter.'</td>';
echo'<td>000'.$row1["exp_id"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row1['date_transaction']));echo'</td>';
echo'<td>'.$row2["agent_first"].' '.$row2["agent_last"].'</td>';
echo'<td>'.$row3["applicant_first"].' '.$row3["applicant_last"].'</td>';
echo'<td>'.$row1["exp_type"].'</td>';
echo'<td>'.$row1["findings"].'</td>';
echo'<td>'.$row1["type_status"].'</td>';
echo'<td>'.$row1["currency"].'</td>';
echo'<td>'.$row1["receivable"].'</td>';

echo'</tr>';
$counter++;
}

$usd = number_format($totalamountUSD, 2);
$grandtot = number_format($totalamountPHP+$totalamountUSD, 2);
?>
<tr>
<td  colspan="9"  style="font-weight:bold;text-align:right;font-size:14px;color:red"><?PHP ECHO $_POST['currency'];?></td>
<td  style="font-weight:bold;text-align:right;font-size:14px;color:red"> <?=$usd;?> </td>
</TR>
</tbody>
</table>
</br>

<div style="padding:8px;border:1px solid lightgray;text-align:right;margin:-top:20px;font-size:14px;color:blue">
Total Expenses: <?=$grandtot;?> 
</div>



</body>
</html>
