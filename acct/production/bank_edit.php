<?php include'inc/header1.php';
include'tracking.php';
include'inc/bank.php';


$app = mysql_query("SELECT * FROM bank_accounts where bank_id=".$_GET['accounts']." ");
$row=mysql_fetch_array($app);
?>
<script>
	function popUp(strURL,strType,strHeight,strWidth) {
var strOptions="";
if (strType=="console") strOptions="resizable,height="+strHeight+",width="+strWidth;
if (strType=="fixed") strOptions="status,height="+strHeight+",width="+strWidth;
if (strType=="elastic") strOptions="toolbar,menubar,scrollbars,resizable,location,height="+strHeight+",width="+strWidth;
window.open(strURL, 'newWin', strOptions);
}
</script>

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





<DIV style="clear:both;height:10px"></DIV>


<div class="" role="tabpanel" data-example-id="togglable-tabs">
<ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
<li role="presentation" class="active"><a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Expeses Info</a>
</li>



</ul>
<div id="myTabContent" class="tab-content">
<div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">
<form class="form-horizontal form-label-left input_mask" method="post" action="">	



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">NAME</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="bank_name"  value="<?=$row['bank_name']?>">
</div>
</div>



<div class="form-group"  style="">
<label class="control-label col-md-3 col-sm-3 col-xs-12">bank_num</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="bank_num"  value="<?=$row['bank_num']?>">
</div>
</div>




<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">bank_currency_ACCOUNT</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" name="bank_currency"  required >
<option><?=$row['bank_currency']?></option>
<option>PHP</option>
<option>USD</option>
</select>
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">bank_remarks</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="bank_remarks" value="<?=$row['bank_remarks']?>">
</div>
</div>

<?php
if($row["bank_user_type"]==0){$request='Bank Account';$rcolor='black';}
if($row["bank_user_type"]==1){$request='Personal Account';$rcolor='blue';}
if($row["bank_user_type"]==2){$request='Office Account';$rcolor='orange';}
?>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Type of Payment</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" name="bank_user_type" required>
<option value="<?=$row['bank_user_type']?>"><?=$request?></option>
<option value="0">Bank Account</option>
<option value="1">Personal Account</option>
<option value="2">Office Account</option>
</select>
</div>
</div>










<div class="form-group">
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="hidden" class="form-control" name="bank_id" value="<?=$_GET['accounts']?>">
<button type="submit" class="btn btn-danger pull-right"  name="update_profile">Save</button>
</div>
</div>


</form>	

</div>
</div>














<div role="tabpanel" class="tab-pane fade" id="tab_content3" aria-labelledby="profile-tab2">


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