<?php include'inc/header.php';
include'db.php';
include'tracking.php';
include'styles.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
      <meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
 
  </head>

<body class="nav-md">
<div class="container body">
<div class="main_container">
<div class="col-md-3 left_col">
<div class="left_col scroll-view">
<div class="navbar nav_title" style="border: 0;">
<a href="index.html" class="site_title"> <span><?php include'title.php';?></span></a>
</div>

<div class="clearfix"></div>

<!-- menu profile quick info -->
<div class="profile clearfix">
<div class="profile_pic">
<img src="images/img.jpg" alt="..." class="img-circle profile_img">
</div>
<div class="profile_info">
<span>Welcome,</span>
<h2><?=$_SESSION['user_fullname']?></h2>
</div>
</div>
<!-- /menu profile quick info -->

<br />

<!-- sidebar menu -->
<div id="sidebar-menu" class="main_menu_side hidden-print main_menu">

<div class="menu_section">
<?php include'inc/menu.php';?>	
</div>
</div>
<!-- /sidebar menu -->

<!-- /menu footer buttons -->
<div class="sidebar-footer hidden-small">
<a data-toggle="tooltip" data-placement="top" title="Settings">
<span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
</a>
<a data-toggle="tooltip" data-placement="top" title="FullScreen">
<span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
</a>
<a data-toggle="tooltip" data-placement="top" title="Lock">
<span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
</a>
<a data-toggle="tooltip" data-placement="top" title="Logout" href="login.html">
<span class="glyphicon glyphicon-off" aria-hidden="true"></span>
</a>
</div>
<!-- /menu footer buttons -->
</div>
</div>

<!-- top navigation -->
<div class="top_nav">
<div class="nav_menu">
<nav>
<div class="nav toggle">
<a id="menu_toggle"><i class="fa fa-bars"></i></a>
</div>
<ul class="nav navbar-nav navbar-right">
<li class="">
<a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
<img src="images/img.jpg" alt="">
<span class=" fa fa-angle-down"></span>
</a>
<ul class="dropdown-menu dropdown-usermenu pull-right">

</li>
<li><a href="logout.php"><i class="fa fa-sign-out pull-right"></i> Log Out</a></li>
</ul>
</li>


</ul>
</li>
</ul>
</nav>
</div>
</div>
<!-- /top navigation -->

<!-- page content -->
<div class="right_col" role="main">





<div class="row">




<div class="col-md-12 col-xs-12">


<div class="x_panel">

<h2>Available Applicants</h2>


<div class="x_content">

<form action="" method="post"">
<?php
if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){

echo'<select name="emp_id"  required style="padding:5px;width:150px;border:1px solid black">';
echo'<option VALUE="">--</option>
<option value="0">N/A</option>';
$emp1 = mysql_query("SELECT * FROM employer ORDER BY employer_name asc");
while($emp111=mysql_fetch_array($emp1))
{
echo'<option value="'.$emp111["employer_id"].'">  '.$emp111["employer_name"].'</option>';
}
ECHO'</select>';
?>
 
<a href="delete_methode_link" onclick="return confirm('Are you sure you want to Sent CV?');">		
<button type="submit" class="btn btn-success btn-sm"  name="sentcv" style="border:BORDER 2PX SOLID black;color:white;border:1px solid black">Sent CV</button>
</a>
<?PHP } ?>   
</br></br>



<script>
function exportF(elem) {
  var table = document.getElementById("example-checkbox");
  var html = table.outerHTML;
  var url = 'data:application/vnd.ms-excel,' + escape(html); // Set your html table into url 
  elem.setAttribute("href", url);
  elem.setAttribute("download", "export.xls"); // Choose the file name
  return false;
}
</script>

<a id="downloadLink" onclick="exportF(this)" style="color:Red;FLOAT:right;font-size:14px;padding:5px">Export to excel</a>



<table  cellspacing="0"
id="example-checkbox">
<thead>
<tr>

<th></th>
<th>#</th>
<th>Date Applied</th>
<th># Days Process</th>
<th>Applicant Name</th>
<th>Age</th>
<th>Position</th>
<th>Passport</th>
<th>Country</th>
<th>Agent</th>
<th >MEDICAL </th>	
<th >CLINIC</th>	
<th >RESULT</th>
<th>Employer/Prinicipal</th>
<th>Status</th>

<th>ACTION</th>
</tr>
</thead>
<tbody>
<?php

