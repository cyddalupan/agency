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
<?php
include'db.php';

if(isset($_POST['add_saved'])){
$app11 = mysql_query("UPDATE set_
SET 
set_cat = '".$_POST['set_cat']."'
,set_type = '".$_POST['set_type']."'
,account = '".$_POST['account']."'
,a_amount = ".$_POST['a_amount']."
,fixme = '".$_POST['fixme']."'
Where set_id=".$_GET['set']." ");
echo'<h2 style="color:red">SAVE....</h2>';
}

$app = mysql_query("SELECT * FROM set_ where set_id=".$_GET['set']." ");
$row=mysql_fetch_array($app);
?>



<div class="col-md-12 col-xs-12">
<div class="x_panel">
<h2>Edit Settings <small><a href="settings.php" style="color:Red">Back</a></small></h2>
<div class="x_content">
<div class="x_panel">
<div class="x_content">



<form action="" method="post">
<table>
<tr>

<td><small style="color:blue">RATE STATUS</small>
<select class="form-control" required name="fixme"  required style="width:150px">
<option><?=$row["fixme"]?></option>
<option value="FIXED RATE">FIXED RATE</option>
<option value="OPEN RATE">OPEN RATE</option>
</select>
</td>



<td><small style="color:blue">CATEGORY</small>
<select class="form-control" required name="set_cat"  required style="width:150px">
<option><?=$row["set_cat"]?></option>
<option></option>
<option value="EXPENSE">EXPENSE</option>
<option value="COLLECTION">COLLECTION</option>
</select>
</td>


<td><small style="color:blue">SUB CATEGORY</small>
<select class="form-control" required name="set_type"  required style="width:150px">
<option><?=$row["set_type"]?></option>
<option></option>
<option value="APPLICANT EXPENSE">APPLICANT EXPENSE</option>
<option value="OFFICE EXPENSE">OFFICE EXPENSE</option>
<option value="PERSONAL">PERSONAL</option>
<option value="AGENT EXPENSE">AGENT EXPENSE</option>
<option value="AGENT & APPLICANT PAYMENT">AGENT & APPLICANT PAYMENT</option>
<option value="AGENT COLLECTION">AGENT COLLECTION</option>
<option value="APPLICANT COLLECTION">APPLICANT COLLECTION</option>
<option value="FRA COLLECTION">FRA COLLECTION</option>
</select>
</td>


<td><small style="color:blue">Account</small><input type="text" class="form-control" name="account" value="<?=$row["account"]?>"></td>
<td><small style="color:blue">Amount</small><input type="text" class="form-control" name="a_amount" value="<?=$row["a_amount"]?>"></td>



<td><input type="submit" name="add_saved" value="Save "  class="btn btn-info btn-xs" ></td>

</tr>
</table>
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

</body>
</html>
