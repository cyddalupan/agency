<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Medical System</title>

    <!-- Bootstrap -->
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="../vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- iCheck -->
    <link href="../vendors/iCheck/skins/flat/green.css" rel="stylesheet">
	
    <!-- bootstrap-progressbar -->
    <link href="../vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
    <!-- JQVMap -->
    <link href="../vendors/jqvmap/dist/jqvmap.min.css" rel="stylesheet"/>
    <!-- bootstrap-daterangepicker -->
    <link href="../vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="../build/css/custom.min.css" rel="stylesheet">
  </head>

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
                <ul class="nav side-menu">
                                 
                  <li><a href="index.html"><i class="fa fa-laptop"></i>dashboard</a></li>
				   <li><a href="sales.html"><i class="fa fa-file"></i>Sales</a></li>
				    <li><a href="employer.html"><i class="fa fa-users"></i>Employer</a></li>
					 <li><a href="medical.html"><i class="fa fa-users"></i>Medical</a></li>
                </ul>
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
                    <img src="images/img.jpg" alt="">John Doe
                    <span class=" fa fa-angle-down"></span>
                  </a>
                  <ul class="dropdown-menu dropdown-usermenu pull-right">
                    <li><a href="javascript:;"> Profile</a></li>
                    <li>
                      <a href="javascript:;">
                        <span class="badge bg-red pull-right">50%</span>
                        <span>Settings</span>
                      </a>
                    </li>
                    <li><a href="javascript:;">Help</a></li>
                    <li><a href="login.html"><i class="fa fa-sign-out pull-right"></i> Log Out</a></li>
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
					<div class="x_title">
					<h2>REMITANCE TRANSACTION</h2>

					</div>
					<div class="x_content">
					
					
					<a href="new_sales.html"><button type="submit" class="btn btn-primary btn-sm pull-right">NEW TRANSACTION</button></a>
					
					<form class="form-horizontal form-label-left input_mask">

					<div class="x_panel">

					<div class="x_content">
					
						<?php 
						$result = $db->query("SELECT * FROM applicant 
						LEFT JOIN applicant_requirement	
						ON applicant.applicant_id=applicant_requirement.requirement_id
							LEFT JOIN applicant_certificate	
						ON applicant.applicant_id=applicant_certificate.certificate_id
						
							LEFT JOIN recruitment_agent	
						ON applicant.applicant_source=recruitment_agent.agent_id
					
						 ");
						?>
					
					
					

					<table class="table table-bordered">
					<thead>
					<tr>
					
					<th STYLE="width:3%">DATEAPPLIED</th>
					<th STYLE="width:3%">APPLICANT_NAME</th>
					<th STYLE="width:3%">AGENT_NAME</th>
					<th STYLE="width:3%">MEDICAL_DATE_ISSUE</th>
					<th STYLE="width:3%">MEDICAL_DATE</th>
					<th STYLE="width:3%">MEDICAL_RESULT</th>
					<th STYLE="width:3%">REMARKS</th>
					<th STYLE="width:3%">ACTION</th>
					</tr>
					</thead>
					<tbody>
					
					<?php
										while($row = $result->fetch(PDO::FETCH_ASSOC)) {
					
					echo'<tr>';

					echo'<td>'.$row['applicant_date_applied'].' </td>';
					echo'<td>	'.$row['applicant_last'].','.$row['applicant_firts'].' '.$row['applicant_middle'].'	</td>';
					echo'<td>	'.$row['agent_firts'].' '.$row['agent_last'].'	</td>';
					echo'<td>	'.$row['med_issue'].'	</td>';
					echo'<td>	'.$row['certificate_medical_exam_date'].'	</td>';
					echo'<td>	'.$row['certificate_medical_result'].'	</td>';
					echo'<td>   '.$row['certificate_medical_remarks'].'	</td>';
					echo'<td>	<a href="#" STYLE="COLOR:BLUE">PRINT</A>	</td>';
					echo'</tr>';
				?>


				

					</tbody>
					</table>

					</div>
					</div>


					<div class="ln_solid"></div>
				
					</form>
					</div>
					</div>
					</div>  
					

			
			
		</div>			
				
				
				
				
				

            
                
                

        <!-- footer content -->
        <footer>
          <div class="pull-right">
             <a href="https://colorlib.com"></a>
          </div>
          <div class="clearfix"></div>
        </footer>
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
