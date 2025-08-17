<?php 
$con = mysql_connect("localhost","iwebfram_land","^1PCS)XlQ{Hx");
mysql_set_charset ( "latin1_swedish_ci", $con );
if (!$con)
{
die('Could not connect: ' . mysql_error());
}
mysql_select_db("iwebfram_landbase", $con);
include '../reports/style.php';

$today=date('Y-m-d');
?>
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
<table class="gridtable" id="testTable">
<tr id="tblname">
<td colspan="12">
<center>
<div style="clear:both;height:5px"></div>
<H1>CB & T - STAFF,INC.</H1>
<div style="clear:both;height:10px"></div>
</td>
</tr>

<tr id="tblname">
<td colspan="12"><h2></h2>
Printed: <?php echo date('F,d Y', strtotime($today)); ?>
</td>
</tr>	
<tr id="tblname">
<th>#</th>
<th>Applicant#</th>
<th>DATE APPLIED</th>
<th>AGENT</th>
<th>NAME</th>
<th>COUNTRY</th>
<th>PRINCIPAL</th>
<th>STATUS</th>
<th>STATUS REMARKS</th>
<th>FLIGHT DATE</th>

</tr>
<?php
$statu1 = mysql_query("SELECT * from _status");
while($statrows=mysql_fetch_array($statu1)){
$counter = 1; 
$pass = mysql_query("SELECT * from applicant	
where status='".$statrows['status_name']."' 
AND applicant_source='".$_POST['agent_id']."'
" );
echo '	<tr >
<td colspan="12" style="font-weight:bold;background:lightgray;color:black;padding:8px;font-size:14px
">'.$statrows['status_name'].'</td></tr>';
while($row=mysql_fetch_array($pass)){

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$_POST['agent_id']." ");
$row1=mysql_fetch_array($app1);

$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$emprow=mysql_fetch_array($emp);

?>

<tr >
<td style="width:10px;color:red;font-weight:bold"><?php echo"".$counter;?></td>
<td><?=$row['applicant_id']?></td>
<td id="bold"><?php echo date('F d, Y', strtotime($row['applicant_date_applied'])); ?></td>
<td style="width:100px;color:black"><?=$row1["agent_first"]?> <?=$row1["agent_last"]?></td>
<td id="bold"><?=$row['applicant_first']?> <?=$row['applicant_last']?>	</td>
<td><?=$row['applicant_preferred_country']?></td>
<td><?=$emprow['employer_name']?></td>
<td id="red"><?=$row['status']?>-<b style="color:black"><?=$row['sub_status']?></b></td>
<td id=""><?=$row['applicant_remarks']?></td>
<td id="red"><?=$row['flight']?></td>

</tr>

<?php 
$counter++;
}

}
?>

</table>
<div id="forbutton">





<input type="button" onclick="tableToExcel('testTable', 'Agent Reports')" value="Export to Excel">
</div>
