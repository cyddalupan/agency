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
a{
border:1px solid #87CEEB;    
}
</style>

<div style="width:90%;margin:0 auto">
</br>
<?php include'title2.php'?>
  
<h5 style="text-align:center">Loan </h5>
<h5>Name :  <?=$_GET['name']?> </h5>
<h5>Ref # :  000<?=$_GET['e_id']?> </h5>
<h5>Date :  <?=$_GET['date_ad'];?> </h5>
<h5>Position:<input class="w3-input" type="text" STYLE="margin-left:10px;border:1px  solid #FFFFFF;FONT-Size:10px"> </h5>
 <form action="" method="post">

<?php
include'inc/db.php';

$app = mysql_query("SELECT * FROM e_liq where l_status=0
AND l_staff_id=".$_GET['employe']."
AND l_type  IN ('LOAN')
order by l_type,l_id desc");

?>


<?php
$in = mysql_query("SELECT * FROM email_address
where account  IN ('LOAN')
AND staff_id=".$_GET['employe']."
order by e_id desc");

?>

<h3>Loan List </h3>

<table  cellspacing="0" id="customers">
<thead>
<tr>

<th STYLE="width:2%">Ref #</th>
<th STYLE="width:10%">DATE</th>
<th STYLE="width:5%">Req.By</th>
<th STYLE="width:5%">Type</th>
<th STYLE="width:15%">Description</th>
<th STYLE="width:5%">Currency</th>
<th STYLE="width:5%">Amount</th>
</tr>
</thead>
<tbody>
<?php	


while($rowin=mysql_fetch_array($in))
{

$rowintotal1=$rowin['amount']+$rowintotal1;
$rowinamt = number_format($rowin["amount"], 2);


echo'<tr>';
echo'<td>000'.$rowin["e_id"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($rowin['date_ad']));echo'</td>';

echo'<td>'.$rowin["method"].'</td>';
echo'<td>'.$rowin["account"].'</td>';
echo'<td>'.$rowin["description"].'</td>';
echo'<td>'.$rowin["currency"].'</td>';
echo'<td>'.$rowin['amount'].'</td>';
echo'</tr>';
}

?>
<tr>
<td  colspan="6"  style="font-weight:bold;text-align:right;font-size:10px;color:black">Total Loan:</td>
<td  style="font-weight:bold;text-align:left;font-size:10px;color:black"> <?=$rowintotal1;?> </td>
</TR>

</tbody>
</table>

<h3>Loan Deduction</h3>

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
$tto=$rowintotal1-$total1;
$tot1 = number_format($total1, 2);
$tot12 = number_format($tto, 2);
$tot13 = number_format($_GET['amt'], 2);
?>


<tr>
<td  colspan="6"  style="font-weight:bold;text-align:right;font-size:11px;color:blue"></br></td>
<td  style="font-weight:bold;text-align:left;font-size:11px;color:blue"> </br></td>
</TR>

<tr>
<td  colspan="6"  style="font-weight:bold;text-align:right;font-size:11px;color:blue">Total Loan:</td>
<td  style="font-weight:bold;text-align:left;font-size:11px;color:blue"> <?=$rowintotal1;?> </td>
</TR>
<tr>
<td  colspan="6"  style="font-weight:bold;text-align:right;font-size:10px;color:black">Total Loan Deduction:</td>
<td  style="font-weight:bold;text-align:left;font-size:10px;color:black"> <?=$total1;?> </td>
</TR>



<tr>
<td  colspan="6"  style="font-weight:bold;text-align:right;font-size:11px;color:red"> Loan Balance:</td>
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
<P style="font-size:11px;color:blue">Loan Balance: <?=$balanceloan?></P>
<br>
<p style="margin-top:-10px;font-size:12px"> Employee Signature: _____________________________ </p>
<p style="margin-top:-10px;font-size:12px"> Date Release: _____________________________ </p>



</form>		
		

</div>







