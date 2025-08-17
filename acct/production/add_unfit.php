<?php
session_start();
include'inc/functions.php';
include'tracking.php';
$app = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$_GET['agent']." ");
$row=mysql_fetch_array($app);
?>
<html lang="en">
  <head>
      <meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
    <title>Accounting System</title>

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
<style>
#wrapper{
border:0px solid black;
width:90%;
min-height:630px;
height:!important;
margin: 0 auto;	
padding:10px;
font-size:12px;
font-family:calibri;
}
.table-fixed td,th{
font-size:13px;
padding:2px;
}


</style>

<body style="background:white">
<div id="wrapper">

	<div>
	<h1 style="font-size:30px;color:black;text-align:center;"><?=$row['agent_first']?> <?=$row['agent_last']?></h1>
	<p><?=$row['agent_conracts']?> </p>
	</div>
	
	 <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
           

            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">
                <div class="x_panel">
             
                  <div class="x_content">


					<section class="content invoice">
                  
				
				
			</div>
				
</BR>
				
				<!-- Table row -->
				<div class="row">
				<div class="col-xs-12 table">
			
			
			
			
			<div class="row">
              
					<div class="col-md-8 col-xs-8">


					<div class="x_panel">
					
					<h2>Add New Transaction<small style="color;black"> Expenses</small></h2>

				
					<div class="x_content">

					<form class="" action="" method="post">

					<div class="x_panel">

					<div class="x_content">
					
					 <input type="hidden" class="form-control" id="inputSuccess3" name="agent_id" value="<?=$_GET['agent']?>">
                     <input type="hidden" class="form-control" id="inputSuccess3" name="user" value="<?=$_SESSION['staff']?>">
                     <input type="hidden" class="form-control" id="inputSuccess3" name="exp_status" value="1">
                     
					  <input type="hidden" class="form-control" id="inputSuccess3" name="email_created" value="<?php echo date('Y-m-d H:i:s');?>">
                    
					  <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
                        <input type="text" class="form-control has-feedback-left" id="inputSuccess2" placeholder="Applicant Name" name="app_id" required>
                        <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span>
                      </div>

                       
					 <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
                        <input type="number" class="form-control" id="inputSuccess5" placeholder="Amount" name="receivable" required>
                      </div>

                    

					  
					  <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
                      <select class="form-control" required name="currency">
							<option value="">-SELECT Currency-</option>
							<option>USD</option>
							<option>PHP</option>
						</select>
					  </div>

					  
					  
					  
					   <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
                        <input type="text" class="form-control" id="inputSuccess5" placeholder="Country" name="country" required>
                      </div>
					  
					  
					   <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
                      <select class="form-control" required name="exp_type">
<?php 
echo'<option value="">-Select Expenses-</option>';
$set_ = mysql_query("SELECT * FROM set_ ORDER BY account asc");
while($setr=mysql_fetch_array($set_))
{
echo'<option value="'.$setr["account"].'">'.$setr["account"].'</option>';
}
echo'</select>';
?>
					  </div>

                      
					  
					  
						<label class="control-label col-md-3 col-sm-3 col-xs-5">Date Transction</label>
                        <div class="col-md-3 col-sm-3 col-xs-5">
                          <input type="date" class="form-control" name="date_transaction" required>
                        </div>
                      </div>
					  
					  
					  </br>
					    <div class="col-md-12 col-sm-6 col-xs-12 form-group has-feedback">
                        <input type="text" class="form-control" id="inputSuccess5" placeholder="Description" name="findings" required>
                      </div>
                   
                   </br></br></br>
					
					

				



					</div>
					</div>


					<div class="ln_solid"></div>
					<div class="form-group">
					<div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-9">

					<button type="submit" class="btn btn-danger"  name="new_unfit">Sava Transaction</button>
					</div>
					</div>

					</form>
					</div>
					</div>
					</div>  
					
					
					
		<div style="clear:both"></div>			
					
<h2 style="color:Green">Applicant Expense</h2>
				
				  <div style="">
				 
				<table class="table table-fixed">
				<thead>
				
				<tr>
				<th>#</th>
				<th style="width: 29%">Description</th>
				<th style="text-align:right">Currency</th>
				<th style="text-align:right">Receivable</th>
				</tr>
				
				</thead>
				<tbody>
					<?PHP
					$app = mysql_query("SELECT * FROM email_address4 where  agent_id=".$_GET['agent']."  AND currency='PHP'
					 and receivable!=0
					order by app_id desc ");
					while($row=mysql_fetch_array($app))
					{
					$total=$row['receivable']+$total;
					echo'<tr>';
					echo'<td>'.$row["agent_id"].'</td>';
				
					echo'<td>'.$row["findings"].'</td>';
					echo'<td style="text-align:right">'.$row["currency"].'</td>';
					echo'<td style="text-align:right">'.$row["receivable"].'</td>';
					echo'</tr>';
					}
					?>	
					<tr>
					<td style="color:Red;text-align:right;font-size:18px" colspan="7"> <?php echo number_format($total,2);?></td>
					</tr>	
				</tbody>
				</table>
				
				</div>
				
				
				
				</BR></BR>
				
			
			
			
		</div>			
				

			
			
			
				
				
				
				
				</div>
				
				
				
				</div>
				<!-- /.col -->
				</div>
				<!-- /.row -->

                     
                    </section>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /page content -->
	
	
</div>

</body>