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
                <h2>John Doe</h2>
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
 

			<form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post">

			<div class="form-group">
			<div class="col-md-4 col-sm-4 col-xs-4 form-group">
			<input type="text" placeholder="First Name, Last Name" class="form-control" name="search" required>
			</div>

			<div class="col-md-6 col-sm-12 col-xs-12 form-group">
			<button type="submit" class="btn btn-info"  name="Search" >Search Applicant</button>
			
			</div>
			</form>       
              

			  
			    <button type="button" class="btn btn-danger btn-lg" data-toggle="modal" data-target="#myModal">Add Applicant</button>
</br>
  <div class="row">
               
					
					
					
					<div class="col-md-12 col-xs-12">


					<div class="x_panel">
				
					<h2>Availble Applicants</h2>

					
					<div class="x_content">
					
				
					<div class="x_panel">


					<div class="x_content">
	
 <div style="width:100%; height:300px; overflow:auto;">
	
	<table class="table table-bordered">
	<thead>
	<tr>

	<th STYLE="width:10%">DATE</th>
	<th STYLE="width:10%">#</th>
	<th>Name</th>
	<th>Passport</th>
	<th>Date EXPIRED</th>
	<th>Country</th>
	<th>Agent</th>
	<th>Agency</th>
	</tr>
	</thead>
	<tbody>
<?php	
if($_SESSION['user_name']=='riza1'){
$app = mysql_query("SELECT * FROM email_address2
where status='Available'
AND counter_part='4'
ORDER BY app_id desc limit 100");
}
if($_SESSION['user_name']=='cielo'){
$app = mysql_query("SELECT * FROM email_address2
where status='Available'
AND counter_part  IN (6,11)
ORDER BY app_id desc");
}


if($_SESSION['user_name']=='jen'){
$app = mysql_query("SELECT * FROM email_address2
where status='Available'
AND counter_part  IN (5,12)
ORDER BY app_id desc");
}

if($_SESSION['user_name']=='bella'){
$app = mysql_query("SELECT * FROM email_address2
where status='Available'
AND counter_part  IN (3,2)
ORDER BY app_id desc");
}



if($_SESSION['user_name']=='aiza'){
$app = mysql_query("SELECT * FROM email_address2
where status='Available'
AND counter_part  IN (2)
ORDER BY app_id desc");
}

 
if(isset($_POST['Search']))
{
$app = mysql_query("SELECT email_address2.app_id as adonis, applicant_certificate.*,applicant_requirement.*,email_address2.* FROM email_address2
LEFT JOIN applicant_certificate
ON email_address2.app_id=applicant_certificate.app_id

LEFT JOIN 	applicant_requirement
ON email_address2.app_id=applicant_requirement.app_id

where
name LIKE '%".$_POST['search']."%'
OR lname LIKE '%".$_POST['search']."%'
OR passport LIKE '%".$_POST['search']."%'
");
}

 
while($row=mysql_fetch_array($app))
{
$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);

$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['counter_part']." ");
$emprow=mysql_fetch_array($emp);


echo'<tr>';

echo'<td>'; echo date('Y-M-d', strtotime($row['date_applied']));echo'</td>';
echo'<td>'.$row["app_id"].'</td>';
echo'<td><a href="profile_applicants.php?appid='.$row['app_id'].'" STYLE="COLOR:BLUE" target="_blank">'.$row["name"].' '.$row["lname"].'</A></td>';
echo'<td>'.$row["passport"].'</td>';
echo'<td>'.$row["ppt_expired"].'</td>';
echo'<td>'.$row["country"].'</td>';
echo'<td style="color:orange;font-size:10px">'.$row1["agent_first"].' '.$row1["agent_last"].'</td>';
echo'<td>'.$emprow["employer_name"].'</td>';
echo'</tr>';
}
?>
</tbody>
</table>
</div>
	</div>
	</div>

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
<style>
table td,th{
font-size:11px;
}
table th{
color:Red
}
</style>

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
