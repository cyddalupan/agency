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




<?PHP
include'inc/functions.php';
include'tracking.php';

//
$app = mysql_query("SELECT email_address.*
,applicant.applicant_first
,applicant.applicant_middle
,applicant.applicant_last
FROM email_address
LEFT JOIN  	applicant
ON email_address.app_id = applicant.applicant_id


where request_payment=3 AND
account='".$_POST["account"]."' order by date_ad desc");
if(isset($_POST['filter']))
{	
	$app = mysql_query("SELECT *FROM email_address
	where request_payment=3 AND
	account='".$_POST["account"]."'
	AND date_ad between '".$_POST['datefrom']."' AND '".$_POST['dateto']."' order by date_ad desc");
}

?>
<div id="wrapper">

<form id="demo-form2"  method="post">
<table style="width:500px;border:none">
	<tr>
	<input type='HIDDEN' name='account' value='<?=$_POST['account']?>' readonly>
		<td style="border:none">Date From: <input type="date"  class="form-control" name="datefrom" required  value="<?=$date2?>"></td>
		<td style="border:none">Date To: <input type="date"  class="form-control" name="dateto" required   value="<?=$date2?>"></td>
		<td style="border:none"><button type="submit" class="btn btn-info"  name="filter" >Filter</button></td>
	</tr>
</table>
</form>
<div id="clear"></div></br>


<h1><?=$_GET['set']?> Expense</h1>
<div id="clear"></div>


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




<h3>AS of <?php echo date('Y-M-d', strtotime($date2)); ?> </h3>
<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:14px;padding:5px">Export to excel</a>

<table class="table table-bordered" id="customers" >
<thead>
<tr>
<th STYLE="">#</th>
<th STYLE="width:3%">Ref#</th>
<th STYLE="width:10%">Supplier</th>
<th STYLE="width:10%">DATE</th>
<th STYLE="width:10%">PAYEE</th>
<th>APPLICANT</th>
<th STYLE="width:10%">Account</th>
<th STYLE="width:10%">C/O Staff</th>
<th >PARTICULAR</th>
<th STYLE="width:5%">CURRENCY</th>
<th STYLE="width:5%">AMOUNT </th>
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

//NEW

$sup = mysql_query("SELECT * FROM  suppliers where sup_id=".$row['sup_id']." ");
$sup1=mysql_fetch_array($sup);



echo'<tr>';
echo'<td>'.$counter1.'</td>';
echo'<td>000'.$row["e_id"].'</td>';
echo'<td>'.$sup1["codes"].'-'.$sup1["name"].' <br><i style="color:RED;backgroun:lightgray;FONT-SIZE:10PX">'.$sup1["sup_type"].'</si></td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['date_ad']));echo'</td>';

echo'<td>'.$row2["agent_first"].' '.$row2["agent_last"].'</td>';

echo'<td STYLE="Color:black;font-weight:bold;font-size:14px">'.$row["applicant_first"].' '.$row["applicant_last"].'</td>';


echo'<td>'.$row["account"].'</td>';
echo'<td STYLE="color:green"><a href="view_liq.php?e_id='.$rowu["e_id"].' && name='.$rowu["user_fullname"].'" target="_blank" ">'.$rowu["user_fullname"].'</td>';
echo'<td>'.$row["description"].'</td>';
echo'<td>'.$row["currency"].'</td>';
echo'<td>'.$row["amount"].'</td>';

echo'<td> '.$ded.'</td>';
echo'</tr>';
$counter1++;
}
$php = number_format($totalamountPHP, 2);
?>

<tr>
<td  colspan="11"  style="font-weight:bold;text-align:right;font-size:14px;color:red"><?PHP ECHO $_POST['currency'];?></td>
<td  style="font-weight:bold;text-align:right;font-size:14px;color:red"> <?=$php;?> </td>
</TR>
</tbody>
</table>








</body>
</html>
