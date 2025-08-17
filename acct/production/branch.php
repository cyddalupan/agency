<?php include'inc/header.php';?>

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
 

         
              


  <div class="row">
               
					
				 <button type="button" class="btn btn-danger btn-xs pull-right" data-toggle="modal" data-target="#myModal">PAY NOW!</button>		
				
					
					<div class="col-md-12 col-xs-12">


					<div class="x_panel">
				
					<h2 >BRANCH LIST</h2>

					
					<div class="x_content" style="margin-top:-20px;border:none">
					
				
			  <div class="clearfix"></div>
                  	
     <div class="row">
                    <br />
                    <form id="demo-form2" data-parsley-validate class="form-horizontal form-label-left" method="post">

                      <div class="form-group">
						<div class="col-md-4 col-sm-4 col-xs-4 form-group">
						<input type="text" placeholder="First Name, Last Name" class="form-control" name="agent_name" required>
						<input type="hidden" class="form-control" name="typeid" value="2">
						</div>
						
						 <div class="col-md-6 col-sm-12 col-xs-12 form-group">
							<?php
							if($_SESSION['user_type']==77 || $_SESSION['user_type']==88){
							echo'<button type="submit" class="btn btn-info"  name="Search" >Search branch</button>';
							echo'<button type="submit" class="btn btn-danger" name="add_agent" >Add New BRANCH</button>';
							
							}
							if($_SESSION['user_type']==99){
							echo'<button type="submit" class="btn btn-info"  name="Search" >Search branch</button>';
							}
							?>
                        </div>
						  </form>
						 
						
						
						
                      </div>
                     
                      </div>
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        
                      </div>

                    
                  </div>
 <br />
<script>
//code to export html to excel coded by:chachi yr: 2000
//no need to edit bellow
var tableToExcel = (function() {
  var uri = 'data:application/vnd.ms-excel;base64,'
    , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
    , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
    , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
  return function(table, name) {
    if (!table.nodeType) table = document.getElementById(table)
    var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
    window.location.href = uri + base64(format(template, ctx))
  }
})()

</script>
	<input type="button" onclick="tableToExcel('testTable', 'Alumni Tracer')" value="Export to Excel" style="background:red;color:white;padding:3px;float:right">

	<table class="table table-bordered" id="testTable">
	<thead>
	<tr>

	<th STYLE="width:2%">#</th>
	<th STYLE="width:15%">Agent Name</th>
	<th STYLE="width:10%">APPLICANTS</th>
	<th STYLE="width:8%">USDCOM</th>
	<th STYLE="width:8%">PHPCOM</th>
	
	<th STYLE="width:12%">RECEIVABLE PHP</th>
	<th STYLE="width:12%">RECEIVABLE USD</th>
	<th STYLE="width:10%">Collected</th>
	<th STYLE="width:10%">Action</th>
	</tr>
	</thead>
	<tbody>
