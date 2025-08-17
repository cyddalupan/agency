<?php
session_start();
include'inc/functions.php';
include'tracking.php';
if(isset($_POST['delete'])){
$app11 = mysql_query("delete  FROM email_address4 where exp_id=".$_GET['exp']." ");
echo'<h2>deleted</h2>';
}

$app1 = mysql_query("SELECT * FROM email_address4 where exp_id=".$_GET['exp']." ");
$row1=mysql_fetch_array($app1);

$app = mysql_query("SELECT * FROM applicant where applicant_id=".$row1['app_id']." ");
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
<h1 style="font-size:25px;red:black;text-align:left;color:black">Applicant Name: <?=$row['applicant_first']?> <?=$row['applicant_last']?></h1>
<form class="form-horizontal form-label-left input_mask" method="post" action="">	
<button type="submit" class="btn btn-danger pull-right"  name="delete">delete</button>

</form>

</div>

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


<div class="x_panel" style="background:#DCDCDC">

<h2  style="color:red">View Transaction OF ONE APPLICANT</h2>


<div class="x_content">

<form class="" action="" method="post">

<div class="x_panel">

<div class="x_content">

<input type="hidden" class="form-control" id="inputSuccess3" name="agent_id" value="<?=$row['applicant_source']?>">
<input type="hidden" class="form-control" id="inputSuccess3" name="user" value="<?=$_SESSION['staff']?>">
<input type="hidden" class="form-control" id="inputSuccess3" name="exp_status" value="1">
<input type="hidden" class="form-control" id="inputSuccess3" name="exp_id" value="<?=$row1['exp_id']?>">

<input type="hidden" class="form-control" id="inputSuccess3" name="email_updated" value="<?php echo date('Y-m-d H:i:s');?>">



<div style="clear:both;height:10px"></div>



<div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
<label class="control-label col-md-3 col-sm-3 col-xs-5">Amount</label>

<input type="number" class="form-control" id="inputSuccess5"  name="receivable" step="any"  required  value="<?=$row1['receivable']?>">
</div>

<div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback">
<label class="control-label col-md-3 col-sm-3 col-xs-5">Other Charges</label>

<input type="number" class="form-control" id="inputSuccess5"  name="othercharge" step="any"  required  value="<?=$row1['othercharge']?>">
</div>





<div class="col-md-3 col-sm-3 col-xs-12 form-group has-feedback">
<label class="control-label col-md-3 col-sm-3 col-xs-5">Currency</label>

<select class="form-control" required name="currency">
<option><?=$row1['currency']?></option>
<option>PHP</option>
</select>
</div>

<div class="col-md-3 col-sm-3 col-xs-12 form-group has-feedback">
<label class="control-label col-md-3 col-sm-3 col-xs-5">Type</label>

<select class="form-control" required name="type_status">
<option><?=$row1['type_status']?></option>
<option>Agency</option>
<option>Agent</option>
</select>
</div>

<div style="clear:both;height:10px"></div>



<div class="col-md-3 col-sm-3 col-xs-12 form-group has-feedback">
<select class="form-control" required name="exp_type">
<option><?=$row1['exp_type']?></option>
</select>
</div>

<?php
if($row2["status_paid"]==0){$status1='Not Paid';}
if($row2["status_paid"]==1){$status1='Paid';}
?>

<div class="col-md-3 col-sm-3 col-xs-12 form-group has-feedback">
<select class="form-control" required name="exp_type">
<option value="status_paid"><?=$status1?></option>
<option value="0">Not Paid</option>
<option value="1">Paid</option>
</select>
</div>





<div class="form-group">

<label class="control-label col-md-3 col-sm-3 col-xs-5">Date Transction</label>
<div class="col-md-3 col-sm-3 col-xs-5">
<input type="date" class="form-control" name="date_transaction" value="<?=$row1['date_transaction']?>" required>
</div>
</div>
</br></br>



<div class="form-group"> 
<div class="clearfix"></div>
<div class="col-md-12 col-sm-6 col-xs-12 form-group has-feedback">
<input type="text" class="form-control" id="inputSuccess5" placeholder="Description" name="findings" value="<?=$row1['findings']?>" required>
</div>
</div>	

</br></br></br>







</div>
</div>


<div class="ln_solid"></div>
<div class="form-group">
<div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-9">

<button type="submit" class="btn btn-info"  name="update_unfit">UPDATE</button>
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