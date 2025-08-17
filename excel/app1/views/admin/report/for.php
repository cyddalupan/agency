<?php 
$con = mysql_connect("localhost","iwebfram_land","^1PCS)XlQ{Hx");
mysql_set_charset ( "latin1_swedish_ci", $con );
if (!$con)
{
die('Could not connect: ' . mysql_error());
}
mysql_select_db("iwebfram_landbase", $con);



if ($_GET['exp']==5){
$pass =  mysql_query("SELECT 
applicant.applicant_id as adonis, applicant_certificate.*,applicant_requirement.*,applicant.*,employer.* FROM applicant
LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_id

LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_id

LEFT JOIN employer
ON applicant.applicant_employer=employer.employer_id

where applicant.status='For Deployment'");
$visa="For Deployment ";
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
<H1><?php include'title.php';?> </H1>	
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
<th>Principal</th>
<th>Medical</th>
<th>Status</th>

<th>Deployment Date</th>


</tr>
<?php
while($row=mysql_fetch_array($pass)){
?>

<tr >
<td style="width:10px;color:red"><?php echo"".$counter;?></td>
<td id="bold"><?php echo date('F d, Y', strtotime($row['applicant_date_applied'])); ?></td>
<td><?=$row['applicant_first']?> <?=$row['applicant_last']?></td>
<td><?=$row['applicant_contacts']?></td>
<td><?=$row['employer_name']?></td>
<td>FIT TO WORK</td>
<td style="color:Red"><?=$row['status']?>--<b style="color:black"><?=$row['sub_status']?></b></td>

<td id="bold"><?php echo date('F d, Y', strtotime($row['flight'])); ?></td>
<?php 
$counter++;
?>
</tr>

<?php } ?>

</table>
<div id="forbutton">


<input type="button" onclick="tableToExcel('testTable', 'Alumni Tracer')" value="Export to Excel">
</div>
