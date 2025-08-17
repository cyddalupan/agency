<?php 
include'db.php';
mysql_select_db("iwebfram_landbase", $con);
if ($_GET['exp']==1){
$pass =  mysql_query("SELECT 

applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_id

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.applicant_id

LEFT JOIN recruitment_agent
ON applicant.applicant_source=recruitment_agent.agent_id 

where 
requirement_visa_expiration NOT IN('0000-00-00','1970-01-01')
AND applicant.status NOT IN ('Deployed','BACKOUT','UNFIT')
AND requirement_visa_expiration <= DATE_ADD(DATE(now()), INTERVAL 30 day)
AND applicant.status NOT IN('Deployed','BACKOUT','CANCELLED')
");

$visa="EXPIRED VISA";
}

if ($_GET['exp']==2){
$pass =  mysql_query("SELECT 
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.applicant_id

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.applicant_id

LEFT JOIN recruitment_agent
ON applicant.applicant_source=recruitment_agent.agent_id 

where 
applicant_certificate.certificate_owwa_from NOT IN('0000-00-00','1970-01-01')
AND applicant_certificate.certificate_owwa_from <= DATE_ADD(DATE(now()), INTERVAL 3 day)
AND applicant.status!='Deployed'
AND applicant.sub_status='OWWA Training'
");
$visa="OWWA SCHEDULE";
}

if ($_GET['exp']==3){
$pass =  mysql_query("SELECT 
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.applicant_id

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.applicant_id

LEFT JOIN recruitment_agent
ON applicant.applicant_source=recruitment_agent.agent_id 

where 
applicant_certificate.biometrics NOT IN('0000-00-00','1970-01-01')
AND applicant_certificate.biometrics <= DATE_ADD(DATE(now()), INTERVAL 3 day)
AND applicant.status!='Deployed'
AND applicant.sub_status='Biometrics Schedule'
");
$visa="BIOMETRICS SCHEDULE";
}

if ($_GET['exp']==4){
$pass =  mysql_query("SELECT 
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.* FROM applicant
LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.applicant_id

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.applicant_id

LEFT JOIN recruitment_agent
ON applicant.applicant_source=recruitment_agent.agent_id 

where 
applicant_requirement.oec_expired NOT IN('0000-00-00','1970-01-01')
AND applicant_requirement.oec_expired <= DATE_ADD(DATE(now()), INTERVAL 3 day)
AND applicant.status NOT IN('Deployed','BACKOUT','CANCELLED')
");
$visa="OEC EXPIRATION";
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
<H1><?php include'title.php';?></H1>	
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
<th>Visa Expired Date</th>
<th>OEC Expired Date</th>



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
<td style="color:Red"><?=$row['status']?>--<b style="color:black"><?=$row['sub_status']?></b></td>
<td><?=$row['requirement_visa_expiration']?></td>
<td><?=$row['oec_expired']?></td>


<?php 
$counter++;
?>
</tr>

<?php } ?>

</table>
<div id="forbutton">


<input type="button" onclick="tableToExcel('testTable', 'Alumni Tracer')" value="Export to Excel">
</div>
