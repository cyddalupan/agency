<?php
session_start();
include'inc/functions.php';
include'tracking.php';
include'styles.php';
	

if(isset($_POST['adddme']))
{

$app = mysql_query("UPDATE employer
SET agree_ftw=".$_POST['agree_ftw'].", agree_visa=".$_POST['agree_visa'].", agree_deployed=".$_POST['agree_deployed']."
, agree_sent=".$_POST['agree_sent']."
, agree_before=".$_POST['agree_before']."
, agree_driver1=".$_POST['agree_driver1']."
, agree_driver2=".$_POST['agree_driver2']."
, agree_direct1=".$_POST['agree_direct1']."
, agree_direct2=".$_POST['agree_direct2']."
, agree_driver3=".$_POST['agree_driver3']."
, agree_direct3=".$_POST['agree_direct3']."
, numberdays=".$_POST['numberdays']."
WHERE employer_id=".$_POST['employer_id']." ");

echo"Updated..";

}


$app = mysql_query("SELECT * FROM employer where employer_id=".$_GET['emp_id']." ");
$row=mysql_fetch_array($app);




?>

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
<style>
#wrapper{
border:0px solid black;
width:90%;
min-height:630px;
height:!important;
margin: 0 auto;	
padding:10px;
font-size:12px;
font-family:calibri;
}
.table-fixed td,th{
font-size:13px;
padding:2px;
}


</style>

<body style="background:white">
<div id="wrapper">

<div>
<h1 style="font-size:20px;red:black;text-align:left;color:black">FRA  Name: <?=$row['employer_name']?></h1>

</div>
<a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a>

<!-- page content -->
<div class="right_col" role="main">
<div class="">


<div class="clearfix"></div>

<div class="row">
<div class="col-md-12">
<div class="x_panel">

<div class="x_content">

<section class="content invoice">



</div>

</BR>

<!-- Table row -->
<div class="row">
<div class="col-xs-12 ">




<div class="row" >
<div class="col-md-12 col-xs-12" >
<div class="x_panel" style="background:#F5F5F5">
<div class="x_content">




<h3>REGULAR AGREEMENT FRA</h3>

<form class="form-horizontal form-label-left input_mask" method="post" action="">	

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">CV Sent</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agree_sent" step="any"  required value="<?=$row['agree_sent']?>">
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agree_VISA</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agree_ftw" step="any"  required value="<?=$row['agree_ftw']?>">
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agree_contract</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agree_visa" step="any"  required value="<?=$row['agree_visa']?>">
</div>
</div>




<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">before deployment</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agree_before" step="any"  required value="<?=$row['agree_before']?>">
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agree_deployed</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agree_deployed" step="any"  required value="<?=$row['agree_deployed']?>">
</div>
</div>

<div style="clear:both"></div>	
<h3>FOR DRIVER</h3>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agree_visa</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agree_driver3" step="any"  required value="<?=$row['agree_driver3']?>">
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agree_contract</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agree_driver1" step="any"  required value="<?=$row['agree_driver1']?>">
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agree_deployed</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agree_driver2" step="any"  required value="<?=$row['agree_driver2']?>">
</div>
</div>





<div style="clear:both"></div>	
<h3>FOR DIRECT HIRE</h3>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agree_visa</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agree_direct3" step="any"  required value="<?=$row['agree_direct3']?>">
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agree_contract</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agree_direct1" step="any"  required value="<?=$row['agree_direct1']?>">
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">agree_deployed</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="agree_direct2" step="any"  required value="<?=$row['agree_direct2']?>">
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Billed Days after Deployed</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="numberdays"   required value="<?=$row['numberdays']?>">
</div>
</div>


<div class="form-group">
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="hidden" class="form-control" name="employer_id" value="<?=$_GET['emp_id']?>">
<button type="submit" class="btn btn-danger pull-right"  name="adddme">Save</button>
</div>
</div>
</form>	













</div>
</div>
</div>  
<div style="clear:both"></div>			
</div>			









</div>
</div>
<!-- /.col -->
</div>
<!-- /.row -->


</section>
</div>
</div>
</div>
</div>
</div>
</div>
<!-- /page content -->


</div>

</body>