<style>
#wrapper{
border:0px solid black;
width:1000px;
min-height:630px;
height:!important;
margin: 0 auto;	
padding:3px;
font-size:13px;
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
padding:1px;
font-size:12px;
}
table th{
border:1px solid black;
font-weight:bold;
font-size:13px;
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

//$apps = mysql_query("SELECT * FROM applicant_view where applicant_status!=9 AND applicant_address LIKE '%".$_POST['place']."%'");

if($_POST['status']==99){
	$status1="ALL";
	$stat=" AND applicant_status IN  (4,12,8,10)";
}
if($_POST['status']==4){
	$status1="Selected";
	$stat="AND applicant_status=".$_POST['status']."";
}
if($_POST['status']==12){
	$status1="FOR BOOKING";
	$stat="AND applicant_status=".$_POST['status']."";
}

if($_POST['status']==8){
	$status1="FOR DEPLOYMENT";
	$stat="AND applicant_status=".$_POST['status']."";
}

if($_POST['status']==10){
	$status1="FOR REVIEW";
	$stat="AND applicant_status=".$_POST['status']."";
}


if($_POST['user']==999){
	
	$user="";
}

if($_POST['user']!=999){
	
	$user="AND applicant_createdby=".$_POST['user']."";
}



if($_POST['sub_status']==1){
	$sub_status="";
}

if($_POST['sub_status']!=1){
	$sub_status="AND  sub_status='".$_POST['sub_status']."'";
}


$apps = mysql_query("SELECT * FROM applicant where 
applicant_status!=99
$stat
$user
AND applicant_created between '".$_POST['date1']."' AND '".$_POST['date2']."'
order by applicant_employer asc
");
$counter = 1; 



?>

<div id="wrapper">

	
		<table id="testTable" style="width:1000px">
	
		
		<tr>
				<td colspan="10" style="padding:3px;border:0px solid black">
					<div id="clear"></div>
					<H1 STYLE="font-size:20px">LANDBASE HUMAN RESOURCES </H1>
					<h3><?=$status1?> Applicants</h3>
					
					<h4>FILTER DATE: <?php echo date('d-F-Y', strtotime($_POST['date1'])); ?>- 
					<?php echo date('d-F-Y', strtotime($_POST['date2'])); ?></h4>
					
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
		<th style="width:20px">Code</th>
		<th>Encoded Dtae</th>
		<th>NAME</td>
		<th style="width:100px">Contact</th>
		<th>Age</th>
		<th>Position</th>
		<th>Principal</td>
		<th>Status</th>
		<th>Encoded by</th>

		</tr>
		<?php
	
		while($row=mysql_fetch_array($apps)){

$packnamed = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']);
$row1 = mysql_fetch_array($packnamed);

$employer = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']);
$employerrow = mysql_fetch_array($employer);


$user = mysql_query("SELECT * FROM user where user_id=".$row['applicant_createdby']);
$userrow = mysql_fetch_array($user);

if($row['applicant_status']==4){
	$status="Selected";
}
if($row['applicant_status']==9){
	$status="Deployed";
}
if($row['applicant_status']==12){
	$status="FOR BOOKING";
}

if($row['applicant_status']==8){
	$status="FOR DEPLOYMENT";
}
if($row['applicant_status']==10){
	$status="FOR REVIEW";
}
if($row['applicant_status']==9){
	$status="DEPLOYED";
}



	
		
			
		?>

		<tr >
		<td style="width:20px;"><?php echo"".$counter;?></td>
		<td STYle="text-transform:uppercase">000<?=$row['applicant_id']?></td>
		<td style="width:140px;text-transform:uppercase"><?php echo date('d-F-Y', strtotime($row['applicant_created'])); ?></td>
		<td style="width:200px;text-transform:uppercase"><?=$row['applicant_first']?> <?=$row['applicant_middle']?> <?=$row['applicant_last']?>	</td>
		<td STYle="text-transform:uppercase"><?=$row['applicant_contacts']?></td>
		<td STYle="text-transform:uppercase"><?=$row['applicant_age']?></td>
		<td STYle="text-transform:uppercase"><?=$row1['position_name']?></td>
		<td STYle="text-transform:uppercase"><b><?=$employerrow['employer_name']?></b></td>
		<td STYle="text-transform:uppercase"><?=$status?> <small style="color:Red">
		</br><?=$row['sub_status']?></small></b></td>
		<td STYle="text-transform:uppercase"><?=$userrow['user_fullname']?></td>
		<?php
			$counter++;
			}
		?>
		</tr>

		</table>
		
		
<div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div>


<div id="clear" style="clear:both;height:25px"></div>
<div>
<input type="submit" onclick="tableToExcel('testTable', '<?=$type?>-<?=$_POST['datepick1']?>.<?=$_POST['datepick2']?>')" value="Export to Excel">
</div>
	
<div id="clear"></div><div id="clear"></div><div id="clear"></div><div id="clear"></div>
<div id="clear" style="clear:both;height:5px"></div>
</div>