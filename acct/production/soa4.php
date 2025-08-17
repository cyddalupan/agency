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
table, td, th {
    border: 1px solid black;
	text-align:left;
	font-size:14px;
	PADDING:3PX;

}

table {
    border-collapse: collapse;
    width: 100%;
}

th {
    height: 30px;
}
</style>

<div style="width:1100px;padding:20px;border:1px solid black;margin:0 auto;font-family:calibri;">


<h2 STYLE="MARGIN-TOP:-10PX;TEXT-ALIGN:CENTER;FONT-SIZE:48PX"><img src="pjvbanner.png"> </h2>


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


<?php
IF($_GET['mysoa']==1){
$title="FOR APPROVAL";

$liqq = mysql_query("SELECT * FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
where e_liq.l_status=0
AND email_address.currency='USD'
AND email_address.request_payment=0
AND e_liq.l_type  IN ('Agent')
order by date_ad desc ");
}

IF($_GET['mysoa']==2){
$title="APPROVED";

$liqq = mysql_query("SELECT * FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
where e_liq.l_status=0
AND email_address.currency='USD'
AND email_address.request_payment=1
AND e_liq.l_type  IN ('Agent')
order by date_ad desc ");
}


IF($_GET['mysoa']==3){
$title="For Release";

$liqq = mysql_query("SELECT * FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
where e_liq.l_status=0
AND email_address.currency='USD'
AND email_address.request_payment=2
AND e_liq.l_type  IN ('Agent')
order by date_ad desc ");
}



IF($_GET['mysoa']==4){
$title="Released";

$liqq = mysql_query("SELECT * FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
where e_liq.l_status=0
AND email_address.currency='USD'
AND email_address.request_payment=3
AND e_liq.l_type  IN ('Agent')
order by date_ad desc ");
}

IF($_GET['mysoa']==5){
$title="PPT";

$liqq = mysql_query("SELECT * FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
where e_liq.l_status=0
AND email_address.currency='USD'
AND email_address.request_payment=3
AND e_liq.type_applicant  IN ('PPT')
AND e_liq.l_type  IN ('Agent')
order by date_ad desc ");
}


IF($_GET['mysoa']==6){
$title="FTW";

$liqq = mysql_query("SELECT * FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
where e_liq.l_status=0
AND email_address.currency='USD'
AND email_address.request_payment=3
AND e_liq.type_applicant  IN ('FTW 1','FTW 2','FTW 3')
AND e_liq.l_type  IN ('Agent')
order by date_ad desc ");
}


IF($_GET['mysoa']==7){
$title="CS";

$liqq = mysql_query("SELECT * FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
where e_liq.l_status=0
AND email_address.currency='USD'
AND email_address.request_payment=3
AND e_liq.type_applicant  IN ('SIGN CONTRACT')
AND e_liq.l_type  IN ('Agent')
order by date_ad desc ");
}


IF($_GET['mysoa']==8){
$title="DEPLOYED";

$liqq = mysql_query("SELECT * FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
where e_liq.l_status=0
AND email_address.currency='USD'
AND email_address.request_payment=3
AND e_liq.type_applicant  IN ('DEPLOYED 1','DEPLOYED 2','DEPLOYED 3')
order by date_ad desc ");
}



IF($_GET['mysoa']==9){
$title="C/A";

$liqq = mysql_query("SELECT * FROM  e_liq 
LEFT JOIN email_address
ON e_liq.l_e_d = email_address.e_id
where e_liq.l_status=0
AND e_liq.liq_app=0
AND email_address.request_payment=3
AND e_liq.type_applicant  IN ('CASH ADVANCE')
AND e_liq.l_type  IN ('Agent')
order by date_ad desc ");
}


?>



</br>
<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:14px;padding:5px">Save my work</a>



<table class="table table-fixed"style="width:99%;PADDING:5PX" id="example-checkbox">
<thead>

<tr  style="border:none;text-align:center">
<td colspan="10" style="border:none;text-align:center"> <h2><?=$title?>   </h2> </td>
</tr>



<tr>
<th>#</th>
<th>Date</th>
<th style="text-align:center">Ref #</th>
<th style="text-align:center">Status</th>
<th style="text-align:center">Applicant</th>
<th style="text-align:center">Agent</th>
<th style="text-align:center">Country</th>
<th style="text-align:center">Account Type</th>
<th style="text-align:center">Description</th>
<th style="text-align:center">Currency</th>
<th style="text-align:center">Amount </th>
</tr>

</thead>
<tbody>
<?PHP


$counter=1;


while($liqrow=mysql_fetch_array($liqq))
{
$statys = mysql_query("SELECT * FROM  email_address where e_id=".$liqrow['l_e_d']." ");
$statys1=mysql_fetch_array($statys);		
$liqcharge=$liqrow['l_amount']+$liqcharge;  	
$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$liqrow['liq_app']." ");
$rowp=mysql_fetch_array($applicant);


$country = mysql_query("SELECT * FROM country where country_id=".$rowp['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$liqrow['agent_id']." ");
$row1=mysql_fetch_array($app1);



if($statys1["request_payment"]==0){$request='Pending';$rcolor='black';$disme='';}
if($statys1["request_payment"]==1){$request='Approved';$rcolor='blue';$disme='';}
if($statys1["request_payment"]==2){$request='For Release';$rcolor='orange';$disme='';}
if($statys1["request_payment"]==3){$request='Released';$rcolor='green';$disme='disabled';}
if($statys1["request_payment"]==4){$request='Decline';$rcolor='red';$disme='';}
if($statys1["request_payment"]==5){$request='Return to maker';$rcolor='red';$disme='';}	
	
echo'<tr>';
echo'<td>'.$counter.'</td>';
echo'<td>'; echo date('M-d-Y', strtotime($liqrow['l_date']));echo'</td>';
echo'<td>000'.$liqrow["l_e_d"].'</td>';
echo'<td style="font-size:12px;color:white;">
<i>'.$request.'</i>
</td>';
echo'<td style="font-size:16px;font-weight:bold">'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td font-size:16px;font-weight:bold>'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';
echo'<td>'.$liqrow["type_applicant"].'</td>';
echo'<td>'.$liqrow["l_remarks"].'</td>';
echo'<td style="text-align:center">$</td>';
echo'<td style="text-align:center;font-weight:bold" font-size:16px;font-weight:bold">'.$liqrow["l_amount"].'</td>';


echo'</tr>';
$counter++;
}
?>		

<tr>
<td style="FONT-WEIGHT:bold;text-align:right;font-size:22px;background:lightblue" colspan="11">GRAND TOTAL AMOUNT : &nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo number_format($liqcharge,2);?></td>
</tr>





</tbody>
</table>











	

</br>




</div>