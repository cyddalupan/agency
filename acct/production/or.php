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
				
<h2 style="color:red"><?=$_POST['agent_id']?></h2>

					
					<div class="x_content">
					
					
					
					<div class="x_panel">


					<div class="x_content">

<?PHP
include'inc/or.php';
$app = mysql_query("SELECT * FROM email_address6 order  by date desc limit 5");
if(isset($_POST['submitsearch']))
{
$app = mysql_query("SELECT * FROM email_address6 where agent_id='".$_POST['agent_id']."' ");

}
?>
<form action="" method="post" style="width:400px;float:left" >

<select class="form-control" name="agent_id"  style="width:160px">

<?php

$usertype = mysql_query("SELECT * FROM email_address6  group by agent_id desc");
echo'<option value=""></option>';

while($rows=mysql_fetch_array($usertype))
{
echo'<option value="'.$rows["agent_id"].'">'.$rows["agent_id"].'</option>';
}
ECHO'</select>';
?>
<input type="submit" name="submitsearch" value="Search" style="width:80px;flot:right" >

</form>

<form name="result_save" method="post">
<table class="table table-bordered">
<thead>
<tr>


<th >#</th>
<th ></th>
<th >agent</th>

<th>OR #</th>
<th>amount</th>
<th>DESCRIPTION</th>
<th>DATE</th>
</tr>
</thead>
<tbody>
<?php	

while($row=mysql_fetch_array($app))
{
$UNIX_DATE = (($row["date"] - 25569) * 86400);
//echo gmdate("d-m-Y H:i:s", $UNIX_DATE);

echo'<tr>';
echo'<td  style="width:30px" >';echo' <input type="HIDDEN" name="tr_id[]" value="'.$row["tr_id"].'">'.$row["tr_id"].'</td>';

echo'<td  style="width:30px" >';echo' '.$row["agent_id"].'</td>';

echo'<td>
<select class="form-control" name="agent_id[]"  style="width:160px" required>
<option value=""></option>';
$usertype = mysql_query("SELECT * FROM recruitment_agent ORDER BY agent_first asc");
while($rows=mysql_fetch_array($usertype))
{
echo'<option value="'.$rows["agent_id"].'">'.$rows["agent_first"].' '.$rows["agent_last"].'-'.$rows["agent_id"].'</option>';
}
ECHO'</select>
</td>';
//echo'<td><input type="text" name="date_applied[]" value="'; echo gmdate("Y-m-d", $UNIX_DATE);echo'"></td>';
//echo'<td><input type="text" name="date_medical[]" value="'.$row["date_medical"].'"></</td>';

echo'<td>'.$row["or"].'</</td>';
echo'<td>'.$row["amount"].'</</td>';
echo'<td>'.$row["particular"].'</</td>';
echo'<td><input type="text" name="date[]" value="'; echo gmdate("Y-m-d", $UNIX_DATE);echo'">'.$row["date"].'</td>';
echo'</tr>';
}
?>


<tr>
<td  colspan="8"  style="font-weight:bold;text-align:right;font-size:14px;color:red">
<input type="submit" name="app_save" value="UPDATE" style="float:right; padding:5px; font-size:14px; margin-right:30px" />
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
