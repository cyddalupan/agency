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
width: 100%;
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
IF($_GET['num']==1){
$title="Line Up";
$filter="applicant_status IN (5)";
}
IF($_GET['num']==2){
$title="PRE-SELECTED / RESERVED";
$filter="applicant_status IN (2,3)";
}

IF($_GET['num']==3){
$title="Selected";
$filter="applicant_status IN (4)";
}

IF($_GET['num']==4){
$title="FOR DEPLOYMENT / FOR BOOKING";
$filter="applicant_status IN (12,8)";
}

IF($_GET['num']==5){
$title="DEPLOYED";
$filter="applicant_status IN (9)";
}

IF($_GET['num']==6){
$title="CANCELLED / UNFIT / BACKOUT / BLACKLIST";
$filter="applicant_status IN (7,21,1,14)";
}


IF($_GET['num']==7){
$title="REPAT";
$filter="applicant_status IN (22)";
}

IF($_GET['num']==8){
$title="FINISHED CONTRACT";
$filter="applicant_status IN (23)";
}


include'../db.php';

?>


<h1 style="text-align:center"><?=$title?></h1>
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
where
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
<th>NAME</th>
<th style="width:30%">AGENT/PRO</th>
<th style="width:15%">LAST UPDATE</th>
<th style="width:10%">STATUS</th>
</tr>
<tr>

<?php
$count=1;
$fit =mysql_query("SELECT * FROM applicant
LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number
where
$filter
AND applicant.applicant_employer=".$row1['applicant_employer']."
order by applicant_updated desc ");

			while($row=mysql_fetch_array($fit))
			{
				if($row['applicant_status']=='4') {$color='orange';}
			if($row['applicant_status']=='3') {$color='green';}
			if($row['applicant_status']=='2') {$color='blue';}
			if($row['applicant_status']=='12') {$color='orange';}
			if($row['applicant_status']=='8') {$color='orange';}
			if($row['applicant_status']=='5') {$color='blue';}
			else{$color='black';}
			$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
			$employer=mysql_fetch_array($emp);
			$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
			$agentrow1=mysql_fetch_array($agent);

			echo'<tr>';
			echo'<td>'.$count.'</td>';
			echo'<td>'.$row["applicant_last"].', '.$row["applicant_first"].'</td>';
			echo'<td>'.$agentrow1["agent_first"].'  '.$agentrow1["agent_last"].'</td>';
			echo'<td>'.$row["applicant_updated"].'</td>';
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
