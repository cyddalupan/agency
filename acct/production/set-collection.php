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
include'db.php';
include'tracking.php';


$app1 = mysql_query("SELECT * FROM email_address6
where  collect_type=1 and status_collect=0 
AND account='".$_GET["account"]."' and currency='".$_GET["cur"]."'
order by date desc");

if(isset($_POST['filter']))
{	
	$app1 = mysql_query("SELECT * FROM email_address6
	where  
	date between '".$_POST['datefrom']."' AND '".$_POST['dateto']."'
	AND collect_type=1 and status_collect=0 
	AND account='".$_GET["account"]."' 
	AND currency='".$_GET["cur"]."'
	order by date desc");
	//echo'test';
}


?>
<div id="wrapper">

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

<h1 STYLE="text-transform: capitalize;"><?=$_GET['account']?> Collection Report AS OF </h1>
<h3><?php echo date('Y-M-d', strtotime($date2)); ?></h3>
<div id="clear"></div>



<div id="clear"></div>


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



</br>
<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:14px;padding:5px">Export to excel</a>

<table class="table table-bordered" id="example-checkbox" >
<thead>
<tr>
<th>#</th>
<th>DATE</th>
<th>AGENT</th>
<th>FRA</th>
<th>Applicant</th>
<th >Account</th>
<th >Particular</th>
<th STYLE="width:5%">CURRENCY</th>
<th STYLE="width:5%">AMOUNT</th>

</tr>
</thead>
<tbody>
<?php	
$counter = 1; 
while($row1=mysql_fetch_array($app1))
{
$totalamountUSD=$row1['amount']+$totalamountUSD;
$EM = mysql_query("SELECT * FROM employer
WHERE employer_id=".$row1['emp_id']."");
$Erow=mysql_fetch_array($EM);

$app2 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row1['agent_id']." ");
$row2=mysql_fetch_array($app2);

$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$row['app_id']." ");
$rowp=mysql_fetch_array($applicant);

echo'<tr>';
echo'<td>'.$counter.'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row1['date']));echo'</td>';
echo'<td>'.$row2["agent_first"].' '.$row2["agent_last"].'</td>';

echo'<td>'.$Erow["employer_name"].'</td>';
echo'<td>
'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$row1["account"].'</td>';
echo'<td>'.$row1["particular"].'</td>';
echo'<td>'.$row1["currency"].'</td>';
echo'<td>'.$row1["amount"].'</td>';
echo'</tr>';
$counter++;
}

$usd = number_format($totalamountUSD, 2);
?>
<tr>
<td  colspan="8"  style="font-weight:bold;text-align:right;font-size:14px;color:red"><?=$cur;?></td>
<td  style="font-weight:bold;text-align:right;font-size:14px;color:red"> <?=$usd;?> </td>
</TR>
</tbody>
</table>

<div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div>
</DIV>


</body>
</html>
