<?php include'inc/header.php';
include'inc/add.php';
include'tracking.php';
?>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">
              <a href="index.html" class="site_title"> <span>Network Agency</span></a>
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
                <h3>Live On</h3>
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
<div class="col-md-6 col-sm-6 col-xs-12">
<div class="x_panel" style="height:500px">
<div class="x_title">
<h2>Selected Reports</h2>
<div class="clearfix"></div></br>
<form action="reports/selected.php" method="post" target="_blank"class="form-horizontal form-label-left input_mask" >

		<div class="form-group">
		<label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
		<div class="col-md-6 col-sm-6 col-xs-12">
		<select class="form-control" required  name="status">
		<option>Selected</option>
		<option>For Booking</option>
		<option>For Deployment</option>
		<option>Backout</option>
		</select>
		</div>
		</div>


		<div class="form-group">
		<label class="control-label col-md-3 col-sm-3 col-xs-12">Sub Status</label>
		<div class="col-md-6 col-sm-6 col-xs-12">
		<select class="form-control" required  name="sub_status">
		<option>All</option>
		<option>To Undergo Medical</option>
		<option>Medical Under Process</option>
		<option>Fit to Work</option>
		<option>For Insurance</option>
		<option>For Encoding Enjaz</option>
		<option>Waiting for job Offer</option>
		<option>For job offer Signing</option>
		<option>For  Offer Letter</option>
		<option>Incoming Contract</option>
		<option>With Contract</option>
		<option>Waiting for Contract Signing</option>
		<option>Contract Sign</option>
		<option>With labor Contract (Awaiting Visa)</option>
		<option>For Visa Applicantion</option>
		<option>Waiting For Visa</option>
		<option>Visa in Process</option>
		<option>Visa Approved</option>
		<option>Visa Stamping</option>
		<option>OEC FILING</option>
		<option>OWWA Training</option>
		<option>Unfit</option>
		<option>Canceled/backout</option>
		</select>
		</div>
		</div>
		
<input type="HIDDEN" class="form-control" name="user" value="<?=$_SESSION['user_name']?>">


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Employer</label>
<div class="col-md-6 col-sm-6 col-xs-12">
<select class="form-control" name="counter_part" required >
<?php
$usertype = mysql_query("SELECT * FROM employer ORDER BY employer_name desc");

while($rows=mysql_fetch_array($usertype))
{
echo'<option value="'.$rows["employer_id"].'">'.$rows["employer_name"].'</option>';
}
?>
</select>
</div>
</div>

<div class="form-group">
<div class="col-md-9 col-sm-6 col-xs-12">
<button type="submit" class="btn btn-danger pull-right"  name="submitsearch">Save</button>
</div>
</div>

</form>

</div>
</div>
</div>




<div class="col-md-6 col-sm-6 col-xs-12">
<div class="x_panel" style="height:500px">
<div class="x_title">
<h2>Deployed Reports</h2>
<div class="clearfix"></div></br>
<form action="reports/deployed.php" method="post" target="_blank"class="form-horizontal form-label-left input_mask" >

		<div class="form-group">
		<label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
		<div class="col-md-6 col-sm-6 col-xs-12">
		<select class="form-control" required  name="status">
		<option>Deployed</option>
		</select>
		</div>
		</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Start Date</label>
<div class="col-md-6 col-sm-6 col-xs-12">
<input type="date" class="form-control" name="start">
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">End Date</label>
<div class="col-md-6 col-sm-6 col-xs-12">
<input type="date" class="form-control" name="end" required>
</div>
</div>
<input type="HIDDEN" class="form-control" name="user" value="<?=$_SESSION['user_name']?>">

	


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Employer</label>
<div class="col-md-6 col-sm-6 col-xs-12">
<select class="form-control" name="counter_part" required >
<?php
$usertype = mysql_query("SELECT * FROM employer ORDER BY employer_name desc");

while($rows=mysql_fetch_array($usertype))
{
echo'<option value="'.$rows["employer_id"].'">'.$rows["employer_name"].'</option>';
}
?>
</select>
</div>
</div>

<div class="form-group">
<div class="col-md-9 col-sm-6 col-xs-12">
<button type="submit" class="btn btn-danger pull-right"  name="submitsearch">Save</button>
</div>
</div>

</form>

</div>
</div>
</div>


</div>

    <!-- jQuery -->
    <script src="../vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- FastClick -->
    <script src="../vendors/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="../vendors/nprogress/nprogress.js"></script>
    <!-- Chart.js -->
    <script src="../vendors/Chart.js/dist/Chart.min.js"></script>
    <!-- gauge.js -->
    <script src="../vendors/gauge.js/dist/gauge.min.js"></script>
    <!-- bootstrap-progressbar -->
    <script src="../vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
    <!-- iCheck -->
    <script src="../vendors/iCheck/icheck.min.js"></script>
    <!-- Skycons -->
    <script src="../vendors/skycons/skycons.js"></script>
    <!-- Flot -->
    <script src="../vendors/Flot/jquery.flot.js"></script>
    <script src="../vendors/Flot/jquery.flot.pie.js"></script>
    <script src="../vendors/Flot/jquery.flot.time.js"></script>
    <script src="../vendors/Flot/jquery.flot.stack.js"></script>
    <script src="../vendors/Flot/jquery.flot.resize.js"></script>
    <!-- Flot plugins -->
    <script src="../vendors/flot.orderbars/js/jquery.flot.orderBars.js"></script>
    <script src="../vendors/flot-spline/js/jquery.flot.spline.min.js"></script>
    <script src="../vendors/flot.curvedlines/curvedLines.js"></script>
    <!-- DateJS -->
    <script src="../vendors/DateJS/build/date.js"></script>
    <!-- JQVMap -->
    <script src="../vendors/jqvmap/dist/jquery.vmap.js"></script>
    <script src="../vendors/jqvmap/dist/maps/jquery.vmap.world.js"></script>
    <script src="../vendors/jqvmap/examples/js/jquery.vmap.sampledata.js"></script>
    <!-- bootstrap-daterangepicker -->
    <script src="../vendors/moment/min/moment.min.js"></script>
    <script src="../vendors/bootstrap-daterangepicker/daterangepicker.js"></script>

    <!-- Custom Theme Scripts -->
    <script src="../build/js/custom.min.js"></script>
	
  </body>
</html>

