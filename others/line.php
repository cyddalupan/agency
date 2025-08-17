<?php include'review.php';?>	
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
<link href="../assets/styles/admin/bootstrap.min.css" rel="stylesheet" />
<link href="../assets/styles/admin/font-awesome.min.css" rel="stylesheet" />

</head>


<!-- /Head -->
<!-- Body -->
<body style="background:#F5F5F5">

<div class="col-md-14 col-sm-14" style="margin-top:-1px">
<div class="row">
<div class="col-md-12">
  <form action="" method="post">
  
<div class="clients-page">

<div class="col-sm-2 pull-right" align="center">
</div>


<div class="row" style="margin:30px">
<div class="col-md-12"  style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC;border:1px solid #DCDCDC;width:140%">


<?php
include'db.php';

if(isset($_POST['updatedata'])){

foreach ($_POST['applicant_id'] as $delall) {
 
$insert1=mysql_query("UPDATE applicant
SET 
applicant_status =1
WHERE applicant_id=".$delall."");

 }
 
 echo '<div style="width:100%;padding:5px;color:red;">';			
echo " Successfully Cancelled";
echo "</div>";

}



$app = mysql_query("SELECT * FROM applicant
LEFT JOIN applicant_passport
ON applicant.applicant_id = applicant_passport.passport_applicant
LEFT JOIN  statuses
ON applicant.applicant_status = statuses.number

where
applicant_status IN (5)

order by applicant_status ASC");
?>



<form action="" method="post">


<?php
if($_GET['id']==0) { $stat="available";}
if($_GET['id']==5) { $stat="Line Up";}
if($_GET['id']==2) { $stat="Reserved";}
if($_GET['id']==4) { $stat="On Process";}
if($_GET['id']==12){ $stat="For Booking";}
if($_GET['id']==9) {$stat="Deployed";}
if($_GET['id']==15) { $stat="Passporting";}
?>

<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h1 style="color:black"><span>CV Sent </span></h1>
<!-- para -->
<?php
include'menuchange.php';
include'menu.php';
?>
<ul id="menu">
<li><a  href="dashboard.php"><?=$dashboard?></a></li>
<li><a  href="preselected.php"  ><?=$available?></a></li>
<li><a href="passporting.php"><?=$passporting?></a></li>
<li><a class="active"  href="line.php"> <?=$cv?></a></li>
<li><a href="med.php"><?=$medical?></a></li>
<li><a   href="all-app.php"><?=$selected?></a></li>
<li><a   href="tesdanew.php"><?=$tesda?></a></li>
<li><a   href="musaned.php"><?=$musaned?></a></li>
<li><a   href="owwanew.php"><?=$owwa?></a></li>
<li><a   href="stampnew.php"><?=$visamenu?></a></li>
<li><a   href="oecnew.php"><?=$oecmenu?></a></li>
<li><a href="booking.php"><?=$bookingmenu?></a></li>
<li><a href="deployednew.php"><?=$deployed?></a></li>
<li><a href="repatnew.php"><?=$repat?></a></li>
<li><a href="atoa.php"><?=$atoa?></a></li>
<li><a href="backouts.php"><?=$backoutmenu?></a></li>
</ul>
<div style="clear:both;height:10px"></div>

<div style="clear:both;height:10px"></div>
</div> 

<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">
<div class="table-responsive">

<div style="padding:50px;background:blue;margin:20px;float:left;width:400px;text-align:center">
<a href="lineupdh.php" style="color:WHITE;font-size:50px;">HOUSEHOLD</a></div>

<div style="padding:50px;background:orange;margin:20px;float:left;width:400px;text-align:center">
<a href="lineupskilled.php" style="color:WHITE;font-size:50px;">SKILLED</a></div>



  </form>
<div style="clear:both;height:40px"></div>
</div>

</div>
</div>

</div>
</div>
</div>


</div>
</div>
</div>
</div>



</body>
<!--  /Body -->
</html>

<script src="assets/js/bootstrap.min.js"></script>


