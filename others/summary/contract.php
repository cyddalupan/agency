<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {background:#F5F5F5;
FONT-FAMILY:arial;
font-size:12px;
}
#customers {
font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
border-collapse: collapse;
width: 90%;
}

#customers td, #customers th {
border: 1px solid #ddd;
padding: 4px;
}

#customers tr:nth-child(even){background-color: white;}

#customers tr:hover {background-color: white;}

#customers th {
padding-top: 10px;
padding-bottom: 10px;
text-align: left;
background-color: #DCDCDC;
color: black;
}
</style>
</head>
<body>
<?PHP
include'tracking.php';
$monday=date("l");
if($monday=='Monday'){
$dateme=date_create($date1);
date_sub($dateme,date_interval_create_from_date_string("3 days"));
$dateko=date_format($dateme,"Y-m-d");
}
else{
$dateme=date_create($date1);
date_sub($dateme,date_interval_create_from_date_string("1 days"));
$dateko=date_format($dateme,"Y-m-d");
}



IF($_GET['num']==1){
$title="Today";
$filter="AND requirement_contract between date_sub(now(), interval 0 day)  AND date_add(now(), interval 1 day)";

}
IF($_GET['num']==2){
$title="Weekly";
$filter="AND requirement_contract between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day)";
}
IF($_GET['num']==3){
$title="Monthly";
$filter="AND requirement_contract between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)";
}

IF($_GET['num']==4){
$title="Yesterday";
$filter="AND requirement_contract=".$dateko."";
}

include'../db.php';

?>


<h1 style="text-align:center"><?=$title?> Contract Arrived</h1>
<h3 style="text-align:center">As of <?php echo date('d-F-Y', strtotime($date1)); ?></p></h3>
<p>
<button onclick="goBack()">Go Back</button>

<script>
function goBack() {
  window.history.back();
}
</script></p>
<div class="row">

<?php

$groupee = mysql_query("SELECT * FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where  applicant_status!=9
$filter
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
<th  style="width:30%">NAME</th>
<th>AGENT</th>
<th>FRA</th>
<th>Country</th>
<th >CONTRACT DATE</th>
<th >STATUS</th>
</tr>
<tr>

<?php
$count=1;
$fit =mysql_query("SELECT * FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

LEFT JOIN  	employer
ON applicant.applicant_employer =employer.employer_id

where 
 applicant.applicant_employer=".$row1['applicant_employer']."
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
			
			$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);
$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);

			echo'<tr>';
			echo'<td>'.$count.'</td>';
			echo'<td>'.$row["applicant_last"].', '.$row["applicant_first"].'</td>';
			echo'<td>'.$agentrow1["agent_first"].'  '.$agentrow1["agent_last"].'</td>';
					echo'<td>'.$row["employer_name"].'</td>';
			echo'<td>'.$countryrow["country_name"].'</td>';
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
