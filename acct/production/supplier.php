
<?php
include'inc/functions.php';
$app = mysql_query("SELECT * FROM suppliers where sup_id=".$_GET['e_id']." ");
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
min-height:130px;
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
<h1 style="font-size:30px;color:black;text-align:center;"><?=$row['name']?> </h1>

</div>

<div id="mainb" style="height:10px;"></div>

<div class="col-md-12 col-sm-12 col-xs-12">





<?php
$count=1;
?>
<div id="mainb" style="height:10px;"></div>
</div>

<div class="col-xs-12 table">

<h3 style="color:red">Total DEBIT</h5>

<table class="table table-fixed">
<thead>
<tr>
<th>Date Transaction</th>
<th>SOA NO.</th>
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
$app = mysql_query("SELECT * FROM email_address where  sup_id=".$_GET['e_id']."
 and request_payment=3
order by date_ad desc");
while($row=mysql_fetch_array($app))
{
$debit=$row['amount']+$debit;
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
<td style="color:black;text-align:right;font-size:16px;font-weight:bold" colspan="9">TOTAL: <?php echo number_format($debit,2);?></td>
</tr>	
</tbody>
</table>



</div>
</div>











<?php

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