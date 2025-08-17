    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

<div style="width:90%;padding:20px;border:0px solid black;margin:0 auto;font-family:calibri;">


<?PHP include'title2.php';?>



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
$office2 = mysql_query("SELECT email_address.*
,applicant.applicant_first
,applicant.applicant_middle
,applicant.applicant_last
FROM email_address
LEFT JOIN  	applicant
ON email_address.app_id = applicant.applicant_id
where account  IN ('Full Agent','Cash Advance agent','FTW','CS','DEPLOYED','PARTIAL AGENT')
AND account_expense=0
and request_payment!=999
AND request_payment=3
order by date_ad desc
");

?>



</br>
<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:14px;padding:5px">Save my work</a>



<table class="table table-fixed"style="width:99%;PADDING:5PX" id="customers">
<thead>

<tr  style="border:none;text-align:center">
<td colspan="10" style="border:none;text-align:center"> <h2>AGENT EXPENSE</h2> </td>
</tr>

<tr>
<th>#</th>
<th > Date</th>
<th>Suppier/Client</th>
<th>AGENT</th>
<th>APPLICANT</th>
<th>Type</th>
<th >Account</th>
<th >DESCRIPTION</th>
<th >CURRENCY</th>
<th>AMOUNT</th>
<th>TOTAL ₱</th>
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

if($row["currency"]=="USD"){$total_reim=$row['amount']*50;}
if($row["currency"]=="PHP"){$total_reim=$row['amount'];}

$mytotal=$total_reim+$mytotal;




echo'<tr>';
echo'<td>'.$counter.'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['date_ad']));echo'</td>';
echo'<td>'.$sup1["name"].'</td>';
echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row["applicant_first"].' '.$row["applicant_last"].'</td>';
echo'<td>'.$row["method"].'</td>';
echo'<td style="font-size:16px;font-weight:bold">'.$row["account"].'</td>';
echo'<td>'.$row["description"].'</td>';
echo'<td>'.$row["currency"].'</td>';
echo'<td> '.number_format($row['amount'], 0).'</td>';
echo'<td  style="font-size:18px;font-weight:bold"> '.number_format($total_reim, 0).'</td>';

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