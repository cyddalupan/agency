<?php include'inc/header.php';
include'styles.php';
?>

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


</ul>                </li>
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

<h2>Settings</h2>


<div class="x_content">


<div class="x_panel">


<div class="x_content">





<form action="" method="post">
<table>
<tr>

<td><small style="color:blue">RATE STATUS</small>
<select class="form-control" required name="fixme"  required style="width:150px">
<option value="">--</option>
<option value="FIXED RATE">FIXED RATE</option>
<option value="OPEN RATE">OPEN RATE</option>
</select>
</td>




<td><small style="color:blue">CATEGORY</small>
<select class="form-control" required name="set_cat"  required style="width:150px">
<option value="">--</option>
<option value="EXPENSE">EXPENSE</option>
<option value="COLLECTION">COLLECTION</option>
</select>
</td>


<td><small style="color:blue">SUB CATEGORY</small>
<select class="form-control" required name="set_type"  required style="width:150px">
<option value="">--</option>

<option value="OFFICE EXPENSE">OFFICE EXPENSE</option>
<option value="AGENT EXPENSE">AGENT EXPENSE</option>
<option value="PERSONAL">PERSONAL</option>
<option value="AGENT COLLECTION">AGENT COLLECTION</option>
<option value="APPLICANT COLLECTION">APPLICANT COLLECTION</option>
<option value="FRA COLLECTION">FRA COLLECTION</option>
</select>
</td>


<td><small style="color:blue">Account</small><input type="text" class="form-control" name="account" required></td>

<td><small style="color:blue">Amount</small><input type="text" class="form-control" name="a_amount" required></td>

<td><input type="submit" name="add_set" value="Add "  class="btn btn-info btn-xs" ></td>

</tr>
</table>
</form>	




<div class="clearfix"></div>    <div class="clearfix"></div>
</br></br>

<h2 style="color:red">EXPENSE	CATEGORY</h2>
<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>

<th STYLE="width:3%">#</th>
<th ></th>
<th >RATE STATUS</th>
<th >CATEGORY</th>
<th >SUB CATEGORY</th>
<th >ACCOUNT</th>
<th >PRICE</th>
<th STYLE="color:blue">Total Peso</th>
</tr>
</thead>
<tbody>
<?php	
include'inc/functions.php';

if(isset($_POST['delete_orders'])){
foreach ($_POST['delall'] as $delall) {
$app11 = mysql_query("delete  FROM set_ where set_id= ".$delall)

or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted</p>";
}


$app = mysql_query("SELECT * FROM set_
WHERE set_cat IN('EXPENSE','OTHERS')
order by  set_type ASC");


while($row=mysql_fetch_array($app))
{
	
$expense = mysql_query("SELECT SUM(amount) as amount_total ,email_address.* FROM email_address
WHERE account='".$row["account"]."' AND request_payment IN (3)");
$expense1=mysql_fetch_array($expense);

echo "<form action='set-expense.php' method='post' target='_blank'>";

if	($expense1["amount_total"]==0) {$expnes88="--";}
if	($expense1["amount_total"]!=0) {$expnes88=$expense1["amount_total"];}	
echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$row['set_id']."' ></td> ";
echo'<td>
<button type="submit" class="btn btn-default" btn-xs" style="font-size:11px">Review</button>

<button type="button" class="btn btn-warning" btn-xs" style="font-size:11px">
<a href="set-edit.php?set='.$row["set_id"].'" target="_blank"   STYLE="color:black">Edit</button></a>

</td>';
echo'<td><B>'.$row["fixme"].'</B></td>';
echo'<td><B>'.$row["set_cat"].'</B></td>';
echo'<td>'.$row["set_type"].'</td>';
echo"<td><input type='text' name='account' value='".$row['account']."' readonly></td>";
echo'<td>'.$row["a_amount"].'</td>';
echo'<td style="color:Red"> '.number_format($expnes88,2).'</td>';
echo'</tr>';
echo "</form>";
}
?>
</tbody>
</table>






<form action="" method="post" style="width:500px;FLOat:left;margin-left:100px">	
<h2 style="color:red">COLLECTION CATEGORY	</h2>
<table  cellspacing="0" id="example-checkbox">
<thead>
<tr>

<th STYLE="width:3%">#</th>
<th ></th>
<th >CATEGORY</th>
<th >SUB CATEGORY</th>
<th >ACCOUNT</th>
<th STYLE="color:blue">Total Peso</th>
<th STYLE="color:blue">Total Dollar</th>
</tr>
</thead>
<tbody>
<?php	

if(isset($_POST['delete_orders1'])){
foreach ($_POST['delall'] as $delall) {
$collect = mysql_query("delete  FROM set_ where set_id= ".$delall)

or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted</p>";
}


$collect = mysql_query("SELECT * FROM set_
WHERE set_cat='COLLECTION'
order by  set_type ASC");


while($collect1=mysql_fetch_array($collect))
{
	
$apppeso = mysql_query("SELECT SUM(amount) as amount_total ,email_address6.* FROM email_address6
Where collect_type=1 and status_collect=0
AND account='".$collect1["account"]."' and currency='PHP' ");	
$apppeso1=mysql_fetch_array($apppeso);	
if	($apppeso1["amount_total"]==0) {$apppeso188="";}
if	($apppeso1["amount_total"]!=0) {$apppeso188=$apppeso1["amount_total"];}




$appdollar = mysql_query("SELECT SUM(amount) as amount_total ,email_address6.* FROM email_address6
Where collect_type=1 and status_collect=0
AND account='".$collect1["account"]."' and currency='USD' ");	
$appdollar1=mysql_fetch_array($appdollar);	
if	($appdollar1["amount_total"]==0) {$appdollar18="";}
if	($appdollar1["amount_total"]!=0) {$appdollar18=$appdollar1["amount_total"];}


	
echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$collect1['set_id']."' ></td> ";
echo'<td>
<button type="button" class="btn btn-default" btn-xs" style="font-size:11px">
<a href="set-collection.php?account='.$collect1["account"].'&&cur=PHP" target="_blank"  STYLE="color:black">Review ₱</button></a>

<button type="button" class="btn btn-info" btn-xs" style="font-size:11px">
<a href="set-collection.php?account='.$collect1["account"].'&&cur=USD" target="_blank"  STYLE="color:black">Review $</button></a>

<button type="button" class="btn btn-warning" btn-xs" style="font-size:11px">
<a href="set-edit.php?set='.$collect1["set_id"].'"  STYLE="color:black">Edit</button></a>


</td>';
echo'<td><B>'.$collect1["set_cat"].'</B></td>';
echo'<td>'.$collect1["set_type"].'</td>';
echo'<td>'.$collect1["account"].'</td>';
echo'<td style="color:Red"> '.number_format($apppeso188,2).'</td>';
echo'<td style="color:Red"> '.number_format($appdollar18,2).'</td>';
echo'</tr>';
}
?>
</tbody>
</table>

<div class="row">
<div class="col-5" style="width:100%,float:right">		
<input type="submit" value="Delete" name="delete_orders1" style="width:130px;background:green;padding:5px;margin-left:20px;margin-top:-1px;color:white">

</div>
</div>


</br></br>

</form>	




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
