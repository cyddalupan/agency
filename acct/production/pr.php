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
where 
date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND currency='PHP'");
$phprow=mysql_fetch_array($PHP);

$USD = mysql_query("SELECT sum(amount) as USDTOTAL FROM email_address
where 
date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND currency='USD'");
$usdrow=mysql_fetch_array($USD);


$app = mysql_query("SELECT *FROM email_address
where 
date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND currency='PHP'");

$app1 = mysql_query("SELECT * FROM email_address
where 
date_ad between '".$_POST['start']."' AND '".$_POST['end']."'
AND currency='USD'");

$phpfinal = number_format($phprow['PHPTOTAL'], 2);
$usdfinal = number_format($usdrow['USDTOTAL'], 2);

?>
<div id="wrapper">
<h1>CASH FLOW AS OF </h1>
<div id="clear"></div>
<p style="color:BLACK;margin-top:-10px;text-align:center;font-size:14px">Start: <?php echo date('Y-M-d', strtotime($_POST['start'])); ?>  
</p>
<div id="clear"></div>
<p style="color:BLACK;margin-top:-18px;text-align:center;font-size:14px">End: <?php echo date('Y-M-d', strtotime($_POST['end'])); ?></p>
<div id="clear"></div>

<p style="color:BLACK;margin-top:-22px;text-align:lef;font-size:18px">TOTAL PHP  <B><?=$phpfinal?></B></p>
<div id="clear"></div>

<p style="color:BLACK;margin-top:-24px;text-align:lef;font-size:18px">TOTAL USD  <B><?=$usdfinal?></B></p>
<div id="clear"></div>


<script>
//code to export html to excel coded by:chachi yr: 2000
//no need to edit bellow
var tableToExcel = (function() {
  var uri = 'data:application/vnd.ms-excel;base64,'
    , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
    , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
    , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
  return function(table, name) {
    if (!table.nodeType) table = document.getElementById(table)
    var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
    window.location.href = uri + base64(format(template, ctx))
  }
})()

</script>



<table class="table table-bordered" id="testTable">
<thead>
<tr>
<th STYLE="">#</th>
<th STYLE="width:10%">DATE</th>
<th STYLE="width:10%">REF NO.</th>
<th >PARTICULAR</th>
<th STYLE="width:5%">CURRENCY</th>
<th STYLE="width:5%">AMOUNT</th>

</tr>
</thead>
<tbody>
<?php	
$counter1 = 1; 
while($row=mysql_fetch_array($app))
{
$totalamountPHP=$row['amount']+$totalamountPHP;
echo'<tr>';
echo'<td>'.$counter1.'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['date_ad']));echo'</td>';

echo'<td>'.$row["ref"].'</td>';
echo'<td>'.$row["description"].'</td>';
echo'<td>'.$row["currency"].'</td>';
echo'<td>'.$row["amount"].'</td>';
echo'</tr>';
$counter1++;
}
$php = number_format($totalamountPHP, 2);
?>

<tr>
<td  colspan="5"  style="font-weight:bold;text-align:right;font-size:14px;color:red">PHP</td>
<td  style="font-weight:bold;text-align:right;font-size:14px;color:red"> <?=$php;?> </td>
</TR>
</tbody>
</table>





</BR></BR></BR>

<table class="table table-bordered">
<thead>
<tr>
<th>#</th>
<th STYLE="width:10%">DATE</th>
<th STYLE="width:10%">REF NO.</th>
<th >DESCRIPTION</th>
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
echo'<tr>';
echo'<td>'.$counter.'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row1['date_ad']));echo'</td>';

echo'<td>'.$row1["ref"].'</td>';
echo'<td>'.$row1["description"].'</td>';
echo'<td>'.$row1["currency"].'</td>';
echo'<td>'.$row1["amount"].'</td>';
echo'</tr>';
$counter++;
}

$usd = number_format($totalamountUSD, 2);
?>
<tr>
<td  colspan="5"  style="font-weight:bold;text-align:right;font-size:14px;color:red">USD</td>
<td  style="font-weight:bold;text-align:right;font-size:14px;color:red"> <?=$usd;?> </td>
</TR>
</tbody>
</table>

<div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div>
</DIV>

<input type="button" onclick="tableToExcel('testTable', 'Alumni Tracer')" value="Export to Excel">

</body>
</html>
