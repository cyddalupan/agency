<?php 
include '../inc/functions.php';
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
<H1>NETWORK MANAGEMENT RESOURCES INC.</H1>
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
<th>PASSPORT #</th>
<th>COUNTRY</th>
<th>Employer</th>
<th>STATUS</th>
<th>STATUS REMARKS</th>
<th>DATE UPDATED</th>

</tr>
<?php
$statu1 = mysql_query("SELECT * from _status");
while($statrows=mysql_fetch_array($statu1)){
$counter = 1; 
$pass = mysql_query("SELECT * from email_address2	
where status='".$statrows['status_name']."' 
AND agent_id='".$_POST['agent_id']."'
" );
echo '	<tr >
<td colspan="12" style="font-weight:bold;background:lightgray;color:black;padding:8px;font-size:14px
">'.$statrows['status_name'].'</td></tr>';
while($row=mysql_fetch_array($pass)){

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);

$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['counter_part']." ");
$emprow=mysql_fetch_array($emp);

?>

<tr >
<td style="width:10px;color:red;font-weight:bold"><?php echo"".$counter;?></td>
<td><?=$row['app_id']?></td>
<td id="bold"><?php echo date('F d, Y', strtotime($row['date_applied'])); ?></td>
<td style="width:100px;color:black"><?=$row1["agent_first"]?> <?=$row1["agent_last"]?></td>
<td id="bold"><?=$row['name']?> <?=$row['lname']?>	</td>
<td><?=$row['passport']?></td>
<td><?=$row['country']?></td>
<td><?=$emprow['employer_name']?></td>
<td id="red"><?=$row['status']?></td>
<td id="red"><?=$row['remarks']?></td>
<?php
if($statrows['status_name']=='Deployed' || $statrows['status_name']=='For Booking' || 
$statrows['status_name']=='Selected' || $statrows['status_name']=='Cancelled' || $statrows['status_name']=='Pending'
|| $statrows['status_name']=='For Passporting')
{
echo'<td id="bold" style="font-weight:bold">';echo date('F d, Y', strtotime($row['status_date']));echo'&nbsp;&nbsp;&nbsp;(Status Date)</td>';
}
if($statrows['status_name']=='Available')
{
echo'<td id="bold" style="font-weight:bold">';echo date('F d, Y', strtotime($row['date_applied']));echo'&nbsp;&nbsp;&nbsp;(Date Applied)</td>';
}

$counter++;
?>
</tr>

<?php }

}
?>

</table>
<div id="forbutton">





<input type="button" onclick="tableToExcel('testTable', 'Agent Reports')" value="Export to Excel">
</div>
