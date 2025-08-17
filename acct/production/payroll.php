<!DOCTYPE html>
<html lang="en">
  <head>
      <meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
   
   

  </head>


<style>
body{
font-family:calibri;    
}
#customers {
  font-family: calibri,"Trebuchet MS", Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 90%;
  font-size:9px;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 2px;
}



#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 2px;
  padding-bottom: 2px;
  text-align: left;
  color: black;
}
h5{
margin-top:-25px;    
}
h3{
FONT-SIZE:12PX;
}
</style>

<div style="width:90%;margin:0 auto">
</br>
<?php include'title2.php'?>
  
<h5 style="text-align:center">Payroll </h5>
<h5>Name :  <?=$_GET['name']?> </h5>
<h5>Ref # :  000<?=$_GET['e_id']?> </h5>
<h5>Date :  <?=$_GET['date_ad'];?> </h5>
<h5>Position:<input class="w3-input" type="text" STYLE="margin-left:10px;border:1px  solid #FFFFFF;FONT-Size:10px"> </h5>
 <form action="" method="post">

<?php
include'inc/db.php';

$app = mysql_query("SELECT * FROM e_liq where l_status=0
AND l_e_d=".$_GET['e_id']."
AND l_type NOT IN ('Liquidation','Incentive','Allowance')
order by l_type,l_id desc");

?>


<?php
$in = mysql_query("SELECT * FROM e_liq where l_status=0
AND l_e_d=".$_GET['e_id']."
AND l_type  IN ('Incentive')
order by l_type,l_id desc");

?>

<h3>Incentive</h3>

<table  cellspacing="0" id="customers">
<thead>
<tr>

<th STYLE="width:2%">Ref #</th>
<th STYLE="width:10%">DATE</th>
<th STYLE="width:5%">Req.By</th>
<th STYLE="width:5%">Type</th>
<th STYLE="width:5%">Description</th>
<th STYLE="width:5%">Currency</th>
<th STYLE="width:5%">Amount</th>
</tr>
</thead>
<tbody>
<?php	


while($rowin=mysql_fetch_array($in))
{
$USER = mysql_query("SELECT * FROM  user where user_id=".$rowin['l_staff_id']." ");
$rowu=mysql_fetch_array($USER);

$rowintotal1=$rowin['l_amount']+$rowintotal1;
$rowinamt = number_format($rowin["l_amount"], 2);


echo'<tr>';
echo'<td>000'.$row["l_e_d"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['l_date']));echo'</td>';
echo'<td>'.$rowu["user_fullname"].'</td>';
echo'<td>'.$rowin["l_type"].'</td>';
echo'<td>'.$rowin["l_remarks"].'</td>';
echo'<td>'.$rowin["l_currency"].'</td>';
echo'<td>'.$rowinamt.'</td>';
echo'</tr>';
}

?>
<tr>
<td  colspan="6"  style="font-weight:bold;text-align:right;font-size:10px;color:black">Total Incentive(s):</td>
<td  style="font-weight:bold;text-align:left;font-size:10px;color:black"> <?=$rowintotal1;?> </td>
</TR>

</tbody>
</table>











<?php
$al = mysql_query("SELECT * FROM e_liq where l_status=0
AND l_e_d=".$_GET['e_id']."
AND l_type  IN ('Allowance')
order by l_type,l_id desc");

?>

<h3>Allowance</h3>

<table  cellspacing="0" id="customers">
<thead>
<tr>

<th STYLE="width:2%">Ref #</th>
<th STYLE="width:10%">DATE</th>
<th STYLE="width:5%">Req.By</th>
<th STYLE="width:5%">Type</th>
<th STYLE="width:5%">Description</th>
<th STYLE="width:5%">Currency</th>
<th STYLE="width:5%">Amount</th>
</tr>
</thead>
<tbody>
<?php	


while($rowal=mysql_fetch_array($al))
{
$USER = mysql_query("SELECT * FROM  user where user_id=".$rowal['l_staff_id']." ");
$rowu=mysql_fetch_array($USER);

$rowaltotal1=$rowal['l_amount']+$rowaltotal1;
$rowalamt = number_format($rowal["l_amount"], 2);


echo'<tr>';
echo'<td>000'.$rowal["l_e_d"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($rowal['l_date']));echo'</td>';
echo'<td>'.$rowal["user_fullname"].'</td>';
echo'<td>'.$rowal["l_type"].'</td>';
echo'<td>'.$rowal["l_remarks"].'</td>';
echo'<td>'.$rowal["l_currency"].'</td>';
echo'<td>'.$rowalamt.'</td>';
echo'</tr>';
}

?>
<tr>
<td  colspan="6"  style="font-weight:bold;text-align:right;font-size:10px;color:black">Total Allowance:</td>
<td  style="font-weight:bold;text-align:left;font-size:10px;color:black"> <?=$rowaltotal1;?> </td>
</TR>

</tbody>
</table>










<h3>Deduction</h3>

<table  cellspacing="0" id="customers">
<thead>
<tr>

<th STYLE="width:2%">Ref #</th>
<th STYLE="width:10%">DATE</th>
<th STYLE="width:5%">Req.By</th>
<th STYLE="width:5%">Type</th>
<th STYLE="width:5%">Description</th>
<th STYLE="width:5%">Currency</th>
<th STYLE="width:5%">Amount</th>
</tr>
</thead>
<tbody>
<?php	


while($row=mysql_fetch_array($app))
{
$USER = mysql_query("SELECT * FROM  user where user_id=".$row['l_staff_id']." ");
$rowu=mysql_fetch_array($USER);

$total1=$row['l_amount']+$total1;
$amt = number_format($row["l_amount"], 2);


echo'<tr>';
echo'<td>000'.$row["l_e_d"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['l_date']));echo'</td>';
echo'<td>'.$rowu["user_fullname"].'</td>';
echo'<td>'.$row["l_type"].'</td>';
echo'<td>'.$row["l_remarks"].'</td>';
echo'<td>'.$row["l_currency"].'</td>';
echo'<td>'.$amt.'</td>';
echo'</tr>';
}

?>

<?php
$tto=($_GET['amt']+$rowintotal1+$rowaltotal1)-$total1;
$tot1 = number_format($total1, 2);
$tot12 = number_format($tto, 2);
$tot13 = number_format($_GET['amt'], 2);
?>
<tr>
<td  colspan="6"  style="font-weight:bold;text-align:right;font-size:10px;color:black">Total Deduction:</td>
<td  style="font-weight:bold;text-align:left;font-size:10px;color:black"> <?=$total1;?> </td>
</TR>





<tr>
<td  colspan="6"  style="font-weight:bold;text-align:right;font-size:11px;color:blue"></br></td>
<td  style="font-weight:bold;text-align:left;font-size:11px;color:blue"> </br></td>
</TR>

<tr>
<td  colspan="6"  style="font-weight:bold;text-align:right;font-size:11px;color:blue">Basic Salary:</td>
<td  style="font-weight:bold;text-align:left;font-size:11px;color:blue"> <?=$tot13;?> </td>
</TR>

<tr>
<td  colspan="6"  style="font-weight:bold;text-align:right;font-size:11px;color:blue">Total Allowance:</td>
<td  style="font-weight:bold;text-align:left;font-size:11px;color:blue"> <?=$rowalamt;?> </td>
</TR>

<tr>
<td  colspan="6"  style="font-weight:bold;text-align:right;font-size:11px;color:blue">Total Incentive:</td>
<td  style="font-weight:bold;text-align:left;font-size:11px;color:blue"> <?=$rowinamt;?> </td>
</TR>

<tr>
<td  colspan="6"  style="font-weight:bold;text-align:right;font-size:11px;color:black">Total Deduction:</td>
<td  style="font-weight:bold;text-align:left;font-size:11px;color:black"> <?=$tot1;?> </td>
</TR>



<tr>
<td  colspan="6"  style="font-weight:bold;text-align:right;font-size:11px;color:red">Net Salary:</td>
<td  style="font-weight:bold;text-align:left;font-size:11px;color:red"> <?=$tot12;?> </td>
</TR>

</table>
<?PHP
$loan = mysql_query("SELECT sum(amount) as cashfund FROM email_address
where account  IN ('LOAN')
AND staff_id=".$_GET['employe']."
");
$loanr=mysql_fetch_array($loan);

$LIQ = mysql_query("SELECT sum(l_amount) as cashfund FROM  e_liq 
where l_status=0
AND l_type  IN ('LOAN')
AND l_staff_id=".$_GET['employe']."
");
$cashliq=mysql_fetch_array($LIQ);

$balanceloan=$loanr['cashfund']-$cashliq['cashfund'];

?>
<br>
<p style="margin-top:-10px;font-size:12px"> Employee Signature: _____________________________ </p>
<p style="margin-top:-10px;font-size:12px"> Date Release: _____________________________ </p>



</form>		
		

</div>
</html>






