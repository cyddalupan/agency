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
$app = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$_GET['agent']." ");
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
	<h1 style="font-size:30px;color:black;text-align:center;TEXT-TRANSform:uppercase"><?=$row['agent_name']?> </h1>

	</div>
	
	 <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
           

            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">
                <div class="x_panel">
             
                  <div class="x_content">

<?php 
$result1 = mysql_query("SELECT count(agent_id) as alls FROM  email_address2 where agent_id='".$_GET['agent']."'");
$all = mysql_fetch_array($result1);

$result11 = mysql_query("SELECT count(agent_id) as hkg,country_id,agent_id FROM  email_address2 where agent_id='".$_GET['agent']."' and country_id=8 ");
$hkg = mysql_fetch_array($result11);

$result2 = mysql_query("SELECT count(agent_id) as kuwait,country_id,agent_id FROM  email_address2 where agent_id='".$_GET['agent']."' and country_id=4");
$kuwait = mysql_fetch_array($result2);

$result3 = mysql_query("SELECT count(agent_id) as malaysia,country_id,agent_id FROM  email_address2 where agent_id='".$_GET['agent']."' and country_id=5");
$malaysia = mysql_fetch_array($result3);

$result4 = mysql_query("SELECT count(agent_id) as singapore,country_id,agent_id FROM  email_address2 where agent_id='".$_GET['agent']."' and country_id=11");
$singapore = mysql_fetch_array($result4);

$result5 = mysql_query("SELECT count(agent_id) as qatar,country_id,agent_id FROM  email_address2 where agent_id='".$_GET['agent']."' and country_id=6");
$qatar = mysql_fetch_array($result5);

$result6 = mysql_query("SELECT count(agent_id) as bahrain,country_id,agent_id FROM  email_address2 where agent_id='".$_GET['agent']."' and country_id=12");
$bahrain = mysql_fetch_array($result6);

$result7 = mysql_query("SELECT count(agent_id) as noc,country_id,agent_id FROM  email_address2 where agent_id='".$_GET['agent']."' and country_id=10");
$noc = mysql_fetch_array($result7);


$result8 = mysql_query("SELECT count(agent_id) as dubai,country_id,agent_id FROM  email_address2 where agent_id='".$_GET['agent']."' and country_id=3");
$dubai = mysql_fetch_array($result8);

$result9 = mysql_query("SELECT count(agent_id) as saudi,country_id,agent_id FROM  email_address2 where agent_id='".$_GET['agent']."' and country_id=1");
$saudi = mysql_fetch_array($result9);

$result11 = mysql_query("SELECT count(agent_id) as brunei,country_id,agent_id FROM  email_address2 where agent_id='".$_GET['agent']."' and country_id=9");
$brunei = mysql_fetch_array($result11);

$result12 = mysql_query("SELECT count(agent_id) as taiwan,country_id,agent_id FROM  email_address2 where agent_id='".$_GET['agent']."' and country_id=13");
$taiwan = mysql_fetch_array($result12);




?>
					<section class="content invoice">
                   <div class="row invoice-info">
				
				
			
				
				<div class="col-sm-2 invoice-col pull-right">
				<a href="add_unfit.php?agent=<?=$_GET['agent']?>"><button type="button" class="btn btn-round btn-danger pull-right">Add New Payment</button></a>
				</br>
				<a href="print.php?agent=<?=$_GET['agent']?>" style="COLOR:red;font-size:16px">Print</a>
				
				</div>
				
				
				
				
				
			</div>
				
</BR>
				
				<!-- Table row -->
				<div class="row">
				
				
				
				
						<!-- Table row -->
				<div class="row">
				<div class="col-xs-12 table">
				<h2 style="color:Red">PAID IN PESO</h2>
				   <div style="width:100%; height:200px; overflow:auto;">
				 <table class="table table-fixed">
				<thead>
				<tr>
				<th>Date</th>
				<th>OR #</th>
				<th style="width: 59%">Description</th>
				<th>Amount</th>
				</tr>
				
				</thead>
				<tbody>
					<?PHP
					$col = mysql_query("SELECT * FROM email_address6 where  agent_id=".$_GET['agent']."
					AND currency='PHP'
					order by date desc");
					while($colphp=mysql_fetch_array($col))
					{
					$colphp1=$colphp['amount']+$colphp1;
					echo'<tr>';
					echo'<td>'; echo date('Y-M-d', strtotime($colphp['date']));echo'</td>';
					echo'<td>'.$colphp["or_num"].'</td>';
					echo'<td>'.$colphp["particular"].'</td>';
					echo'<td  style="text-align:right">'.$colphp["amount"].'</td>';
					echo'</tr>';
					}
					?>	
					<tr>
					<td style="color:Red;text-align:right;font-size:18px" colspan="5"><?php echo number_format($colphp1,2);?></td>
					</tr>	
				</tbody>
				</table>
				</div>
				</BR>
				
				
				
				
				
				<h2 style="color:ORANGE">FOR PAYMENT PHP</h2>
				
				
				<table class="table table-fixed">
				<thead>
			
				<tr>
				<th>#</th>
				<th>Date Paid</th>
				<th>Applicant</th>
				<th>Date Med</th>
				<th style="width: 29%">Description</th>
				<th style="text-align:right">Currency</th>
				<th style="text-align:right">Receivable</th>
				<th style="text-align:right">Amount Paid</th>
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
					echo'<td>'.$row["date_paid"].'</td>';
					echo'<td><a href="edit_unfit.php?exp='.$row["exp_id"].'" target="_blank" style="color:blue">'.$row["app_id"].'</a></td>';
					echo'<td>'.$row["date_med"].'</td>';
					echo'<td>'.$row["findings"].'</td>';
					echo'<td style="text-align:right">'.$row["currency"].'</td>';
					echo'<td style="text-align:right">'.$row["receivable"].'</td>';
					echo'<td  style="text-align:right">'.$row["amount_paid"].'</td>';
					echo'</tr>';
					}
					?>	
					<tr>
					<td style="color:black;text-align:right;font-size:18px" colspan="7">TOTAL RECEIVABLE <?php echo number_format($total,2);?></td>
					</tr>
					<tr>
					<td style="color:blue;text-align:right;font-size:18px" colspan="7">PAID:  <?php echo number_format($colphp1,2);?></td>
					</tr>
					<tr>
					<td style="color:Red;text-align:right;font-size:18px" colspan="7">GRAND TOTAL:  <?php echo number_format($php=$total-$colphp1,2);?></td>
					</tr>					
				</tbody>
				</table>
				
				
				
				
				
				
				
				
				
				
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