<?php include'inc/header1.php';
include'inc/case.php';
include'tracking.php';
?>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">
              <a href="index.html" class="site_title"> <span>Accounting</span></a>
            </div>

            <div class="clearfix"></div>

            <!-- menu profile quick info -->
            <div class="profile clearfix">
              <div class="profile_pic">
                <img src="images/img.jpg" alt="..." class="img-circle profile_img">
              </div>
              <div class="profile_info">
                <span>Welcome,</span>
     
              </div>
            </div>
            <!-- /menu profile quick info -->

            <br />

            <!-- sidebar menu -->
            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
            
              <div class="menu_section">
                <h3>Live On</h3>
			<?php include'inc/menu1.php';?>	

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
				
					<h2>CASES Applicants</h2>

					
					<div class="x_content">
					
				
					<div class="x_panel">


					<div class="x_content">
	

	
	<table class="table table-bordered">
	<thead>
	<tr>
	<th STYLE="width:10%">HEARING DATE</th>
	<th>Name</th>
	<th>type</th>
	<th>Case</th>
	<th>Remarks</th>
	<th>ACTION</th>
	</tr>
	</thead>
	<tbody>
<?php	

$app = mysql_query("SELECT * FROM cases
where status!='DELETED'
ORDER BY date_hearing,timess,case_id desc limit 50");
 
if(isset($_POST['Search']))
{
$app = mysql_query("SELECT * FROM cases where
applicant_name LIKE '%".$_POST['applicant_name']."%'
");
}

 
while($row=mysql_fetch_array($app))
{
date_default_timezone_set('Manila');
$date2 = date('Y-m-d');

 $date1= date('Y-m-d', strtotime("+5 day", strtotime($date2)));

if($row['date_hearing']<=$date1){
$color='style="border:2px solid red;background:#F0FFFF"';
}
else{
$color='';
}

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);
echo'<tr>';

echo'<td '.$color.'>'; echo date('Y-M-d', strtotime($row['date_hearing']));echo' '.$row['timess'].'</td>';
echo'<td '.$color.'>'.$row["applicant_name"].'</td>';
echo'<td '.$color.' >'.$row["case_type"].'</td>';
echo'<td '.$color.'>'.$row["caso"].' / <b style="color:green">'.$row["status"].'</b> </td>';
echo'<td '.$color.'>'.$row["remarks"].' </td>';
echo'<td '.$color.'><a href="case_view.php?case='.$row['case_id'].'" STYLE="COLOR:BLUE" target="_blank">View</A></td>';
echo'</tr>';
}
?>
</tbody>
</table>

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
          <h4 class="modal-title">New Case</h4>
        </div>
        <div class="modal-body">
					
			<form class="form-horizontal form-label-left input_mask" method="post" action="">	
					
			 		    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" style="color:red">case_type</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
								<select class="form-control" required  name="case_type">
								<option value="">----</option>
								<option>POEA</option>
								<option>OWWA</option>
								<option>LABOUR</option>
								<option>OTHERS</option>
								</select>
                        </div>
                      </div>
			 
			 <input type="hidden" class="form-control" name="date_added" value="<?=$date1?>" required>
                      	  
					  
					  
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">DATE HEARING</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="date" class="form-control" name="date_hearing" >
                        </div>
                      </div>
					
					  
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Applicant Name</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="applicant_name" required>
                        </div>
                      </div>
					 
					 <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Case</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
						<textarea name="caso" class="col-md-9 col-sm-9 col-xs-12"></textarea>	
                        </div>
                      </div>
					  
					   <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Remarks</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
						<textarea name="remarks" class="col-md-9 col-sm-9 col-xs-12"></textarea>	
                        </div>
                      </div>
					  
					  	 
					    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" style="color:red">STATUS</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
								<select class="form-control" required  name="status">
								<option value="">----</option>
								<option>LOW</option>
								<option>PRIOTIRY</option>
								<option>HIGH PRIORITY</option>
								<option>DELETE</option>
								</select>
                        </div>
                      </div>
					  
					  	  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">AGENT</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
								<select class="form-control" required  name="agent_id" >
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
