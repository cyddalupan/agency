<?php 
session_start();
if(!isset($_SESSION['admin']['user'])){
header("location:noaccess.php");
}
	include 'db.php';
	include 'tracking.php';


?>
<?php
$counter=1;
include'db1.php';

if(isset($_POST['add_agree']))
{
		$app = mysql_query("UPDATE applicant
		SET mystatus=2
		WHERE applicant_id=".$_POST['applicant_id']." "); 
	
	$insert=mysql_query("insert into subject (app_name,fra_name,app_contact,app_country,applicant_id,app_last,app_deployed,app_remarks,app_position,passport,oec,agent,sub,app_action) values
('".$_POST['app_name']."','".$_POST['fra_name']."','".$_POST['app_contact']."'
,'".$_POST['app_country']."','".$_POST['applicant_id']."','".$_POST['app_last']."','".$_POST['app_last']."','Deployed','".$_POST['app_position']."','".$_POST['passport']."','".$_POST['oec']."','".$_POST['agent']."','".$_POST['sub']."','".$_POST['app_last']."')");
	
echo"<P STYLE='COLOR:white;FONT-SIZE:18PX;padding:8px;margin-left:200px;background:GREEN;width:200px;text-align:center;font-weight:bold;border:1px solid black'>Save to OWMS</P>";

}	

if(isset($_POST['declined']))
{
	
$app = mysql_query("UPDATE applicant
SET mystatus=1
WHERE applicant_id=".$_POST['applicant_id']." "); 


	
echo"<P STYLE='COLOR:white;FONT-SIZE:18PX;padding:8px;margin-left:200px;background:red;width:200px;text-align:center;font-weight:bold;border:1px solid black'>Declined Transaction</P>";
}


                    $app = mysql_query("SELECT * FROM applicant
                    LEFT JOIN  statuses
                    ON applicant.applicant_status = statuses.number
                    
                    LEFT JOIN  	applicant_certificate
                    ON applicant.applicant_id = applicant_certificate.certificate_applicant
                    
                    
                    LEFT JOIN  	applicant_requirement
                    ON applicant.applicant_id =applicant_requirement.requirement_applicant
                    
                    LEFT JOIN  	employer
                    ON applicant.applicant_employer =employer.employer_id
                    
                    LEFT JOIN  	recruitment_agent
                    ON applicant.applicant_source =recruitment_agent.agent_id
                    
                  
                    
                    LEFT JOIN  applicant_passport
                    ON applicant.applicant_id =	applicant_passport.passport_applicant
                    where
                    applicant_status=9
                    AND mystatus=0
                     limit 30 ");
?>




<style>
#customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 90%;
  margin:30px;
  font-size:12px;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding:3px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}
</style>

<H2>Deployed</H2>
<table id="customers">
<thead>
<tr>
<th>#</th>
<th style=""> Date</th>
<th style="">Deployment Date</th>
<th style="">Name</th>
<th style="">Position</th>

<th style="">FRA</th>
<th style="">Country</th>
<th style="">Passport</th>
<th style="">OEC</th>
<th style="">AGENT</th>

<th style="">SUB AGENT</th>

<th style="">Confirm</th>
<th style="">Declined</th>


								
</tr>
</thead>
<tbody>
   		
<?php
$counter=1;
while($row=mysql_fetch_array($app))
{			

?>
<form action="" method="post"   style="width:45%;float:Left;min-height:700px;margin:5px">
<input type="hidden" id="app_name" name="app_name" value="<?=$row['applicant_last']?>,<?=$row['applicant_first']?> <?=$row['applicant_middle']?>" >
<input type="hidden" id="app_contact" name="app_contact" value="<?=$row['applicant_contacts']?>, <?=$row['applicant_contacts_2']?>"  >
<input type="hidden" id="fra_name" name="fra_name" value="<?=$employer['employer_name']?>"  >
<input type="hidden" id="app_country" name="app_country" value="<?=$countryrow['country_name']?>">
<input type="hidden" id="applicant_id" name="applicant_id" value="<?=$row['applicant_id']?>">
<input type="hidden" id="app_last" name="app_last" value="<?=$row['flight_date']?>">
<input type="hidden" id="app_position" name="app_position" value="<?=$position1['position_name']?>">

<input type="hidden" id="passport" name="passport" value="<?=$row['passport_number']?>">
<input type="hidden" id="oec" name="oec" value="<?=$row['requirement_oec_number']?>">
<input type="hidden" id="agent" name="agent" value="<?=$row['agent_first']?> <?=$row['agent_last']?>">
<input type="hidden" id="sub" name="sub" value="<?=$row['other_source']?>">
<?php
$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$employer=mysql_fetch_array($emp);

$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);

$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);

echo"<tr>";
echo"<td>".$counter."</td>";
echo"<td>".$row['applicant_date_applied']."</td>";
echo"<td>".$row['flight_date']."</td>";
echo"<td>".$row['applicant_last'].", ".$row['applicant_first']." ".$row['applicant_middle']."</td>";
echo"<td>".$position1['position_name']."</td>";
echo"<td>".$employer['employer_name']."</td>";
echo'<td>'.$countryrow["country_name"].'</td>';
echo"<td>".$row['passport_number']."</td>";
echo"<td>".$row['requirement_oec_number']."</td>";
echo"<td>".$row['agent_first']." ".$row['agent_last']."</td>";
echo"<td>".$row['other_source']."</td>";
?>

<td><a href="delete_methode_link" onclick="return confirm('Are you sure you want to SAVE ?');"> 
<button type="submit" class="btn btn-success pull-right"  name="add_agree" style="padding:8px;font-size:12px;background:green;color:white;border:1pxsolid black">Confirm</button>
</a></td>

<td><a href="delete_methode_link" onclick="return confirm('Are you sure you want to SAVE?');"> 
<button type="submit" class="btn btn-success pull-right"  name="declined" style="padding:8px;font-size:12px;background:Red;color:white;border:1pxsolid black">Declined</button>
</a></td>
</form>
<?php
echo"</tr>";
$counter++;
}				
?>	

</tbody>
</table>

 