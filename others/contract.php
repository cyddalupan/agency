<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {background:#F5F5F5;
FONT-FAMILY:arial;
}
#customers {
font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
border-collapse: collapse;
width: 95%;
}

#customers td, #customers th {
border: 1px solid #ddd;
padding: 4px;
}

#customers tr:nth-child(even){background-color: white;}

#customers tr:hover {background-color: white;}

#customers th {
padding-top: 8px;
padding-bottom: 8px;
text-align: left;
background-color: #DCDCDC;
color: black;
}
</style>
</head>
<body>
<?PHP
IF($_GET['num']==1){
$title="Today";
$filter="AND requirement_contract between date_sub(now(), interval 1 day)  AND date_add(now(), interval 1 day)";

}
IF($_GET['num']==2){
$title="Weekly";
$filter="AND requirement_contract between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day)";
}
IF($_GET['num']==3){
$title="Monthly";
$filter="AND requirement_contract between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)";
}
include'../db.php';

?>


<h1 style="text-align:center"><?=$title?> Contract Arrived</h1>
<p><a href="../views_m.php">Back</a></p>
<div class="row">

<?php

$groupee = mysql_query("SELECT * FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where applicant.applicant_status IN (4,5)
group by applicant.applicant_employer");

while($row1=mysql_fetch_array($groupee))
{
$emp1 = mysql_query("SELECT * FROM employer where employer_id=".$row1['applicant_employer']." ");
$employer1=mysql_fetch_array($emp1);
?>


<div class="col-md-12 col-sm-12" style="margin-top:-1px;margin:10px;">
<div class="row" style="floar:left;background:white;padding:10px;min-height:100px">
<h4 style="text-align:left"><?=$employer1['employer_name']?></h4>
<table id="customers">
<tr>
<th style="width:3%">#</th>
<th>NAME</th>
<th style="width:30%">AGENT/PRO</th>
<th style="width:15%">CONTRACT DATE</th>
<th style="width:10%">STATUS</th>
</tr>
<tr>

<?php
$count=1;
$fit =mysql_query("SELECT * FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

where applicant.applicant_status IN (4,5)
AND applicant.applicant_employer=".$row1['applicant_employer']."
$filter
");

			while($row=mysql_fetch_array($fit))
			{
				if($row['applicant_status']=='4') {$color='orange';}
			if($row['applicant_status']=='3') {$color='green';}
			if($row['applicant_status']=='2') {$color='blue';}
			if($row['applicant_status']=='12') {$color='orange';}
			if($row['applicant_status']=='8') {$color='orange';}
			if($row['applicant_status']=='5') {$color='blue';}
			$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
			$employer=mysql_fetch_array($emp);
			$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
			$agentrow1=mysql_fetch_array($agent);

			echo'<tr>';
			echo'<td>'.$count.'</td>';
			echo'<td>'.$row["applicant_last"].', '.$row["applicant_first"].'</td>';
			echo'<td>'.$agentrow1["agent_first"].'  '.$agentrow1["agent_last"].'</td>';
			echo'<td>'.$row["requirement_contract"].'</td>';
			echo'<td><u style="color:'.$color.'">'.$row["statusText"].'</u></td>';
			echo'</tr>';
			$count++;

			}
			ECHO'</table>';
			echo'</div>';
			ECho'<div style="clear:both; height:10px;"></div>';
			echo'</div>';
		

}
	
		
?>		


		
		

  
  
  
</div>

</body>
</html>
