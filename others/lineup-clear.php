<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<!-- Head -->
<head>
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<title></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

</head>
<style>
table{
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  font-size:10px;
}

 td,th {
  border: 1px solid #ddd;
  padding: 5px;
}

tr:nth-child(even){background-color: #f2f2f2;}

tr:hover {background-color: #ddd;}

th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: lightblue;
  color: white;
    padding: 8px;
}


th{
font-size:8px;
font-weight:none;
color:blue;	
padding:1px;
}
tD{
padding:1px;
}
input{
width:110px;
padding:1px;
font-size:11px;
height:20px;	
}


</style>

<!-- /Head -->
<!-- Body -->
<body style="background:white">

<div class="col-md-14 col-sm-14" style="margin-top:-1px">
<div class="row">
<div class="col-md-12">
  <form action="" method="post">
  
<div class="clients-page">

<div class="col-sm-2 pull-right" align="center">
</div>


<div class="row">
<div class="col-md-12" style="margin:20px">

<?php
include'db.php';




if(isset($_POST['delete_orders'])){
foreach ($_POST['delall'] as $delall) {
$app = mysql_query("delete  FROM multiple_lineups where id= ".$delall)

or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted</p>";
}



$app = mysql_query("SELECT * FROM  multiple_lineups
where applicant_id=".$_GET['appid']."
order by id desc  ");



?>



<form action="" method="post">
<input type='submit'class="btn btn-lg btn-danger" name='delete_orders' value='Delete' style="color:White;font-size:16px;
width:140px;height:40px;float:right;margin-right:150px;border:2px solid white;background:red">


<?php
if($_GET['id']==0) { $stat="available";}
if($_GET['id']==5) { $stat="Line Up";}
if($_GET['id']==2) { $stat="Reserved";}
if($_GET['id']==4) { $stat="On Process";}
if($_GET['id']==12){ $stat="For Booking";}
if($_GET['id']==9) {$stat="Deployed";}
if($_GET['id']==15) { $stat="Passporting";}
?>

<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3><span>Line UP LIST</span></h3>
<!-- para -->
</div> 

<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">

<table class="table table-hover table-striped table-bordered"  id="example-checkbox" STYLE="FONT-SIZE:9px;width:98%" >
<thead>
<tr>
<th style="font-size:10px;width:15px"></th>
<th style="font-size:10px;width:105px">Name</th>
<th style="font-size:10px">FRA</th>
<th style="font-size:10px">STATUS</th>
	
									
										
</tr>
</thead>
<tbody>
   		
<?php

while($row=mysql_fetch_array($app))
{			
$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$employer=mysql_fetch_array($emp);

$row22 = mysql_query("SELECT * FROM applicant where applicant_id=".$row['applicant_id']." ");
$row2=mysql_fetch_array($row22);


if($row['applicant_status']=='4') {$color='orange';}
if($row['applicant_status']=='3') {$color='lightgray';}
if($row['applicant_status']=='2') {$color='blue';}
if($row['applicant_status']=='12') {$color='orange';}
if($row['applicant_status']=='8') {$color='orange';}

echo"<tr>";	
echo "<td><input type='checkbox' name='delall[]' value='".$row['id']."' ></td> ";	
echo"<td>".$row2['applicant_first']."  ".$row2['applicant_last']."</td>";
echo"<td>".$employer['employer_name']."</td>";
echo"<td><div style='background:".$color.";padding:4px;color:white'>".$row['statusText']."</div></td>";


echo"</tr>";
}
?>	

</tbody>
</table>

  </form>
<div style="clear:both;height:40px"></div>
</div>

</div>
</div>

</div>
</div>
</div>


</div>
</div>
</div>
</div>



</body>
<!--  /Body -->
</html>

<script src="assets/js/jquery-1.12.2.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/propeller.min.js"></script>

