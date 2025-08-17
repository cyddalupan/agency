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
$title="Today";
$filter="AND date_added between date_sub(now(), interval 0 day)  AND date_add(now(), interval 1 day)";

}
IF($_GET['num']==2){
$title="Weekly";
$filter="AND date_added between date_sub(now(), interval 7 day)  AND date_add(now(), interval 1 day)";
}
IF($_GET['num']==3){
$title="Monthly";
$filter="AND date_added between date_sub(now(), interval 30 day)  AND date_add(now(), interval 1 day)";
}
include'../db.php';

?>


<h1 style="text-align:center"><?=$title?> Active Complaint</h1>
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

$groupee = mysql_query("SELECT *
FROM cases
where  status NOT IN ('DELETED','CLOSED')
$filter
group by employerid");

while($row1=mysql_fetch_array($groupee))
{
$emp1 = mysql_query("SELECT * FROM employer where employer_id=".$row1['employerid']." ");
$employer1=mysql_fetch_array($emp1);
?>


<div class="col-md-12 col-sm-12" style="margin-top:-1px;margin:10px;">
<div class="row" style="floar:left;background:white;padding:10px;min-height:100px">
<h4 style="text-align:left"><?=$employer1['employer_name']?></h4>
<table id="customers">
<tr>
<th style="width:3%">#</th>
<th>NAME</th>
<th style="width:55%">ISSUE</th>
<th style="width:10%">TYPE</th>
<th style="width:7%">DATE ISSUE</th>
<th style="width:7%">STATUS</th>
</tr>
<tr>

<?php
$count=1;
$fit =mysql_query("SELECT *
FROM cases
where  status NOT IN ('DELETED','CLOSED')
$filter
AND employerid=".$row1['employerid']."
order by date_added desc ");

			while($row=mysql_fetch_array($fit))
			{
				
			
$app1 = mysql_query("SELECT * FROM applicant 
where applicant_id=".$row['app_id']." ");
$approw=mysql_fetch_array($app1);
			
			$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['employerid']." ");
			$employer=mysql_fetch_array($emp);
			$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
			$agentrow1=mysql_fetch_array($agent);

			echo'<tr>';
			echo'<td>'.$count.'</td>';
			if($row['app_id']==0){
			echo'<td '.$color.' style="width:20%;text-transform:uppercase"><b><a href="case_view.php?case='.$row['case_id'].'"  target="_blank" >
			<a href="case_view.php?case='.$row['case_id'].'"  target="_blank" style="color:blue">
			'.$row["lname"].', '.$row["applicant_name"].' '.$row["mname"].'</A></b>';
			echo'&nbsp; </td>';}

			if($row['app_id']!=0){
			echo'<td '.$color.' style="width:20%;text-transform:uppercase;border-left:1px solid black;"><b><a href="case_view.php?case='.$row['case_id'].'"  target="_blank" >
			<a href="case_view.php?case='.$row['case_id'].'"  target="_blank" style="color:green"> '.$approw["applicant_last"].', '.$approw["applicant_first"].'</A></b>';
			echo'&nbsp; </td>';}
			
			echo'<td  >';
if($row['others']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["others"].'</button>';}
if($row['c1']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c1"].'</button>';}
if($row['c2']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c2"].'</button>';}
if($row['c3']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c3"].'</button>';}
if($row['c4']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c4"].'</button>';}
if($row['c5']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c5"].'</button>';}
if($row['c6']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c6"].'</button>';}
if($row['c7']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c7"].'</button>';}
if($row['c8']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c8"].'</button>';}
if($row['c9']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c9"].'</button>';}
if($row['c10']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c10"].'</button>';}
if($row['c11']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c11"].'</button>';}
if($row['c12']!=""){echo'<button type="button" class="btn pmd-btn-outline btn-info btn-xs">'.$row["c12"].'</button>';}


echo'</td>';
			
			
			echo'<td>'.$row["case_type"].'</td>';
			echo'<td>'.$row["date_added"].'</td>';
			echo'<td><u style="color:'.$color.'">'.$row["status"].'</u></td>';
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
