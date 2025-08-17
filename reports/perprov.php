<style>
#wrapper{
border:1px solid black;
width:1000px;
min-height:630px;
height:!important;
margin: 0 auto;	
padding:10px;
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
font-size:11px;
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

<?php 
include 'db.php';
include 'excel.php';
@set_time_limit(3600); //1 hour
@ini_set('max_execution_time', 3600); //1 hour
@mysql_query("SET SQL_BIG_SELECTS=1") or exit('SQL_BIG_SELECTS error: '.mysql_error());
@mysql_query("SET SESSION SQL_BIG_SELECTS=1") or exit('SESSION SQL_BIG_SELECTS error: '.mysql_error());
@mysql_query("SET MAX_JOIN_SIZE=10000000000") or exit('MAX_JOIN_SIZE error: '.mysql_error());
/*$resultset=array();
$apps2ss = mysql_query("SELECT * FROM agency where agent ='".$_POST['agent']."' order by name desc ");
while($row = mysql_fetch_array($apps2ss)) { 
$resultset[] = $row['name'];
}
$comma_separated = implode("','",$resultset);*/

$apps = mysql_query("SELECT * FROM applicant where applicant_status!=9 AND applicant_address LIKE '%".$_POST['place']."%'");
$counter = 1; 


?>

<div id="wrapper">

	
		<table id="testTable" style="width:900px">
	
		
		<tr>
				<td colspan="8" style="padding:3px;border:0px solid black">
					<div id="clear"></div>
					<H1 STYLE="font-size:26px">Abba Personnel Services Incorporated </H1>
					
				</td>	
		</tr>
<tr>
				<td colspan="8" style="padding:3px;border:0px solid black">
					<div id="clear"></div>
					<H1 STYLE="font-size:22px"><?=$_POST['place'];?> </H1>
				</td>	
		</tr>
		
		
		<tr>
		<th style="width:20px">#</th>
		<th>Date Applied</th>
		<th>NAME</td>
		<th style="width:100px">Contact</th>
		<th>Age</th>
		<th>Position</th>
		<th>Address</th>
		<th>Status</th>
		

		</tr>
		<?php
	
		while($row=mysql_fetch_array($apps)){

$packnamed = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']);
$row1 = mysql_fetch_array($packnamed);
if($row['applicant_status']==0){
	$status="Available";
}
if($row['applicant_status']==1){
	$status="Cancelled";
}
if($row['applicant_status']==6){
	$status="Qualified";
}
if($row['applicant_status']==10){
	$status="For Review";
}		

if($row['applicant_status']==13){
	$status="Active File";
}
	
		
			
		?>

		<tr >
		<td style="width:20px;"><?php echo"".$counter;?></td>
		<td style="width:140px;text-transform:uppercase"><?php echo date('d-F-Y', strtotime($row['applicant_date_applied'])); ?></td>
		<td style="width:200px;text-transform:uppercase"><?=$row['applicant_first']?> <?=$row['applicant_middle']?> <?=$row['applicant_last']?>	</td>
		<td STYle="text-transform:uppercase"><?=$row['applicant_contacts']?></td>
		<td STYle="text-transform:uppercase"><?=$row['applicant_age']?></td>
		<td STYle="text-transform:uppercase"><?=$row1['position_name']?></td>
		<td STYle="text-transform:uppercase"><?=$row['applicant_address']?></td>
		<td STYle="text-transform:uppercase"><?=$status?></b></td>

		<?php
			$counter++;
			}
		?>
		</tr>

		</table>
		
		
<div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div>
<div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div>
<div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div>

<DIV style="width:400px;text-align:left;padding:10px;float:left">
</br>
Prepared By: ____________________________________
</DIV>
<DIV style="width:500px;text-align:right;padding:10px;float:left;padding-left:50px">
Received By: ____________________________________

</DIV>
<div id="clear" style="clear:both;height:25px"></div>
<div>
<input type="submit" onclick="tableToExcel('testTable', '<?=$type?>-<?=$_POST['datepick1']?>.<?=$_POST['datepick2']?>')" value="Export to Excel">
</div>
	
<div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div>
<div id="clear" style="clear:both;height:5px"></div>
</div>