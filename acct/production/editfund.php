<?php include'inc/header1.php';
include'tracking.php';
include'inc/fund.php';

$app = mysql_query("SELECT * FROM fund where fund_id=".$_GET['fund']." ");
$row=mysql_fetch_array($app);



?>

<body class="nav-md" style="background:#F5F5F5">


<?php $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>


<!-- page content -->
<div class="right_col" role="main">
<div class="">


<div class="clearfix"></div>

<div class="row" style="width:90%;margin:0 auto;margin-top:20px;margin-bottom:20px">
<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">

<div class="x_content">

<a href="cashfund.php" style="color:Red;text-align:right">Back</a></br></br>
Date Request : <?php
echo''; echo date('Y-M-d', strtotime($row['fund_date']));echo'';

?>



<DIV style="clear:both;height:30px"></DIV>


<div class="" role="tabpanel" data-example-id="togglable-tabs">
<ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
<li role="presentation" class="active"><a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Expeses</a>
</li>



</ul>
<div id="myTabContent" class="tab-content">
<div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">

<form class="form-horizontal form-label-left input_mask" method="post" action="">	

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">DATE</label>
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="date" class="form-control" name="fund_date" value="<?=$row['fund_date']?>">
</div>
</div>




<div class="form-group" >
<label class="control-label col-md-3 col-sm-3 col-xs-12">Source Payment</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<?php
echo'<select class="form-control" name="sources" required>'; ?>
<option><?=$row['sources']?></option>
<?php
$source1 = mysql_query("SELECT * FROM source_payment ORDER BY s_name asc");
while($rowsource=mysql_fetch_array($source1))
{
echo'<option value="'.$rowsource["s_name"].'">  '.$rowsource["s_name"].'</option>';
}
ECHO'</select>';
?>
</div>
</div>









<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="fund_amount" required step="any"  value="<?=$row['fund_amount']?>">
</div>
</div>



<div class="form-group"  style="">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Description</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="fund_p"  value="<?=$row['fund_p']?>">
</div>
</div>


<div class="form-group">
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="hidden" class="form-control" name="fund_id" value="<?=$_GET['fund']?>">
<button type="submit" class="btn btn-danger pull-right"  name="update_profile_fund">Save</button>
</div>
</div>


</form>	


</div>




<div role="tabpanel" class="tab-pane fade" id="tab_statuss" aria-labelledby="profile-tab2">
<?php include'form/liq.php';?>
</div>



</div>
</div>


</div>
</div>
</div>
</div>
</div>
</div>
<!-- /page content -->

<!-- footer content -->



<!-- jQuery -->
<script src="../vendors/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- FastClick -->
<script src="../vendors/fastclick/lib/fastclick.js"></script>
<!-- NProgress -->
<script src="../vendors/nprogress/nprogress.js"></script>
<!-- morris.js -->
<script src="../vendors/raphael/raphael.min.js"></script>
<script src="../vendors/morris.js/morris.min.js"></script>
<!-- bootstrap-progressbar -->
<script src="../vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
<!-- bootstrap-daterangepicker -->
<script src="../vendors/moment/min/moment.min.js"></script>
<script src="../vendors/bootstrap-daterangepicker/daterangepicker.js"></script>

<!-- Custom Theme Scripts -->
<script src="../build/js/custom.min.js"></script>

</body>
</html>