<!DOCTYPE html>
<html lang="en" ng-app="myApp">
<!-- Head -->
<head>
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<title></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

</head>
<style>
body {background:#F5F5F5;
FONT-FAMILY:arial;
}

.box h3{
text-align:center;
position:relative;
top:10px;
FONT-SIZE:16PX;
}

.box p{
text-align:center;
position:relative;
top:-15px;
FONT-SIZE:35PX;
COLOR:gray;


}

.box {
width:22%;
height:120px;
background:#FFF;
margin:10px auto;
float:left;
}
/*==================================================
* Effect 1
* ===============================================*/
.effect1{
-webkit-box-shadow: 0 10px 6px -6px #777;
-moz-box-shadow: 0 10px 6px -6px #777;
box-shadow: 0 10px 6px -6px #777;
margin:10px;
}
</style>
<body>

<?php
include'db.php';
?>


<div class="col-md-12 col-sm-12" style="margin-top:-1px">
<div class="row" style="floar:left;">

<h2>Applicant Summary</h2>

<div class="col-md-3 col-sm-3 box effect1">
			<h3>Passporting</h3>
			<p>5</p>
			</div>
			
			<h3>For Release Passport</h3>
			<p>15</p>
			</div>
			
			<h3>Fit to Work</h3>
			<p>25</p>
			</div>


	<div style="clear:both; height:1px;"></div>
			<div class="col-md-3 col-sm-3 box effect1">
			<h3>For Contract Signing</h3>
			<p>5</p>
			</div>
			
			<div class="col-md-3 col-sm-3 box effect1">
			<h3>Visa Stamping</h3>
			<p>2</p>
			</div>
			
			<div class="col-md-3 col-sm-3 box effect1">
			<h3>OWWA Schedule</h3>
			<p>23</p>
			</div>
			
			<div class="col-md-3 col-sm-3 box effect1">
			<h3>VFS Schedule</h3>
			<p>23</p>
			</div>
			
			
  	<div style="clear:both; height:1px;"></div>
	
	
	
	
	
	
	
	
</div>
</div>
</body>
<!--  /Body -->
</html>

