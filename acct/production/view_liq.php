<style>
#customers {
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
  font-size:11px;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 2px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 11px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #4CAF50;
  color: white;
}
</style>

<div style="width:70%;margin:0 auto">
<h2 style="text-align:center">Liquidation </h2>
<h4>Name :  <?=$_GET['name']?> </h4>
<h4>Ref # :  000<?=$_GET['e_id']?> </h4>



  <form action="" method="post">

<table  cellspacing="0" id="customers">
<thead>
<tr>

<th STYLE="width:2%">Ref #</th>
<th STYLE="width:3%">DATE</th>
<th STYLE="width:5%">Applicant Exp</th>
<th STYLE="width:5%">Req.By</th>
<th STYLE="width:5%">Type</th>
<th STYLE="width:5%">Description</th>
<th STYLE="width:5%">Currency</th>
<th STYLE="width:5%">Amount</th>
</tr>
</thead>
<tbody>
<?php	

include'inc/db.php';

$app = mysql_query("SELECT * FROM e_liq where l_status=0
AND l_e_d=".$_GET['e_id']."
order by l_type,l_id desc");

while($row=mysql_fetch_array($app))
{
    
$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$row['liq_app']." ");
$rowp=mysql_fetch_array($applicant);    
$USER = mysql_query("SELECT * FROM  user where user_id=".$row['l_staff_id']." ");
$rowu=mysql_fetch_array($USER);

$total1=$row['l_amount']+$total1;
$amt = number_format($row["l_amount"], 2);


echo'<tr>';
echo'<td>000'.$row["l_e_d"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['l_date']));echo'</td>';
echo'<td>'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$rowu["user_fullname"].'</td>';
echo'<td>'.$row["l_type"].'</td>';
echo'<td>'.$row["l_remarks"].'</td>';
echo'<td>'.$row["l_currency"].'</td>';
echo'<td>'.$amt.'</td>';
echo'</tr>';
}
$tot1 = number_format($total1, 2);
?>

<tr>
<td  colspan="7"  style="font-weight:bold;text-align:right;font-size:14px;color:red">Total Liquidation:</td>
<td  style="font-weight:bold;text-align:left;font-size:14px;color:red"> <?=$tot1;?> </td>
</TR>

</tbody>
</table>

<h4>My Receipt</h4>


<?php	

$app11111 = mysql_query("SELECT * FROM resibo where resibo_connect=".$_GET['e_id']."
order by resibo_id desc");

while($row111=mysql_fetch_array($app11111))
{

echo'<a  href="'.$row111["resibo_path"].'" target="_blank"><img src="'.$row111["resibo_path"].'" style="width:170px;height:200px;margin:20px;border:1px solid lighgray"></a>';
}

?>




</form>		
		

</div>







