<style>
#wrapper{
border:0px solid black;
width:1200px;
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
if($_POST['status']==4){
	$status1="Selected";
}
if($_POST['status']==9){
	$status1="Deployed";
}

if($_POST['user']==999){
	
	$user="";
}

if($_POST['user']!=999){
	
	$user="AND applicant_createdby=".$_POST['user']."";
}

$apps =mysql_query("SELECT * FROM applicant
LEFT JOIN position
ON applicant.applicant_preferred_position=position.position_id
LEFT JOIN country
ON applicant.applicant_preferred_country=country.country_id

LEFT JOIN applicant_passport
ON applicant.applicant_id=applicant_passport.passport_id

LEFT JOIN applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_id

LEFT JOIN applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_id
where applicant_status=".$_POST['status']."
$user
 order by applicant_employer asc
");
$counter = 1; 


?>

<div id="wrapper">

	
		<table id="testTable">
	
		
		<tr>
				<td colspan="16" style="padding:3px;border:0px solid black">
					<div id="clear"></div>
					<H1 STYLE="font-size:20px">LANDBASE HUMAN RESOURCES </H1>
					<h3><?=$status1?> Applicants</h3>
					
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
		<th>Date Applied</th>
		<th>NAME</td>
		<th style="width:100px">Contact</th>
		<th>Age</th>
		<th>Position</th>
		<th>Passport</th>
		<th>Address</th>
		<th>Principal</td>
		<th>Salary</td>
	
		<th>Contract Duration</td>
		<th>Status</th>
		<th>Date Deployed</th>
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

$deployed = mysql_query("SELECT * FROM deployed where deployed_applicant=".$row['applicant_id']);
$deployedrow = mysql_fetch_array($deployed);




if($row['applicant_status']==4){
	$status="Selected";
}
if($row['applicant_status']==9){
	$status="Deployed";
}




	
		
			
		?>

		<tr >
		<td style="width:20px;"><?php echo"".$counter;?></td>
		<td STYle="text-transform:uppercase">000<?=$row['applicant_id']?></td>
		<td style="width:140px;text-transform:uppercase"><?php echo date('d-F-Y', strtotime($row['applicant_date_applied'])); ?></td>
		<td style="width:200px;text-transform:uppercase"><?=$row['applicant_first']?> <?=$row['applicant_middle']?> <?=$row['applicant_last']?>	</td>
		<td STYle="text-transform:uppercase"><?=$row['applicant_contacts']?></td>
		<td STYle="text-transform:uppercase"><?=$row['applicant_age']?></td>
			<td STYle="text-transform:uppercase"><?=$row['position_name']?></td>
		<td STYle="text-transform:uppercase"><?=$row['passport_number']?></td>
		<td STYle="text-transform:uppercase;width:220px;"><?=$row['applicant_address']?></td>
		<td STYle="text-transform:uppercase;width:220px;"><b><?=$employerrow['employer_name']?></b></td>
		<td STYle="text-transform:uppercase"><?=$row['requirement_offer_salary']?></td>
		<td STYle="text-transform:uppercase"><?=$row['requirement_contract']?></td>
		
		<td STYle="text-transform:uppercase"><?=$status?></td>
		<td style="width:140px;text-transform:uppercase"><?php echo date('d-F-Y', strtotime($deployedrow['deployed_date'])); ?></td>
		
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