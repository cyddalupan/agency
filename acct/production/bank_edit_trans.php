<?php
session_start();
include'db.php';
include'tracking.php';
include'styles.php';
	



?>

<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
   
    <title>Accounting System</title>

    <!-- Bootstrap -->
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
	<!-- Custom Theme Style -->
    <link href="../build/css/custom.min.css" rel="stylesheet">
	<!-- Datatable js -->


  </head>

<body style="background:white">
<div id="wrapper">

<div>
<h1 style="font-size:20px;red:black;text-align:left;color:black">Name: <?=$row['applicant_first']?> <?=$row['applicant_last']?></h1>

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

<?php


if(isset($_POST['add_agree']))
{

$app = mysql_query("UPDATE cash_transaction
SET cash_bank_out=".$_POST['cash_bank_out']."
, cash_bank_in=".$_POST['cash_bank_in']."
, cash_amount=".$_POST['cash_amount']."
, cash_convertion=".$_POST['cash_convertion']."
, bank_remarks='".$_POST['bank_remarks']."'
, cash_date='".$_POST['cash_date']."'
WHERE cash_id=".$_GET['idme']." ");

echo"Updated..";

}


$app = mysql_query("SELECT * FROM cash_transaction where cash_id=".$_GET['idme']." ");
$row=mysql_fetch_array($app);



$bank_out = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$row['cash_bank_out']." ");
$bank_out1=mysql_fetch_array($bank_out);

$bank_in = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$row['cash_bank_in']." ");
$bank_in1=mysql_fetch_array($bank_in);


?>


<h3>Edit Information</h3>

<form class="form-horizontal form-label-left input_mask" method="post" action="">	

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">DATE (yyyy-mm-dd)</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="cash_date"  value="<?=$row['cash_date']?>">
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Cash OUT </label>
<div class="col-md-4 col-sm-6 col-xs-12">
<?php
echo'<select class="form-control" name="cash_bank_out"  required style="width:150px">';
echo'<option value="'.$row["cash_bank_out"].'">  '.$bank_out1["bank_name"].' </option>';
$bank = mysql_query("SELECT * FROM bank_accounts ORDER BY bank_name asc");
while($bank1=mysql_fetch_array($bank))
{
echo'<option value="'.$bank1["bank_id"].'">  '.$bank1["bank_name"].' - Account #: '.$bank1["bank_num"].' ('.$bank1["bank_currency"].') </option>';
}
ECHO'</select>';
?>
</div>
</div>




<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Cash IN </label>
<div class="col-md-4 col-sm-6 col-xs-12">
<?php
echo'<select class="form-control" name="cash_bank_in"  required style="width:150px">';
echo'<option value="'.$row["cash_bank_in"].'">  '.$bank_in1["bank_name"].' </option>';
$bank = mysql_query("SELECT * FROM bank_accounts ORDER BY bank_name asc");
while($bank1=mysql_fetch_array($bank))
{
echo'<option value="'.$bank1["bank_id"].'">  '.$bank1["bank_name"].' - Account #: '.$bank1["bank_num"].' ('.$bank1["bank_currency"].') </option>';
}
ECHO'</select>';
?>
</div>
</div>






<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">cash_amount</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="cash_amount" step="any"  required value="<?=$row['cash_amount']?>">
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Conversion rate if $</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="cash_convertion" step="any"  required value="<?=$row['cash_convertion']?>">
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">REMARKS </label>
<div class="col-md-4 col-sm-9 col-xs-12">
<textarea name="bank_remarks" STYLE="width:500px;height:200px"><?=$row['bank_remarks']?></textarea>
</div>
</div>





<div class="form-group">
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="hidden" class="form-control" name="agent_id" value="<?=$_GET['agent']?>">
<button type="submit" class="btn btn-danger pull-right"  name="add_agree">Save</button>
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