if(isset($_POST['sentcv'])){
foreach ($_POST['delall'] as $delall) {
$app = mysql_query("UPDATE applicant
SET  applicant_status =5
,applicant_employer =".$_POST['emp_id']."
,applicant_updatedby =".$_SESSION['user_id']."
,applicant_updated= '".$date3."'
where applicant_id= ".$delall)
or die ("cannot Update data");





$app2 = mysql_query("INSERT INTO multiple_lineups (applicant_id,applicant_employer, created_at,updated_at)
VALUES (".$delall." ,".$_POST['emp_id']." ,'".$date3."','".$date3."' )");



}
echo"<p style='color:white;BACKGROUND:#DC143C;padding:5px;FONT-SIZE:1qPX;WIDTH:400px'>Cv Sent!</p>";
}
$count=1;	
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

where applicant.applicant_status IN (0)
group by applicant.applicant_id");

if(isset($_POST['Search']))
{
$app = mysql_query("SELECT  * FROM applicant


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

where applicant.applicant_status IN (0)
AND applicant_last LIKE '%".$_POST['search']."%'
OR applicant_first LIKE '%".$_POST['search']."%'
OR emoloyer_name LIKE '%".$_POST['search']."%' 
OR certificate_medical_result LIKE '%".$_POST['search']."%'
");
}

while($row=mysql_fetch_array($app))
{



$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);

$position = mysql_query("SELECT * FROM position where position_id=".$row['applicant_preferred_position']." ");
$position1=mysql_fetch_array($position);

echo'<tr>';

echo'<td><input type="checkbox" name="delall[]" value="'.$row['applicant_id'].'" ></td>';
echo'<td>'.$count.'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';

$datecountapplied=date_create("".$row['applicant_date_applied']."");
$currentdate=date_create("".$date_now."");
$dateaddedfinal=date_diff($datecountapplied,$currentdate);
echo"<td style='color:black;width:100px'></div><u style='padding:3px;color:black;font-size:12px;background:orange';> ";
echo $dateaddedfinal->format("%r%a days");
echo "</u>";
echo"</td>";

echo'<td><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';
echo"<td>".$row['applicant_age']."</td>";
echo"<td>".$position1['position_name']."</td>";
echo'<td>'.$row["passport_number"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';

echo'<td>'.$row["agent_first"].' '.$row["agent_last"].'</td>';

echo"<td>".$row['certificate_medical_exam_date']."</td>";
echo"<td>".$row['certificate_medical_clinic']."</td>";
echo"<td>".$row['certificate_medical_result']."</td>";
echo'<td>'.$row["employer_name"].'</td>';
echo'<td>
<a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">
<button type="button" class="btn btn-'.$row["statusColors"].' btn-xs" style="font-size:9px">'.$row["status"].'</button></a></td>';


echo'<td><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">View </A></td>';
echo'</tr>';
$count++;
}
?>
</tbody>
</table>
</form>	


<div class="ln_solid"></div>

</div>
</div>
</div>  




</div>			











<!-- /footer content -->
</div>
</div>

<!-- jQuery -->
<script src="../vendors/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>

<!-- Skycons -->
<script src="../vendors/skycons/skycons.js"></script>


<!-- Custom Theme Scripts -->
<script src="../build/js/custom.min.js"></script>

</body>
</html>


<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
<div class="modal-dialog">

<!-- Modal content-->
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal">&times;</button>
<h4 class="modal-title">New Applicant</h4>
</div>
<div class="modal-body">

<form class="form-horizontal form-label-left input_mask" method="post" action="">	

<input type="hidden" class="form-control" name="status" value="Available">

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">DATE APPLIED</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="date" class="form-control" name="date_applied" value="<?=$date1?>" required>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">FIRST NAME</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="name" required>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">MIDDLE NAME</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="mname" required>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">LAST NAME</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="lname" required>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">PASSPORT #</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="passport" required>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12"> DATE EXPIRED</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="date" class="form-control" name="ppt_expired" required>
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Contact #</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="contact" required>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">COUNTRY</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" required  name="country" required>
<option><?=$row1['country']?></option>
<option>KSA</option>
<option>KUWIAT</option>
<option>QATAR</option>
<option>OMAN</option>
</select>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">AGENT</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" required  name="agent_id" required>
<option>-----</option>
<?php
$usertype = mysql_query("SELECT * FROM recruitment_agent ORDER BY agent_first asc");

while($rows=mysql_fetch_array($usertype))
{
echo'<option value="'.$rows["agent_id"].'">'.$rows["agent_first"].' '.$rows["agent_last"].'</option>';
}
?>
</select>
</div>
</div>


<div class="form-group">
<div class="col-md-12 col-sm-9 col-xs-12">	
<button type="submit" class="btn btn-danger pull-right"  name="add_applicants">Save</button>
</div>
</div>


</form>		  



</div>

</div>

</div>
</div>
</html>