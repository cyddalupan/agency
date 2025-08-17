<?php include'inc/header.php';
include'inc/add.php';
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


<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post">

<div class="form-group">
<div class="col-md-4 col-sm-4 col-xs-4 form-group">
<input type="text" placeholder="First Name, Last Name" class="form-control" name="search" required>
</div>

<div class="col-md-6 col-sm-12 col-xs-12 form-group">
<button type="submit" class="btn btn-info"  name="Search" >Search Applicant</button>

</div>
</form>       




<div class="row">




<div class="col-md-12 col-xs-12">


<div class="x_panel">

<h2>Available Applicants</h2>


<div class="x_content">




<table  cellspacing="0"
id="example-checkbox">
<thead>
<tr>

<th>DATE</th>
<th>Name</th>

<th>Passport</th>
<th>Country</th>
<th>Agent</th>
<th>Employer/Prinicipal</th>
<th>Status</th>
<th>Expense</th>
<th>Credit</th>
<th>Balance</th>
<th>ACTION</th>
</tr>
</thead>
<tbody>
<?php	
$app = mysql_query("SELECT * FROM applicant
where applicant_status IN (0)
ORDER BY applicant_id desc limit 300");

if(isset($_POST['Search']))
{
$app = mysql_query("SELECT  * FROM applicant
where
applicant_last LIKE '%".$_POST['search']."%'
OR applicant_first LIKE '%".$_POST['search']."%'
limit 20
");
}

while($row=mysql_fetch_array($app))
{
$status = mysql_query("SELECT * FROM statuses where number=".$row['applicant_status']." ");
$srow=mysql_fetch_array($status);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$row1=mysql_fetch_array($app1);

$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['applicant_employer']." ");
$emprow=mysql_fetch_array($emp);

$passport = mysql_query("SELECT * FROM applicant_passport where passport_applicant=".$row['applicant_id']." ");
$passportrow=mysql_fetch_array($passport);

$country = mysql_query("SELECT * FROM country where country_id=".$row['applicant_preferred_country']." ");
$countryrow=mysql_fetch_array($country);


$app2 = mysql_query("SELECT SUM(l_amount) as totalamount ,e_liq.* FROM e_liq
WHERE liq_app=".$row["applicant_id"]." AND  l_status=0");
$php=mysql_fetch_array($app2);


$collected = mysql_query("SELECT SUM(amount) as totalamount ,email_address6.* FROM email_address6
WHERE app_id=".$row["applicant_id"]."");
$collected1=mysql_fetch_array($collected);

 
$totalbalance=$php["totalamount"]-$collected1["totalamount"];

echo'<tr>';

echo'<td>'; echo date('Y-M-d', strtotime($row['applicant_date_applied']));echo'</td>';
echo'<td><b>'.$row["applicant_first"].' '.$row["applicant_last"].'</b></td>';
echo'<td>'.$passportrow["passport_number"].'</td>';
echo'<td>'.$countryrow["country_name"].'</td>';

echo'<td style="color:orange;font-size:10px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$emprow["employer_name"].'</td>';
echo'<td>
<a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">
<button type="button" class="btn btn-'.$srow["statusColors"].' btn-xs" style="font-size:9px">'.$srow["status"].'</button></a></td>';

echo'<td style="color:red">'.number_format($php["totalamount"],2).'</td>';
echo'<td style="color:red">'.number_format($collected1["totalamount"],2).'</td>';
echo'<td style="color:red">'.number_format($totalbalance,2).'</td>';

echo'<td><a href="soa.php?appid='.$row['applicant_id'].'"  target="_blank">View </A></td>';
echo'</tr>';
}
?>
</tbody>
</table>



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