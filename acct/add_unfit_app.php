<?php
session_start();
include'inc/functions.php';
include'tracking.php';
$app = mysql_query("SELECT * FROM applicant where applicant_id=".$_GET['appid']." ");
$row=mysql_fetch_array($app);

$CERT = mysql_query("SELECT * FROM applicant_certificate where certificate_applicant=".$_GET['appid']." ");
$certrow=mysql_fetch_array($CERT);

$REQ = mysql_query("SELECT * FROM applicant_requirement where requirement_applicant=".$_GET['appid']." ");
$reqrow=mysql_fetch_array($REQ);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['applicant_source']." ");
$row1=mysql_fetch_array($app1);



?>
<html lang="en">
<head>
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">
<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">


<title> <?=$row['applicant_last']?></title>

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
</head>
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
<h1 style="font-size:16px;color:black;text-align:left;padding-left:57px;font-weight:bold">AGENT/SOURCE: <?=$row1['agent_first']?> <?=$row1['agent_last']?></h1>
<h1 style="font-size:16px;color:black;text-align:left;padding-left:57px;font-weight:bold">APPLICANT: <?=$row['applicant_first']?> <?=$row['applicant_last']?></h1>

<p><?=$row['agent_conracts']?> </p>
</div>
<?PHP
if(isset($_POST['charge'])){
$app11 = mysql_query("update  email_address4 
set type_status='agent'
where 
app_id=".$_GET['appid']." ");
echo'<h2 style="color:Red">succefully update</h2>';
}
?>

<div class="col-md-12 col-sm-12 col-xs-12">      
<form class="form-horizontal form-label-left input_mask" method="post" action="">	
<button type="submit" class="btn btn-danger pull-right"  name="charge">CHARGE TO AGENT</button>
</form>		
</DIV>

<!-- page content -->
<div class="right_col" role="main">
<div class="">


<div class="clearfix"></div>

<div class="row" style="margin-left:100px">
<div class="col-md-11">
<div class="x_panel"   style="background:#D3D3D3">

<div class="x_content">


<section class="content invoice">



</div>

</BR>

<!-- Table row -->
<div class="row">
<div class="col-xs-12 table">



<div class="row">

<div class="col-md-11 col-xs-11">


<div class="x_panel">

<h2>Add New Transaction<small style="color;black"> Payment</small></h2>


<div class="x_content">

<form class="" action="" method="post">

<div class="x_panel">

<div class="x_content">

<input type="hidden" class="form-control" id="inputSuccess3" name="agent_id" value="<?=$row['applicant_source']?>">
<input type="hidden" class="form-control" id="inputSuccess3" name="app_id" value="<?=$_GET['appid']?>">
<input type="hidden" class="form-control" id="inputSuccess3" name="user" value="<?=$_SESSION['staff']?>">
<input type="hidden" class="form-control" id="inputSuccess3" name="exp_status" value="1">

<input type="hidden" class="form-control" id="inputSuccess3" name="email_created" value="<?php echo date('Y-m-d H:i:s');?>">


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


</br>
<div class="col-md-12 col-sm-6 col-xs-12 form-group has-feedback">
<input type="text" class="form-control" id="inputSuccess5" placeholder="Particular(s)" name="findings" >
</div>

</br></br></br>







</div>
</div>


<div class="ln_solid"></div>
<div class="form-group">
<div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-9">

<button type="submit" class="btn btn-INFO"  name="new_unfit">Sava Transaction</button>
</div>
</div>

</form>
</div>
</div>
</div>  



<div style="clear:both"></div>			

<h2 style="color:red">Applicant Expenses</h2>



<table class="table table-fixed"style="width:80%">
<thead>

<tr>
<th>#</th>
<th>Date</th>
<th style="width: 29%">Description</th>
<th style="text-align:right">Account Type</th>
<th style="text-align:right">Charge To</th>			
<th style="text-align:right">Currency</th>

<th style="text-align:right">Amount </th>
<th style="text-align:right">Status </th>
</tr>

</thead>
<tbody>
<?PHP
$app = mysql_query("SELECT * FROM email_address4 where  app_id=".$_GET['appid']."  AND currency='PHP'
and receivable!=0
order by app_id desc ");
while($row=mysql_fetch_array($app))
{
$total=$row['receivable']+$total;
if($row["status_paid"]==0){$status1='Not Paid';}
if($row["status_paid"]==1){$status1='Paid';}

echo'<tr>';
echo'<td>'.$row["agent_id"].'</td>';

echo'<td>'.$row["date_transaction"].'</td>';
echo'<td>'.$row["findings"].'</td>';
echo'<td style="text-align:right">'.$row["exp_type"].'</td>';
echo'<td style="text-align:right">'.$row["type_status"].'</td>';
echo'<td style="text-align:right">'.$row["currency"].'</td>';

echo'<td  style="text-align:right">'.$row["receivable"].'</td>';
echo'<td style="text-align:right;font-size:10px;color:Red">'.$status1.'</td>';
echo'</tr>';
}
?>	
<tr>
<td style="color:Red;text-align:right;font-size:18px" colspan="7"> <?php echo number_format($total,2);?></td>
</tr>	
</tbody>
</table>







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