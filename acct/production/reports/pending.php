<?php 
include '../inc/functions.php';
if ($_GET['exp']==1){
$pass =  mysql_query("SELECT * from email_address2
LEFT JOIN applicant_requirement
ON email_address2.app_id=applicant_requirement.app_id 

LEFT JOIN recruitment_agent
ON email_address2.agent_id=recruitment_agent.agent_id 

where 
email_address2.status='UNFIT'
");
$visa="UNFIT APPLICANTS";
}

if(isset($_POST['searchnow']))
{
IF ($_POST['start']!=''){$datefilter="AND email_address2.status_date between '".$_POST['start']."' AND '".$_POST['end']."'";}
IF ($_POST['start']==''){$datefilter="";}
$pass = mysql_query("SELECT * from email_address2
LEFT JOIN applicant_requirement
ON email_address2.app_id=applicant_requirement.app_id 

LEFT JOIN recruitment_agent
ON email_address2.agent_id=recruitment_agent.agent_id 

where 
email_address2.status='UNFIT'
$datefilter
");
$visa="UNFIT APPLICANTS";
}





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
<th>Expired</th>
<th>Status Date</th>


</tr>
<?php
while($row=mysql_fetch_array($pass)){
?>

<tr >
<td style="width:10px;color:red"><?php echo"".$counter;?></td>
<td id="bold"><?php echo date('F d, Y', strtotime($row['date_applied'])); ?></td>
<td><?=$row['name']?> <?=$row1['lname']?>-<?=$row1['app_id']?></td>
<td><?=$row['contact']?></td>
<td><?=$row['agent_first']?><?=$row['agent_last']?></td>
<td style="color:Red"><?=$row['status']?></td>
<td><?=$row['requirement_visa_expiration']?></td>
<td id="bold"><?php echo date('F d, Y', strtotime($row['status_date'])); ?></td>

<?php 
$counter++;
?>
</tr>

<?php } ?>

</table>
<div id="forbutton">

<form method="post" action="">
FILTER BY STATUS DATE

<?php
echo'Start Date: &nbsp;&nbsp; <input type="date" name="start" placeholder="Date Start" >
&nbsp; End Date:  &nbsp;&nbsp; <input type="date" name="end" placeholder="Date End" VALUE="'.date('Y-m-d').'" >
<button class="btn btn-warning" name="searchnow">Search</button>
';

?>
</form>
<input type="button" onclick="tableToExcel('testTable', 'Alumni Tracer')" value="Export to Excel">
</div>
