<?php
session_start();
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/css/bootstrap-select.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/js/bootstrap-select.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Employer/Principal</title>

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

         
              

			
<?php	
include'inc/or.php';
?>
     
       





  <!-- Modal -->
  <div class="" id="myModal" role="dialog" style="width:60%;margin:0 auto">
    <div class="">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Remittance Transaction</h4>
		  <?php $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>
 <a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a>

		 
        </div>
		
        <div class="modal-body">
					
			<form class="form-horizontal form-label-left input_mask" method="post" action="">	
					 <input type="hidden" class="form-control" name="user" value="<?=$_SESSION['user_fullname']?>" required>
					 
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">DATE OF PAYMENT</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="date" class="form-control" name="date" value="<?=$date1?>" required>
                        </div>
                      </div>
					  
					  					  <div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12">Applicant</label>
						<div class="col-md-9 col-sm-9 col-xs-12">
						<?php
						echo'<select class="selectpicker" single data-live-search="true" name="app_id" required>
						<option></option>
						';

						$applicant = mysql_query("SELECT * FROM  applicant
						where  applicant_employer=".$_GET['emp_id']."
						AND applicant_status IN(4,12,9,8)
						 AND applicant_paid!=1
						 ORDER BY applicant_last asc
						
						");
						while($rows1=mysql_fetch_array($applicant))
						{
						echo'<option value="'.$rows1["applicant_id"].'">'.$rows1["applicant_last"].' '.$rows1["applicant_first"].'  -'.$rows1["applicant_id"].'</option>';
						}
						ECHO'</select>';
						?>

						</div>
						</div>
						
						
						
						
			  
					  
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">OR #</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="or_num" >
                        </div>
                      </div>
					 
					 <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="number" class="form-control" name="amount" required>
                        </div>
                      </div>
					 
					 
				
						
							  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">CURRENCY</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
								<select class="form-control" required  name="currency" required>
								<option></option>
								<option>USD</option>
								<option>PHP</option>
								</select>
                        </div>
                      </div>
						
						
								 
				<div class="form-group">
				<label class="control-label col-md-3 col-sm-3 col-xs-12">Account Type</label>
				<div class="col-md-9 col-sm-9 col-xs-12">
				<?php echo'
				<select class="form-control" name="account" required>
				<option>'.$row["account"].'</option>';?>
				<option>remittance</option>
				<option>Processing Fee</option>
				<option>Replacement</option>
				<option>Others</option>
				</select>
				
				</div>
				</div>

 
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Paid Through  </label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="source_m" placeholder="BDO,BPI,WESTERN" >
                        </div>
                      </div> 


						
					    <input type="hidden" class="form-control" name="emp_id" value="<?=$_GET['emp_id']?>" readonly>
                        
						
						 <div class="form-group"  style="">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Remarks</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
						<textarea name="particular" class="col-md-9 col-sm-9 col-xs-12"></textarea>	
                        </div>
                      </div>
						
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Received By</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="received" value="<?=$_SESSION['user_fullname']?>" readonly>
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
