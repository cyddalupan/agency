<?php include'inc/header2.php';
include'styles.php';
include'inc/functions.php';
include'tracking.php';
?>
<style>
table td{
color:black;	
}
form{
color:black;	
}
</style>
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
<span>Staff Request</span>
<h2></h2>
</div>
</div>
<!-- /menu profile quick info -->

<br />

<!-- sidebar menu -->
<div id="sidebar-menu" class="main_menu_side hidden-print main_menu">

<div class="menu_section">

<a href="http://promesa.recruitmensys.com/promesa/acct/"><button type="submit" class="btn btn-success"  name="delete">Login as Accounting</button></a>
<div class="clearfix"></div>

<ul class="nav side-menu" STYLE="font-size:11px">
<?php

echo'<li><a href="requeststaff.php?request=0&&textme=Payment Request&&imy='.$_GET["imy"].'"><i class="fa fa-folder-open"></i>Request</a></li>';
echo'<li><a href="requeststaff.php?request=1&&textme=Approved Request&&imy='.$_GET["imy"].'"><i class="fa fa-check"></i>Approved Request</a></li>';
echo'<li><a href="requeststaff.php?request=3&&textme=Released Request&&imy='.$_GET["imy"].'"><i class="fa fa-money"></i>Released Request</a></li>';
echo'<li><a href="requeststaff.php?request=5&&textme=Return to Maker&&imy='.$_GET["imy"].'"><i class="fa fa-exclamation-circle"></i>Return to Maker</a></li>';
echo'<li><a href="requeststaff.php?request=4&&textme=Declined Request&&imy='.$_GET["imy"].'"><i class="fa fa-exclamation-circle"></i>Declined Request</a></li>';
?>

</ul>



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


</ul>
</li>
</ul>
</nav>
</div>
</div>
<!-- /top navigation -->

<!-- page content -->
<div class="right_col" role="main">






<div class="row" >



<div class="col-md-12 col-xs-12"  >




</div>
<H2 style="color:black;text-decoration:underline">Add new REQUEST</H2>
<p>
<button onclick="goBack()">Go Back</button>

<script>
function goBack() {
  window.history.back();
}
</script></p>


<script>
function showapp(str) {

var xhttp;    
if (str == "") {
document.getElementById("sourcelist").innerHTML = "";
return;
}
xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
if (this.readyState == 4 && this.status == 200) {
document.getElementById("sourcelist").innerHTML = this.responseText;
}
};
xhttp.open("GET", "showapp.php?q="+str, true);
xhttp.send();
}





</script>


<form class="" action="" method="post">

<div class="x_panel">

<div class="x_content" style="background:#D3D3D3">
<div style="clear:both;height:10px"> </div>
<input type="hidden" class="form-control" id="inputSuccess3" name="requestby" value="<?=$_GET['imy']?>">
<input type="hidden" class="form-control" id="inputSuccess3" name="exp_status" value="1">
<input type="hidden" class="form-control" id="inputSuccess3" name="email_created" value="<?php echo date('Y-m-d H:i:s');?>">




<label class="control-label col-md-3 col-sm-3 col-xs-12">Payment Information</label>
<div style="clear:both;height:10px"> </div>

<div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
<select class="form-control" required name="agent_id"  onchange="showapp(this.value)">
<option value="">-SELECT Agent-</option>
<?php
$usertype = mysql_query("SELECT * FROM recruitment_agent ORDER BY agent_first asc");
while($rows=mysql_fetch_array($usertype))
{
echo'<option value="'.$rows["agent_id"].'"> '.$rows["agent_first"].' '.$rows["agent_last"].'</option>';
}
?>
</select>
</div>

<div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
<select class="form-control" required name="app_id" id="sourcelist"  >
<option value="">-SELECT Applicant-</option>
</select>
</div>





<label class="control-label col-md-3 col-sm-3 col-xs-12">Payment</label>
<div style="clear:both;height:10px"> </div>

<div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
<input type="number" class="form-control" id="inputSuccess5" placeholder="Amount" name="receivable" step="any"  required>
</div>

<div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
<input type="number" class="form-control" id="inputSuccess5" placeholder="Other charges" name="othercharge" step="any"  >
</div>

<label class="control-label col-md-3 col-sm-3 col-xs-12">Description</label>
<div style="clear:both;height:10px"> </div>


<div class="col-md-3 col-sm-6 col-xs-12 form-group has-feedback">
<select class="form-control" required name="currency">
<option value="">-SELECT Currency-</option>
<option>PHP</option>
</select>
</div>

<div class="col-md-3 col-sm-6 col-xs-12 form-group has-feedback">
<select class="form-control" required name="type_status">
<option value="">--CHARGE TO--</option>
<option>Agency</option>
<option>Agent</option>
<option>Applicant</option>

</select>
</div>



<div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
<?php echo'
<select class="form-control" name="exp_type" required>
<option>'.$row["account"].'</option>';
$set_ = mysql_query("SELECT * FROM set_ ORDER BY account asc");
while($setr=mysql_fetch_array($set_))
{
echo'<option value="'.$setr["account"].'">'.$setr["account"].'</option>';
}
echo'</select>';
?>
</div>

<div style="clear:both;height:10px"> </div>
<label class="control-label col-md-3 col-sm-3 col-xs-5">Date Transction</label>
<div class="col-md-3 col-sm-3 col-xs-5">
<input type="date" class="form-control" name="date_transaction" required>
</div>
</div>


<div style="clear:both;height:10px"> </div>
<div class="col-md-12 col-sm-6 col-xs-12 form-group has-feedback">
<input type="text" class="form-control" id="inputSuccess5" placeholder="Particular(s)" name="findings" >
</div>
</div>
</div>

<div style="clear:both;height:10px"> </div>

<div class="ln_solid"></div>
<div class="form-group">
<div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-9">
<button type="submit" class="btn btn-INFO"  name="new_unfit" style="color:white;background:red">Sava Transaction</button>
</div>
</div>
</form>

<div style="clear:both;height:100px"> </div>



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
<!-- Skycons -->
<script src="../vendors/skycons/skycons.js"></script>

<!-- Custom Theme Scripts -->
<script src="../build/js/custom.min.js"></script>

</body>









