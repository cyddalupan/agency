<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<!-- Head -->
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<title></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


	<!-- Demo styling -->
	<link href="docs/css/jq.css" rel="stylesheet">

	<!-- jQuery: required (tablesorter works with jQuery 1.2.3+) -->
	<script src="docs/js/jquery-1.2.6.min.js"></script>

	<!-- Pick a theme, load the plugin & initialize plugin -->
	<link href="dist/css/theme.default.min.css" rel="stylesheet">
	<script src="dist/js/jquery.tablesorter.min.js"></script>
	<script src="dist/js/jquery.tablesorter.widgets.min.js"></script>
	<script>
	$(function(){
		$('table').tablesorter({
			widgets        : ['zebra', 'columns'],
			usNumberFormat : false,
			sortReset      : true,
			sortRestart    : true
		});
	});
	</script>



</head>
<?php
include'db.php';
include'tracking.php';
// Turn off error reporting
error_reporting(0);

// Report runtime errors
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Report all errors
error_reporting(E_ALL);

// Same as error_reporting(E_ALL);
ini_set("error_reporting", E_ALL);

// Report all errors except E_NOTICE
error_reporting(E_ALL & ~E_NOTICE);




?>

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

.tablesorter {
font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
border-collapse: collapse;
width: 100%;
}

.tablesorter td, .tablesorter th {
border: 1px solid #ddd;
padding: 4px;
}

.tablesorter tr:nth-child(even){background-color: white;}

.tablesorter tr:hover {background-color: white;}

.tablesorter th {
padding-top: 10px;
padding-bottom: 10px;
text-align: left;
background-color: #DCDCDC;
color: black;
}




/* Style the tab */
.tab {
  overflow: hidden;
  border: 0px solid #ccc;
  background-color: none;
  text-align:center;
}

/* Style the buttons inside the tab */
.tab button {
  background-color: #000080;
  float: left;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 8px 5px;
  transition: 0.3s;
  font-size: 14px;
  margin:4px;
  color:white;
}

/* Change background color of buttons on hover */
.tab button:hover {
  background-color: #1E90FF;
}

/* Create an active/current tablink class */
.tab button.active {
  background-color: #1E90FF;
}

/* Style the tab content */
.tabcontent {
  padding: 2px 6px;
  border: 0px solid #ccc;
  border-top: none;
}


</style>
<body>

<div style="margin:0 auto;margin-top:10px;text-align:center">
<a href="index.php" style="color:blue;font-size:16px ;text-decoration: none;">My Schedule</a> | 
<a href="request.php" style="color:red;font-size:16px ;text-decoration: underline;">Payment Request</a> | 
<a href="b_account.php"  style="color:blue;font-size:16px; text-decoration: none;">Summary Reports</a>
</div>
<div style="clear:both;height:10px"></div>
<h2 style="font-size:22px;text-align:center">Payment Request</h2>
<div style="clear:both;height:20px"></div>
<div class="tab">
<button class="tablinks" style="background:blue;color:white"><a href="request.php" style="color:white"> For Approval Office/Agent </button></a>
<button class="tablinks" style="background:#DC143C;color:white"><a href="requestapplicant.php" style="color:white"> For Approval Applicant</button></a>
<button class="tablinks" style="background:blue;color:white"><a href="requestaapproved.php" style="color:white"> Approved</button></a>
<button class="tablinks" style="background:blue;color:white"><a href="requestreleased.php" style="color:white"> Released</button></a>
<button class="tablinks" style="background:blue;color:white"><a href="requestdeclined.php" style="color:white"> Decline </button></a>

</div>

<div  style="margin-top:-1px;margin:10px" id="London"  class="tabcontent col-md-12 col-sm-12">

<div class="row" style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC;border:1px solid #DCDCDC">

<h1 style="text-align:center;color:#000080">Payment Request of Applicants</h1>
<h3 style="text-align:center">As of <?php echo date('d-F-Y', strtotime($date1)); ?></p></h3>
<?php
include'r/db.php';
include'r/f_applicant.php';

?>


<form action="" method="post">
<input type='submit'class="btn btn-lg btn-danger" 
name='updatedata2' value='Save Data Changes' style="float:right;color: white;background:red;padding:6px">

<table id="customers">
<thead>

<tr>
<th style="width: 15%">Applicant</th>
<th style="width: 10%">Date</th>
<th style="width: 15%">Agent</th>
<th style="">Type</th>
<th style="width: 16%">Description</th>
<th style="text-align:right">Currency</th>
<th style="text-align:right">Amount</th>
<th style="text-align:right">othe charges</th>
<th style="text-align:right;">Charge To</th>
<th style="text-align:right">Status</th>
<th style="text-align:right">Request</th>
<th STYLE="">ACTION</th>
</tr>

</thead>
<tbody>
<?PHP
include'inc/f_applicant.php';
$app = mysql_query("SELECT * FROM email_address4  where request_payment=0 order by exp_id desc ");

if(isset($_POST['Search']))
{
$app = mysql_query("SELECT * FROM email_address4 where
exp_type LIKE '%".$_POST['agent_first']."%' OR findings LIKE '%".$_POST['agent_first']."%'
");
}

while($row=mysql_fetch_array($app))
{

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);

$app12 = mysql_query("SELECT * FROM applicant where applicant_id=".$row['app_id']." ");
$row12=mysql_fetch_array($app12);
$total=$row['receivable']+$total;

if ($row["type_status"]=='Agency'){
	if($row["status_paid"]==0){$status1='';}
	if($row["status_paid"]==1){$status1='';}
}
if ($row["type_status"]=='Agent'){
	if($row["status_paid"]==0){$status1='Not Paid';}
	if($row["status_paid"]==1){$status1='Paid';}
}


if($row["request_payment"]==0){$request='For Approval';$rcolor='black';}
if($row["request_payment"]==1){$request='Approved';$rcolor='blue';}
if($row["request_payment"]==2){$request='For Release';$rcolor='orange';}
if($row["request_payment"]==3){$request='Released';$rcolor='green';}
if($row["request_payment"]==4){$request='Decline';$rcolor='red';}

echo'<tr>';
echo'<td><a href="edit_unfit.php?exp='.$row["exp_id"].'" target="_blank" style="color:blue">'.$row12["applicant_first"].' '.$row12["applicant_last"].'</a></td>';			
echo'<td>'.$row["date_transaction"].'</td>';
echo'<td>'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$row["exp_type"].'</td>';
echo'<td>'.$row["findings"].'</td>';
echo'<td style="text-align:right">'.$row["currency"].'</td>';
echo'<td style="text-align:right">'.$row["receivable"].'</td>';
echo'<td style="text-align:right">'.$row["othercharge"].'</td>';
echo'<td style="text-align:right">'.$row["type_status"].'</td>';
echo'<td style="text-align:right;font-size:10px;color:Red">'.$status1.'</td>';
echo'<td style="text-align:right;font-size:10px;color:white;background:'.$rcolor.'">'.$request.'</td>';

echo "<td><input type='hidden' name='exp_id[]' value='".$row['exp_id']."' >
<select name='request_payment[]'>
<option value='".$row["request_payment"]."'>".$request."</option>
<option value='1'>Approved</option>
<option value='3'>Released</option>
<option value='4'>Decline</option>
</SELECT>

</td> ";
echo'</tr>';
}
?>	
</tbody>
</table>



</form>	


<div style="clear:both; height:5px;"></div>


</div>
</div>












<script>
function openCity(evt, cityName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(cityName).style.display = "block";
  evt.currentTarget.className += " active";
}
</script>




</body>
<!--  /Body -->
</html>




