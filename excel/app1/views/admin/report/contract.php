<?php 
$con = mysql_connect("localhost","iwebfram_land","^1PCS)XlQ{Hx");
mysql_set_charset ( "latin1_swedish_ci", $con );
if (!$con)
{
die('Could not connect: ' . mysql_error());
}
mysql_select_db("iwebfram_landbase", $con);

$pass =  mysql_query("SELECT * from applicant
LEFT JOIN applicant_requirement
ON applicant.applicant_id=applicant_requirement.applicant_id 


LEFT JOIN recruitment_agent
ON applicant.applicant_source=recruitment_agent.agent_id 

where 
applicant.sub_status='Waiting for Contract Signing'
");
$visa="FOR CONTRACT SIGNING";

$counter = 1; 
$today=date('Y-m-d');
?>
<?php include '../reports/style.php'; ?>
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
<td colspan="10">
<center>
<div style="clear:both;height:5px"></div>
<H1>NETWORK MANAGEMENT RESOURCES INC.</H1>	
<div style="clear:both;height:10px"></div>
</td>
</tr>

<tr id="tblname">
<td colspan="10"><h2> <?=$visa?></h2>
Printed: <?php echo date('F,d Y', strtotime($today)); ?>
<div style="clear:both;height:30px"></div>
</td>
</tr>	
<tr id="tblname">
<th>#</th>
<th>Date</th>
<th>Name</th>
<th>Contact</th>
<th>Agent</th>
<th>Status</th>


</tr>
<?php
while($row=mysql_fetch_array($pass)){
?>

<tr >
<td style="width:10px;color:red"><?php echo"".$counter;?></td>
<td id="bold"><?php echo date('F d, Y', strtotime($row['applicant_date_applied'])); ?></td>
<td><?=$row['applicant_first']?> <?=$row['applicant_last']?></td>
<td><?=$row['applicant_contacts']?></td>
<td><?=$row['agent_first']?><?=$row['agent_last']?></td>
<td style="color:Red"><?=$row['status']?>---<b style="color:black"><?=$row['sub_status']?></b></td>

<?php 
$counter++;
?>
</tr>

<?php } ?>

</table>
<div id="forbutton">


<input type="button" onclick="tableToExcel('testTable', 'Alumni Tracer')" value="Export to Excel">
</div>
