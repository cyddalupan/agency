<?php include'inc/header.php';
 include'tracking.php';
include'inc/case.php';
$app = mysql_query("SELECT * FROM cases where case_id=".$_GET['case']." ");
$row=mysql_fetch_array($app);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);



?>

  <body class="nav-md" style="background:white">
   
      

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
          
            
            <div class="clearfix"></div>

            <div class="row" style="width:90%;margin:0 auto;margin-top:20px;margin-bottom:20px">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                 
                  <div class="x_content">
<div class="col-md-4 col-sm-12 col-xs-12">  
<h5>NAME</h5>     
<h2 style="color:Red"><?=$row['applicant_name']?></h2>    
</DIV>

<div class="col-md-4 col-sm-12 col-xs-12">      
<h5>Agent</h5>  
<h2><?=$row1['agent_first']?> <?=$row1['agent_last']?></h2>    
</DIV>
			
<DIV style="clear:both;height:30px"></DIV>
                     

                      <div class="" role="tabpanel" data-example-id="togglable-tabs">
                        <ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
                          <li role="presentation" class="active"><a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Case</a>
                          </li>
                          
                        </ul>
                        <div id="myTabContent" class="tab-content">
                          <div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">

								<?php include'form/case.php';?>

                          </div>
						  
						  
						  
						  
                       
						  
						  
                        </div>
                      </div>
                   
					
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
        
     

    <!-- jQuery -->
    <script src="../vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- FastClick -->
    <script src="../vendors/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="../vendors/nprogress/nprogress.js"></script>
    <!-- morris.js -->
    <script src="../vendors/raphael/raphael.min.js"></script>
    <script src="../vendors/morris.js/morris.min.js"></script>
    <!-- bootstrap-progressbar -->
    <script src="../vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
    <!-- bootstrap-daterangepicker -->
    <script src="../vendors/moment/min/moment.min.js"></script>
    <script src="../vendors/bootstrap-daterangepicker/daterangepicker.js"></script>
    
    <!-- Custom Theme Scripts -->
    <script src="../build/js/custom.min.js"></script>

  </body>
</html>