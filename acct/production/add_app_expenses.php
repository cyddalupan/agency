<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
       <meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>COMMISSION</title>

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
<?php;
include'tracking.php';
include'inc/expenses.php';
$app = mysql_query("SELECT * FROM applicant where applicant_id=".$_GET['appid']." ");
$row=mysql_fetch_array($app);

$CERT = mysql_query("SELECT * FROM applicant_certificate where certificate_applicant=".$_GET['appid']." ");
$certrow=mysql_fetch_array($CERT);

$REQ = mysql_query("SELECT * FROM applicant_requirement where requirement_applicant=".$_GET['appid']." ");
$reqrow=mysql_fetch_array($REQ);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$row1=mysql_fetch_array($app1);




?>

<body class="nav-md" style="background:#F5F5F5">



<!-- page content -->
<div class="right_col" role="main">
<div class="">


<div class="clearfix"></div>

<div class="row" style="width:90%;margin:0 auto;margin-top:20px;margin-bottom:20px">
<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">

<div class="x_content">
<div class="col-md-3 col-sm-12 col-xs-12">  
<h5>NAME</h5>     
<h2 style="color:Red"><?=$row['applicant_first']?> <?=$row['applicant_last']?></h2>    
</DIV>
<div class="col-md-3 col-sm-12 col-xs-12">    
<h5>Country</h5>   
<h2><?=$countryrow['country']?></h2>    
</DIV>
<div class="col-md-3 col-sm-12 col-xs-12">      
<h5>Agent</h5>  
<h2 style="color:green"><?=$row1['agent_first']?> <?=$row1['agent_last']?></h2>    
</DIV>

<div class="col-sm-2 invoice-col">

<a href="cert/pdos.php?app_id=<?=$_GET['appid']?>" style="COLOR:blue;font-size:12px" target="_blank">Print PDOS</a>

</div>

<DIV style="clear:both;height:30px"></DIV>


<div class="" role="tabpanel" data-example-id="togglable-tabs">
<ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
<li role="presentation" class="active"><a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Add Expenses</a>
</li>


<li role="presentation" class=""><a href="#tab_statuss" role="tab" id="profile-tab3" data-toggle="tab" aria-expanded="false"></a>
</li>


</ul>
<div id="myTabContent" class="tab-content">
<div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">

<form class="form-horizontal form-label-left input_mask" method="post" action="">	
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">DATE Transaction</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="date" class="form-control" name="date_ad" value="<?=$date1?>" required>
                        </div>
                      </div>
					
					 <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">VOUCHER #</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="ref">
                        </div>
                      </div>
					
					  
					  <div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12">Applicant</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
						<?php
						echo'<select class="form-control" name="app_id" required>
						';
						echo'<option value="'.$row["applicant_id"].'"> '.$row["applicant_first"].' '.$row["applicant_last"].' -'.$row["applicant_id"].'</option>';
						
						ECHO'</select>';
						?>

						</div>
						</div>
					  
					  
						<div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12">PAYEE</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
						<?php
						echo'<select class="form-control" name="agent_id" required>';

						echo'<option value="'.$row1["agent_id"].'"> '.$row1["agent_first"].' '.$row1["agent_last"].'-'.$row1["agent_id"].'</option>';
						
						ECHO'</select>';
						?>

						</div>
						</div>
					 
					<div class="form-group">
					<label class="control-label col-md-3 col-sm-3 col-xs-12">Type Payment</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
					<select class="form-control" name="method" required>
					<option>Cash</option>
					<option>Cheque</option>
					<option>Credit Card</option>
					<option>Direct Debit</option>
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
					<label class="control-label col-md-3 col-sm-3 col-xs-12">Currency</label>
					<div class="col-md-9 col-sm-9 col-xs-12" required>
					<select class="form-control" name="currency"   >
					<option></option>
					<option>PHP</option>
					<option>USD</option>
					</select>
					</div>
					</div>
					 
					  
				
					  
					 <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="number" class="form-control" name="amount" step="any" required>
                        </div>
                      </div>
					  
					  
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Particular(s)</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="description" >
                        </div>
                      </div>
					  	
					  
					  
					  	  <div class="form-group">
						<div class="col-md-12 col-sm-9 col-xs-12">	
							<button type="submit" class="btn btn-danger pull-right"  name="add_exp">Save</button>
                        </div>
                      </div>
					  
					  
			</form>	

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