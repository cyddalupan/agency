<!DOCTYPE html>
<html lang="en">
  <head>
      <meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
   


  </head>
<?php
include'inc/functions.php';
$app = mysql_query("SELECT * FROM employer where emp_id=".$_GET['emp_id']." ");
$row=mysql_fetch_array($app);
?>

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
}gent_i


</style>

<body style="background:white">
<div id="wrapper">

	<div>
	<h1 style="font-size:30px;color:black;text-align:center;text-transform:uppercase"><?=$row['agent_name']?> </h1>

	</div>
	
	 <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
           

            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">
                <div class="x_panel">
             
                  <div class="x_content">
				<div class="col-sm-2 invoice-col pull-right">
				<a href="add_marketing_pay.php?emp_id=<?=$_GET['emp_id']?>"><button type="button" class="btn btn-round btn-danger pull-right">Add New Payment</button></a>
				</br>
				<a href="print_employer.php?emp_id=<?=$_GET['emp_id']?>" style="COLOR:red;font-size:16px">Print</a>
				
				</div>	
				
			</div>
				
</BR>
					
			
			
				
				
				
				
				
				<h2 style="color:GREEN">PAYMENT DOLLAR</h2>
				
				  <div style="width:100%; height:200px; overflow:auto;">
		 <form action="" method="post">
			 
				<table class="table table-fixed">
				<thead>
			
				
				<tr>
				<th>Date Paid</th>
				<th>Applicant</th>
				<th>Date</th>
				<th style="">Type</th>
				<th style="width: 29%">Particulars</th>
				<th style="text-align:right">Currency</th>
				<th style="text-align:right">Receivable</th>
				<th style="text-align:right">Amount Paid</th>
				<th style="text-align:right">Action</th>
				</tr>
				
				</thead>
				<tbody>
					<?PHP
					$app1 = mysql_query("SELECT * FROM email_address4 where  emp_id=".$_GET['emp_id']." AND currency='USD'
					order by date_transaction desc");
					while($row1=mysql_fetch_array($app1))
					{
					$total1=$row1['receivable']+$total1;
					echo'<tr>';
					echo'<td>'.$row1["date_paid"].'</td>';
					echo'<td><a href="edit_unfit.php?exp='.$row1["exp_id"].'" target="_blank" style="color:blue">'.$row1["nickname"].'</a></td>';
					echo'<td>'.$row1["date_transaction"].'</td>';
					echo'<td>'.$row1["exp_type"].'</td>';
					echo'<td>'.$row1["findings"].'</td>';
					echo'<td style="text-align:right">'.$row1["currency"].'</td>';
					echo'<td style="text-align:right">'.$row1["receivable"].'</td>';
					echo'<td  style="text-align:right">'.$row1["amount_paid"].'</td>';
					echo'<td  style="text-align:right">'.$row1["user"].' '.$row1["email_updated"].'</td>';
					echo'</tr>';
					}
					?>	
					</div>
					<tr>
					<td style="color:black;text-align:right;font-size:16px" colspan="7">  <?php echo number_format($total1,2);?></td>
					</tr>
										
				</tbody>
				</table>
				 </form>	
				
				
				
				
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

</body></html>