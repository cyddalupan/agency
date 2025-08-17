<?php
session_start();
if(!isset($_SESSION['smsadmin'])){
header("location:../login-account.php");
}
include'../include/booking.php';

$todaysched =mysql_query("SELECT 
count(DISTINCT sched_user) as alls,
scheduler_list.*
FROM scheduler_list
where  sched_date='".$date3."'
AND sched_business=".$_SESSION['id']."");
$todaysched1 = mysql_fetch_array($todaysched);

$load =mysql_query("SELECT 
sum(load_amount) as alls,
 loadbalance.*
FROM  loadbalance
where load_status=1 ");
$load1 = mysql_fetch_array($load);

$sales =mysql_query("SELECT 
sum(sales_amount) as alls,
sales.*
FROM sales  ");
$sales1 = mysql_fetch_array($sales);
//biz_id=".$_SESSION['id']."
?>



<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<!-- Tell the browser to be responsive to screen width -->
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="">
<!-- Favicon icon -->
<link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
<title>BIZ VILLAGE</title>
<!-- Custom CSS -->
<link href="assets/libs/chartist/dist/chartist.min.css" rel="stylesheet">
<!-- Custom CSS -->
<link href="dist/css/style.min.css" rel="stylesheet">

</head>

<body>

<div class="preloader">
<div class="lds-ripple">
<div class="lds-pos"></div>
<div class="lds-pos"></div>
</div>
</div>

<div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full" data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

<header class="topbar" data-navbarbg="skin5">
<nav class="navbar top-navbar navbar-expand-md navbar-dark">
<div class="navbar-header" data-logobg="skin5">

<a class="navbar-brand" href="index.php">
<!-- Logo icon -->


<span class="logo-text">
<H3 sTYle="letter-spacing:1px">Biz Village</H3>
</span>
</a>

<!-- This is for the sidebar toggle which is visible on mobile only -->
<a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
</div>

<div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">



</div>
</nav>
</header>

<aside class="left-sidebar" data-sidebarbg="skin6">
<!-- Sidebar scroll-->
<div class="scroll-sidebar">
<!-- Sidebar navigation-->
<nav class="sidebar-nav">
<ul id="sidebarnav">
<!-- User Profile-->
<li>
<!-- User Profile-->
<div class="user-profile d-flex no-block dropdown m-t-20">
<div class="user-pic"><img src="assets/images/users/1.jpg" alt="users" class="rounded-circle" width="40" /></div>
<div class="user-content hide-menu m-l-10">
<a href="javascript:void(0)" class="" id="Userdd" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
<h5 class="m-b-0 user-name font-medium" style="text-transform:capitalize;"><?=$_SESSION['name']?> <?=$_SESSION['usertype']?> <i class="fa fa-angle-down"></i></h5>
</a>



<div class="dropdown-menu dropdown-menu-right" aria-labelledby="Userdd">
<a class="dropdown-item" href="settings.php"><i class="ti-user m-r-5 m-l-5"></i> My Profile</a>
<a class="dropdown-item" href="logout.php"><i class="fa fa-power-off m-r-5 m-l-5"></i> Logout</a>
</div>
</div>
</div>
<!-- End User Profile-->
</li>
<li class="p-15 m-t-10"><a href="product-services.php" class="btn btn-block create-btn text-white no-block d-flex align-items-center">
<i class="fa fa-plus-square"></i> <span class="hide-menu m-l-5">Create New Product</span> </a></li>
<!-- User Profile-->
<li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="index.php" aria-expanded="false"><i class="mdi mdi-view-dashboard"></i><span class="hide-menu">Dashboard</span></a></li>
<li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="orders.php" aria-expanded="false"><i class="mdi mdi-cart"></i><span class="hide-menu"> Orders</span></a></li>
<li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="sales.php" aria-expanded="false"><i class="mdi mdi-cash-usd"></i><span class="hide-menu">Sales</span></a></li>
<li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="load.php" aria-expanded="false"><i class="mdi mdi-cast-connected"></i><span class="hide-menu">SMS settings</span></a></li>
<li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="schedule-marketing.php" aria-expanded="false"><i class="mdi mdi-account-network"></i><span class="hide-menu">Schedules & marketing</span></a></li>
<li class="text-center p-40 upgrade-btn">
<a href="#" class="btn btn-block btn-danger text-white" target="_blank" style="display:none">Upgrade to Pro</a>
</li>
</ul>

</nav>
<!-- End Sidebar navigation -->
</div>
<!-- End Sidebar scroll-->
</aside>
<div class="page-wrapper">

<div class="page-breadcrumb">
<div class="row align-items-center">
<div class="col-5">
<h4 class="page-title">Sent Items</h4>
<div class="d-flex align-items-center">
<nav aria-label="breadcrumb">
<ol class="breadcrumb">
<li class="breadcrumb-item"><a href="#">Home</a></li>
<li class="breadcrumb-item active" aria-current="page">Sent</li>
</ol>
</nav>
</div>
</div>

</div>
</div>

<div class="container-fluid">

<div class="row">


<div class="tabset" style="margin-bottom:8px;float:right">
  <!-- Tab 1 -->
<a href="load.php" style="background:white;padding:10px;margin-left:10px;color:black">SMS Load  </a>
<a href="sms.php" style="background:white;padding:10px;margin-left:5px;color:blue;font-weight:bold;text-decoration:underline;border:1px solid lightgray;"> Sent Items </a>
<a href="schedule-marketing.php" style="background:white;padding:10px;margin-left:5px;color:black"> Market Old Customers </a>  
</div>
<div style="clear:both;height:10px"></div>



<div class="col-12">
<div class="card">
<div class="card-body">
<!-- title -->
<div class="d-md-flex align-items-center">

<div class="ml-auto">
<div class="dl">

</div>
</div>
</div>
<!-- title -->
</div>
<div class="table-responsive">
<table class="table v-middle">
<thead>
<tr class="bg-light">
<th class="border-top-0">#</th>
<th class="border-top-0">Name</th>
<th class="border-top-0">Mobile</th>
<th class="border-top-0">Text Message</th>
<th class="border-top-0">Status</th>

</tr>
</thead>
<tbody>
<?php

$count=1;
$schedule =mysql_query("SELECT * FROM sms_history 
LEFT JOIN user_reg 
ON sms_history.sms_user = user_reg.u_id
where biz_id=".$_SESSION['id']." 
order by sms_id desc  ");

while($row=mysql_fetch_array($schedule)){
if ($row['sms_status']==0){ //pending
$color='warning';
$text='Pending';	
} 	

if ($row['sms_status']==1){ //pending
$color='danger';
$text='Failed';	
} 	


if ($row['sms_status']==2){ //pending
$color='success';
$text='Sent';	
} 	
	
echo'<tr>
<td>'.$count.'</td>
<td><h4 class="m-b-0 font-12">'.$row['u_name'].'</h4></td>
<td>'.$row['u_mobile'].'</td>
<td>'.$row['sms_message'].'</td>
<td><label class="label label-'.$color.'">'.$text.'</label></td>
</tr>';
$count++;
}
?>


</tbody>


</table>
</div>
</div>
</div>














</div>
</div>
<footer class="footer text-center" STyle="font-size:1px">
All Rights Reserved by Xtreme Admin. Designed and Developed by <a href="https://wrappixel.com">WrapPixel</a>.
</footer>

</div>

</div>

<script src="assets/libs/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap tether Core JavaScript -->
<script src="assets/libs/popper.js/dist/umd/popper.min.js"></script>
<script src="assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="dist/js/app-style-switcher.js"></script>
<!--Wave Effects -->
<script src="dist/js/waves.js"></script>
<!--Menu sidebar -->
<script src="dist/js/sidebarmenu.js"></script>
<!--Custom JavaScript -->
<script src="dist/js/custom.js"></script>

</body>

</html>