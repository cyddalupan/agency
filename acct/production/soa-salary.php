<?php
session_start();
include'inc/functions.php';
include'tracking.php';
$app = mysql_query("SELECT * FROM applicant where applicant_id=".$_GET['appid']." ");
$row=mysql_fetch_array($app);




$CERT = mysql_query("SELECT * FROM applicant_certificate where certificate_applicant=".$_GET['appid']." ");
$certrow=mysql_fetch_array($CERT);

$REQ = mysql_query("SELECT * FROM applicant_requirement where requirement_applicant=".$_GET['appid']." ");
$reqrow=mysql_fetch_array($REQ);
$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$row1=mysql_fetch_array($app1);


?>
<style>
#wrapper{
border:0px solid black;
width:90%;
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

#customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}
</style>


<div style="width:1100px;padding:20px;border:0px solid black;margin:0 auto;font-family:calibri;">


<img src="skyline.png" style="width:400px"> 


<script>
function exportF(elem) {
  var table = document.getElementById("customers");
  var html = table.outerHTML;
  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
  elem.setAttribute("href", url);
  elem.setAttribute("download", "export.xls"); // Choose the file name
  return false;
}
</script>


<?php
$office2 = mysql_query("SELECT * FROM email_address
where account  IN ('SALARY')
AND account_expense=0
and request_payment!=999
AND currency='PHP'  
AND request_payment=3
");

?>



</br>
<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:14px;padding:5px">Save my work</a>



<table class="table table-fixed"style="width:99%;PADDING:5PX" id="customers">
<thead>

<tr  style="border:none;text-align:center">
<td colspan="10" style="border:none;text-align:center"> <h2>SALARY EXPENSE  </h2> </td>
</tr>

<tr>
<th>#</th>
<th > Date</th>
<th>Suppier/Client</th>
<th>Type</th>
<th >Account</th>
<th >DESCRIPTION</th>
<th >CURRENCY</th>
<th>AMOUNT</th>

</tr>

</thead>
<tbody>
<?PHP


$counter=1;


while($row=mysql_fetch_array($office2))
{
$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);

$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$row['app_id']." ");
$rowp=mysql_fetch_array($applicant);

$USER = mysql_query("SELECT * FROM  user where user_id=".$row['staff_id']." ");
$rowu=mysql_fetch_array($USER);
$sup = mysql_query("SELECT * FROM  suppliers where sup_id=".$row['sup_id']." ");
$sup1=mysql_fetch_array($sup);



$mytotal=$row['amount']+$mytotal;


echo'<tr>';
echo'<td>'.$counter.'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['date_ad']));echo'</td>';
echo'<td>'.$sup1["name"].'</td>';
echo'<td>'.$row["method"].'</td>';
echo'<td style="font-size:16px;font-weight:bold">'.$row["account"].'</td>';
echo'<td>'.$row["description"].'</td>';
echo'<td>'.$row["currency"].'</td>';
echo'<td> '.number_format($row['amount'], 0).'</td>';

echo'</tr>';
$counter++;
}
?>		

<tr>
<td style="FONT-WEIGHT:bold;text-align:right;font-size:22px;background:lightblue" colspan="11">GRAND TOTAL AMOUNT : &nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo number_format($mytotal,2);?></td>
</tr>





</tbody>
</table>











	

</br>




</div>