<?php
session_start();
if(!isset($_SESSION['staff'])){
header("location:../index.php");
}

?>
<?php 
include'tracking.php';
include'inc/functions.php';
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>POWMADCI</title>
<link rel="stylesheet" type="text/css" href="css/center.css" />

<link rel="stylesheet" type="text/css" href="css/blue.css" />
<?php include'scripts/pop_up.php';?>
<?php include'scripts/tab.php';?>
<?php include'inc/pagination.php';?>


</head>

<body>
<!-- WRAPPER START -->
<div class="container_16" id="wrapper">	

  	<!--LOGO-->
	<div class="grid_8" id="logo">POWMADCI
	<style>
	#tets{margin-top:-35px;font-size:20px;e}
	#tets1{margin-top:-60px;font-size:20px;e}
	#tets a{color:pink}
	#tets1 a{color:red}
	</style>
	
	</div>
    <div class="grid_8">
<!-- USER TOOLS START -->
      <div id="user_tools"><span><a href="#"></a> Welcome <a href="index.php">
	  <?php echo"".$_SESSION['staff'];?></a>  |    <a href="changepassword.php">Change Password</a>| 
	  <a href="../logout.php">Logout</a></span></div>
    </div>
<!-- USER TOOLS END -->    
<div class="grid_16" id="header">
<!-- MENU START -->
<?php
$currentFile = $_SERVER["PHP_SELF"];
$parts = Explode('/', $currentFile);
$pg=$parts[count($parts) - 1];
$b="";
$a="";

if($pg == "patients.php"){
	$b="current";
}else{
$a="current";
}
?>
<div id="menu">
	<ul class="group" id="menu_group_main">
		<li class="item first" id="one"><a href="dashboard.php" class="main <?=$a?>"><span class="outer"><span class="inner dashboard">Dashboard</span></span></a></li>
        <li class="item middle" id="two"><a href="patients.php" class="main <?=$b?>"><span class="outer"><span class="inner content">Patients</span></span></a></li>
			<li class="item middle" id="eight"><a href="reports.php" ><span class="outer"><span class="inner reports png">Daily Reports</span></span></a></li>
        <li class="item middle" id="five"><a href="reviews.php" class="main"><span class="outer"><span class="inner users">Daily Patient Reviews</span></span></a></li>           
		
	<li class="item last" id="eight"><a href="doh.php" ><span class="outer"><span class="inner reports png">DOH Reports</span></span></a></li>
	
    </ul>
</div>
<!-- MENU END -->
</div>


<!--

<div ng-app="myApp" >
    <ng-include src=" baseURL + 'chatcat.html'" ng-controller="AppController"></ng-include>
</div>

<script type="text/javascript">

        var CC_OPTIONS = {

            // Users can create public chat rooms?
            // If this is true users will be able to setup new
            // public rooms
            usersCanCreatePublicRooms: false,

            // Allow anonymous login?
            anonymousLoginEnabled: true,

            // Enable social login - please email us to get your domain whitelisted
            socialLoginEnabled: true

        }

    var ccProtocol=(("https:"==document.location.protocol)?"https://":"http://");
    document.write(decodeURI("%3Clink rel='stylesheet' href='"+ccProtocol+"chatcat.firebaseapp.com/css/_/cc_styles.min.css' %3E%3C/link%3E"));
    document.write(decodeURI("%3Cscript src='"+ccProtocol+"chatcat.firebaseapp.com/js/dist.min.js' type='text/javascript'%3E%3C/script%3E"));
    document.write(decodeURI("%3Cscript src='"+ccProtocol+"chatcat.firebaseapp.com/js/all.min.js' type='text/javascript'%3E%3C/script%3E"));
</script> -->