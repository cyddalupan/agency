<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

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
  </head>

  <body class="nav-md">

        <!-- page content -->
        <div class="right_col" role="main">
 

         
              


  <div class="row">
               
					
					
					
					<div class="col-md-12 col-xs-12">


					<div class="x_panel">
				
					<h2>EXPENSES - <?php echo date('Y-M-d', strtotime($_POST['date_ad'])); ?> </h2>

					
					<div class="x_content">
					
					
					
					<div class="x_panel">


					<div class="x_content">

<?PHP
include'inc/functions5.php';
$app = mysql_query("SELECT * FROM email_address
where date_ad='".$_POST['date_ad']."' AND currency='".$_POST['type']."'");
?>

<form name="result_save" method="post">
<table class="table table-bordered">
<thead>
<tr>

<th >DATE</th>
<th >REF NO.</th>
<th >PAYEE</th>
<th >ACCOUNT</th>
<th >PAYMENT METHOD</th>
<th >DESCRIPTION</th>
<th STYLE="width:5%">CURRENCY</th>
<th STYLE="width:5%">AMOUNT</th>

</tr>
</thead>
<tbody>
<?php	

while($row=mysql_fetch_array($app))
{
$agent = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']."");
$agentrow=mysql_fetch_array($agent);
$totalamountPHP=$row['amount']+$totalamountPHP;
echo'<tr>';
echo'<td  style="width:110px" >'; echo date('Y-M-d', strtotime($row['date_ad']));echo' <input type="text" name="e_id[]" value="'.$row["e_id"].'"></td>';

echo'<td><input type="text" name="ref[]" value="'.$row["ref"].'" style="width:70px"></td>';
echo'<td>
<select class="form-control" name="agent_id[]"  style="width:160px">
<option value="'.$agentrow["agent_id"].'">'.$agentrow["agent_first"].' '.$agentrow["agent_last"].'</option>
<option value="444">MARICEL STAFF</option>
<option value="222">LASTLY STAFF</option>
<option value="444">RIZA STAFF</option>
<option value="555">ALLAM</option>
<option value="666">AHMAD</option>
<option value="777">OFFICE</option>
<option value="999">CASHIER</option>
<option value="888">Others</option>
';

$usertype = mysql_query("SELECT * FROM recruitment_agent ORDER BY agent_first asc");
while($rows=mysql_fetch_array($usertype))
{
echo'<option value="'.$rows["agent_id"].'">'.$rows["agent_first"].' '.$rows["agent_last"].'</option>';
}
ECHO'</select>
</td>';


echo'<td>

<select class="form-control" name="account[]"   style="width:160px" >
<option>'.$row["account"].'</option>';
$set_ = mysql_query("SELECT * FROM set_ ORDER BY account asc");
while($setr=mysql_fetch_array($set_))
{
echo'<option value="'.$setr["account"].'">'.$setr["account"].'</option>';
}


echo'</select>
</td>';


echo'<td>

<select class="form-control" name="method[]"  style="width:120px" >
<option>'.$row["method"].'</option>
<option>Cash</option>
<option>Cheque</option>
<option>Credit Card</option>
<option>Direct Debit</option>
</select>
</td>';





echo'<td>'.$row["description"].'</td>';
echo'<td>'.$_POST["type"].'</td>';
echo'<td><input type="text" name="amount[]" value="'.$row["amount"].'"></</td>';
echo'</tr>';
}
$php = number_format($totalamountPHP, 2);
?>



<tr>
<td  colspan="7"  style="font-weight:bold;text-align:right;font-size:14px;color:red"><?=$_POST["type"]?></td>
<td  style="font-weight:bold;text-align:right;font-size:14px;color:red"> <?=$php;?> </td>
</TR>

<tr>
<td  colspan="8"  style="font-weight:bold;text-align:right;font-size:14px;color:red">
<input type="submit" name="save_update1" value="UPDATE" style="float:right; padding:5px; font-size:14px; margin-right:30px" />
</form></td>
</TR>
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
