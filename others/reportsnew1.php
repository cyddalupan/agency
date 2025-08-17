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


<!--Beyond styles-->
<link id="beyond-link" href="../assets/styles/admin/beyond.min.css" rel="stylesheet" type="text/css" />

</head>
<!-- /Head -->
<!-- Body -->
<body style="background:white">

<div class="col-md-14 col-sm-14" style="margin-top:-1px">
<div class="row">
<div class="col-md-12">

<div class="clients-page">

<div class="row" style="margin:30px">
<div class="col-md-12"  style="floar:left;background:white;padding:10px;min-height:150px;border:1px solid #DCDCDC;border:1px solid #DCDCDC;width:100%">


<?php
if($_GET['id']==0) { $stat="Medical Under Processs";}
if($_GET['id']==5) { $stat="Line Up";}
if($_GET['id']==2) { $stat="Reserved";}
if($_GET['id']==4) { $stat="Selected";}
if($_GET['id']==12){ $stat="For Booking";}
if($_GET['id']==9) {$stat="Deployed";}
if($_GET['id']==15) { $stat="Passporting";}
?>

<div class="hero">
<!-- Title. Don't forget the <span> tag -->
<h3 STYLE="margin-left:40px"><span>  Medical Status</span></h3>
<!-- para -->
<?php include'menu1.php';
include'db.php';
?>




<ul id="menu">
<li><a  class="active" href="medical_view.php">Medical</a></li>
<li><a    href="tesda_view.php">TESDA</a></li>
</ul>

<div style="clear:both;height:20px"></div>




</div> 

<div class="row">
<div class="col-md-12"  style=" padding-bottom:20px;">
	<div  class="pmd-card pmd-z-depth pmd-card-custom-view">













<form method="post" class="form" id="form1" action="<?=$myreport?>medicalreports.php?user=<?=$_GET['user']?>" target="_blank" style="padding:10px;border:1px solid lightgray;margin:2px;WIDTH:600PX;float:left;margin:10px">
<H2>MEDICAL Reports</H2>
<div class="row">

<div class="row">
<div class="col-sm-3">
Select Result:
</div>
<div class="col-sm-5">


<div class="input-group">
<select name="status_medical" class="form-control" required>
<option value="99999">--All--</option>
<option>FOR MEDICAL</option>
<option>FIT TO WORK</option>
<option>PENDING</option>
<option>TREATEMENT</option>
<option>UNFIT</option>
</select>

</div>			    
</div>
</div>



</br></br></br>
<div class="col-sm-3">
Date Medical From/To<small><em>(optional)</em></small>:
</div>
<div class="col-sm-4">
<div class="input-group">
<input name="start" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="From" value="" REQUIRED>

</div>			    
</div>
<div class="col-sm-4">
<div class="input-group">
<input name="end" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="To" value="" REQUIRED>

</div>
</div>

</div>


</br>
<div class="modal-footer">
    <button class="btn btn-primary" type="submit" >Generate</button>
</div>
</br></br>
</form>









<form method="post" class="form" id="form3" action="<?=$myreport?>tesdareports.php?user=<?=$_GET['user']?>" target="_blank" style="padding:10px;border:1px solid lightgray;margin:2px;WIDTH:600PX;float:left;margin:10px">
<H2>Tesda Reports</H2>


<div class="row">

<div class="col-sm-3">
Date Tesda From/To<small><em>(optional)</em></small>:
</div>
<div class="col-sm-4">
<div class="input-group">
<input name="start" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="From" value="" REquired>

</div>			    
</div>
<div class="col-sm-4">
<div class="input-group">
<input name="end" type="date" data-date-format="yyyy-mm-dd" class="form-control date-picker" placeholder="To" value="" REquired>

</div>
</div>

</div>



<div class="modal-footer">
    <button class="btn btn-primary" type="submit" >Generate</button>
</div>
</br></br>
</form>














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

<script src="assets/js/jquery-1.12.2.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/propeller.min.js"></script>


<!-- Datatable js -->
<script type="text/javascript" language="javascript" src="jsdatatables.js"></script>



