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

$title="";
$filter="requirement_visa_stamp   NOT IN ('0000-00-00','1970-01-01','')";

include'../db.php';

?>


<h1 style="text-align:center"><?=$title?> VISA STAMPING REPORTS</h1>
<h3 style="text-align:center">As of <?php echo date('d-F-Y', strtotime($date1)); ?></p></h3>

<div class="row">

<?php

$groupee =mysql_query("SELECT * FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant
where 
$filter
AND applicant_status NOT IN (9,21,22,14,1)
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
<th>NAME OF WORKER</th>
<th style="width:30%">AGENT/PRO</th>
<th>VISA STAMP</th>
<th>VISA EXPIRY</th>
<th>VISA #</th>
<th>SPONSOR ID</th>
<th>PT RESULT</th>
<th>PT DATE</th>
<th style="width:10%">STATUS</th>
<th>REMARKS</th>
</tr>
<tr>

<?php
$count=1;
$fit =mysql_query("SELECT * FROM applicant
LEFT JOIN 	applicant_requirement
ON applicant.applicant_id=applicant_requirement.requirement_applicant

LEFT JOIN 	applicant_certificate
ON applicant.applicant_id=applicant_certificate.certificate_applicant

LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number
where 
$filter
AND applicant_status NOT IN (9,21,22,14,1)
AND applicant.applicant_employer=".$row1['applicant_employer']."");

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
			echo'<td>'.$row["requirement_visa_stamp"].'</td>';
			echo'<td>'.$row["requirement_visa_expiration"].'</td>';
			echo'<td>'.$row["requirement_visa_no"].'</td>';
			echo'<td>'.$row["applicant_requirement_ecode"].'</td>';
			echo'<td>'.$row["certificate_pt_result"].'</td>';
echo'<td>'.$row["certificate_pt_result_date"].'</td>';			
			
			echo'<td><u style="color:'.$color.'">'.$row["statusText"].'</u></td>';
			echo'<td>'.$row["sub_status"].'</td>';
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
