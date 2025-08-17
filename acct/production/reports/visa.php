<?php 
include '../inc/functions.php';
if ($_GET['exp']==1){
$pass =  mysql_query("SELECT * from applicant_requirement
LEFT JOIN email_address2
ON applicant_requirement.app_id=email_address2.app_id 

LEFT JOIN recruitment_agent
ON email_address2.agent_id=recruitment_agent.agent_id 

where 
applicant_requirement.requirement_visa_expiration NOT IN('0000-00-00','1970-01-01')
AND applicant_requirement.requirement_visa_expiration <= DATE_ADD(DATE(now()), INTERVAL 20 day)
");
$visa="EXPIRED VISA";
}
if ($_GET['exp']==2){
$pass = mysql_query("SELECT applicants_list.dateapplied,applicants_list.p_id,applicants_list.fname,
applicants_list.lname,applicants_list.contact1,applicants_list.contact2,
applicants_list.app_remarks,applicants_list.app_status,
d_settings.p_id ,d_settings.calling_visa,d_settings.visa_expired,d_settings.contract,
d_settings.visa_rel,category.*,country.* ,applicants_list.ecode
FROM applicants_list
LEFT JOIN d_settings
ON applicants_list.p_id=d_settings.p_id 
LEFT JOIN category
ON applicants_list.cat_id=category.cat_id
LEFT JOIN country
ON applicants_list.country_id=country.country_id

where  applicants_list.stat_del=1
AND d_settings.calling_visa!='' 
AND applicants_list.app_status!='Deployed'
order by d_settings.visa_rel desc");
$visa="WITH VISA";
$applicant="AND d_settings.calling_visa!='' ";

}
if(isset($_POST['searchnow']))
{
IF ($_POST['start']!=''){$datefilter="AND applicants_list.date_deployed between '".$_POST['start']."' AND '".$_POST['end']."'";}
IF ($_POST['start']==''){$datefilter="";}
$pass = mysql_query("SELECT applicants_list.dateapplied,applicants_list.p_id,applicants_list.fname,
applicants_list.lname,applicants_list.contact1,applicants_list.contact2,
applicants_list.app_remarks,applicants_list.app_status,
d_settings.p_id ,d_settings.calling_visa,d_settings.visa_expired,d_settings.contract,
d_settings.visa_rel,category.*,country.* ,applicants_list.ecode
FROM applicants_list
LEFT JOIN d_settings
ON applicants_list.p_id=d_settings.p_id 
LEFT JOIN category
ON applicants_list.cat_id=category.cat_id
LEFT JOIN country
ON applicants_list.country_id=country.country_id

where  applicants_list.stat_del=1
$applicant
$datefilter
AND applicants_list.country_id=".$_POST['country_id']."
AND applicants_list.app_status!='Deployed'
order by d_settings.visa_rel desc");
$visa="WITH VISA";
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

<?php 
$counter++;
?>
</tr>

<?php } ?>

</table>
<div id="forbutton">


<input type="button" onclick="tableToExcel('testTable', 'Alumni Tracer')" value="Export to Excel">
</div>