<?php	
include'inc/or.php';
$app = mysql_query("SELECT * FROM recruitment_agent
WHERE typeid=2
order by agent_name asc");

if(isset($_POST['Search']))
{
$app = mysql_query("SELECT * FROM recruitment_agent where
agent_name LIKE '%".$_POST['agent_name']."%' OR agent_name LIKE '%".$_POST['agent_name']."%'
");
}


while($row=mysql_fetch_array($app))
{
$app1 = mysql_query("SELECT SUM(amount) as USD ,agent_id,currency FROM email_address
WHERE agent_id=".$row["agent_id"]." AND currency='USD'");
$usd=mysql_fetch_array($app1);


$unfit = mysql_query("SELECT SUM(receivable) as receive ,agent_id,receivable FROM email_address4
WHERE agent_id='".$row["agent_id"]."'  AND currency='PHP'");
$unfitrow=mysql_fetch_array($unfit);

$CON = mysql_query("SELECT SUM(receivable) as receiveusd ,agent_id,receivable FROM email_address4
WHERE agent_id='".$row["agent_id"]."'  AND  currency='USD' ");
$contract=mysql_fetch_array($CON);

$result1 = mysql_query("SELECT count(agent_id) as alls FROM  email_address2 where agent_id='".$row['agent_id']."'");
$all = mysql_fetch_array($result1);


$col = mysql_query("SELECT SUM(amount) as amountusd ,agent_id,amount FROM email_address6
WHERE agent_id='".$row["agent_id"]."'  AND  currency='USD' ");
$colusd=mysql_fetch_array($col);


$colS = mysql_query("SELECT SUM(amount) as amounPHP ,agent_id,amount FROM email_address6
WHERE agent_id='".$row["agent_id"]."'  AND  currency='PHP' ");
$colphp=mysql_fetch_array($colS);



echo'<tr>';
echo'<td>'.$row["agent_id"].'</td>';
echo'<td> '.$row["agent_name"].'</td>';
echo'<td style="color:black">'.$all["alls"].'</td>';
echo'<td style="color:black">'.$usd["USD"].'</td>';
echo'<td></td>';
if($unfitrow["receive"]!=0){
echo'<td style="color:red">'.number_format($totalPHP=$unfitrow["receive"]-$colphp["amounPHP"],2).'</td>';
}
if($unfitrow["receive"]==0){
echo'<td style="color:red">'.number_format($totalPHP=$unfitrow["receive"],2).'</td>';
}

if($contract["receiveusd"]!=0){
echo'<td style="color:green">'.number_format($totalusd=$contract["receiveusd"]-$colusd["amountusd"],2).'</td>';
}
if($contract["receiveusd"]==0){
echo'<td style="color:green">'.number_format($totalusd=$contract["receiveusd"],2).'</td>';
}
echo'<td style="color:RED">'.number_format($totalPHP=$colphp["amounPHP"],2).' PHP </BR>
<B style="color:green">'.number_format($totalPHP=$colusd["amountusd"],2).' USD</B></td>';

//echo'<td style="color:orange">'.number_format($colusd["amountusd"],2).'</td>';
//echo'<td style="color:orange">'.number_format($colphp["amounPHP"],2).'</td>';
echo'<td><a href="profile.php?agent='.$row["agent_id"].'" target="_blank" style="color:blue;font-size:11px">View</a> | 
<a href="print.php?agent='.$row["agent_id"].'" target="_blank" style="color:RED;font-size:11px">PRINT</a>
</td>';

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


  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">New Transaction</h4>
        </div>
        <div class="modal-body">
					
			<form class="form-horizontal form-label-left input_mask" method="post" action="">	
					 <input type="hidden" class="form-control" name="user" value="<?=$_SESSION['user_fullname']?>" required>
					 
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">DATE APPLIED</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="date" class="form-control" name="date" value="<?=$date1?>" required>
                        </div>
                      </div>
					
					  
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">OR #</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="or_num" required>
                        </div>
                      </div>
					 
					 <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="number" class="form-control" name="amount" required>
                        </div>
                      </div>
					 
					 
						 <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Particulars</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
						<textarea name="particular" class="col-md-9 col-sm-9 col-xs-12"></textarea>	
                        </div>
                      </div>
						
						
							  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">CURRENCY</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
								<select class="form-control" required  name="currency" required>
								<option VALUE=""></option>
								<option>PHP</option>
								<option>USD</option>
								
								</select>
                        </div>
                      </div>
						
						
								 
				<div class="form-group">
				<label class="control-label col-md-3 col-sm-3 col-xs-12">Account</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
				<?php echo'
				<select class="form-control" name="account" required>
				<option>'.$row["account"].'</option>';
				$set_ = mysql_query("SELECT * FROM set_ ORDER BY account asc");
				while($setr=mysql_fetch_array($set_))
				{
				echo'<option value="'.$setr["account"].'">'.$setr["account"].'</option>';
				}
				echo'</select>';
				?>
				</div>
				</div>
						
						
					  	  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Agent</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
								<select class="form-control" required  name="agent_id" required>
								<option>-----</option>
								<?php
								$usertype = mysql_query("SELECT * FROM recruitment_agent 
								WHERE typeid=2
								ORDER BY agent_name asc");

								while($rows=mysql_fetch_array($usertype))
								{
								echo'<option value="'.$rows["agent_id"].'">'.$rows["agent_name"].'</option>';
								}
								?>
								</select>
                        </div>
                      </div>
					  
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Received By</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="received" value="<?=$_SESSION['user_fullname']?>" readonly>
                        </div>
                      </div
					  
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
