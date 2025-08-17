<?php include'inc/header2.php';
include'tracking.php';
include'inc/col.php';
include'styles.php';

// delete liqfra
if(isset($_POST['delete_ordersfra'])){
foreach ($_POST['delall'] as $delall) {
mysql_query("delete  FROM liq_fra
WHERE fra_id = ".$delall)


or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted.</p>";
}



if(isset($_POST['delete'])){
$app11 = mysql_query("delete  FROM email_address6 where tr_id=".$_GET['trid']." ");
echo'<h2>deleted</h2>';
}


if(isset($_POST['approved'])){
echo'<h2>test</h2>';
}

$app = mysql_query("SELECT * FROM  email_address6 where tr_id=".$_GET['trid']." ");
$row=mysql_fetch_array($app);


$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$row['app_id']." ");
$rowp=mysql_fetch_array($applicant);

$emp = mysql_query("SELECT * FROM employer where employer_id=".$rowp['applicant_employer']." ");
$emprow=mysql_fetch_array($emp);

$bank = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$row['collect_bank_id']." ");
$bank1=mysql_fetch_array($bank);


if($row["collect_status"]==0){$request='Pending';$idrequest=0;$rcolor='black';}
if($row["collect_status"]==1){$request='Received';$idrequest=1;$rcolor='green';}
if($row["collect_status"]==2){$request='Return Maker';$idrequest=2;$rcolor='red';}

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);



?>

<body class="nav-md" style="background:white">



<!-- page content -->
<div class="right_col" role="main">
<div class="">


<div class="clearfix"></div>

<div class="row" style="width:90%;margin:0 auto;margin-top:20px;margin-bottom:20px">
<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">

<div class="x_content">






<div class="col-md-12 col-sm-12 col-xs-12"> 

 <?php if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){ ?>     
<form class="form-horizontal form-label-left input_mask" method="post" action="">	
<a href="delete_methode_link" onclick="return confirm('Are you sure you want to Remove?');">
<button type="submit" class="btn btn-danger pull-left"  name="delete" >Delete Transaction</button></a>
 <?php } ?>
</form>		

</DIV>

<DIV style="clear:both;height:30px"></DIV>
<?php $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>
<a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a>

<table id="customers">
<tr>
<td><b>Code # :</b>  000<?=$row["tr_id"]?></td>
<td><b>Ref # :</b>  <?=$row["or_num"]?></td>
<td><b>FRA Name :  </b><?=$emprow["employer_name"]?></td>
<td><b>Applicant Name:  </b><?=$rowp["applicant_first"]?> <?=$rowp["applicant_last"]?></td>
<td><b>Status : </b> <i style="color:<?=$rcolor?>"><?=$request?></i></td>
</tr>
</table>



<div class="" role="tabpanel" data-example-id="togglable-tabs">
<ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
<li role="presentation"><a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">
Transaction </a>
</li>


<li role="presentation"  class="active"><a href="#tab_content2" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Liquidation Details</a>
</li>


</ul>
<div id="myTabContent" class="tab-content">
<div role="tabpanel" class="tab-pane fade" id="tab_content1" aria-labelledby="home-tab">



<form class="form-horizontal form-label-left input_mask" method="post" action="">	

<?php
$emp = mysql_query("SELECT * FROM employer where employer_id=".$row['emp_id']." ");
$emprow=mysql_fetch_array($emp);
?>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<?php
echo'<select class="form-control" name="emp_id">';
echo'<option value="'.$row["emp_id"].'">  '.$emprow["employer_name"].'</option>';
echo'<option value="">  --</option>';
$emp1 = mysql_query("SELECT * FROM employer ORDER BY employer_name asc");
while($emp111=mysql_fetch_array($emp1))
{
echo'<option value="'.$emp111["employer_id"].'">  '.$emp111["employer_name"].'</option>';
}
ECHO'</select>';
?>
</div>
</div>




<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" name="collect_status" required>
<option value="<?=$idrequest?>"><?=$request?></option>
<option value="0">Pending</option>
<option value="1">Received</option>
<option value="2">Returnt to Maker</option>
</select>
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Transaction Date</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="date" class="form-control" name="date" value="<?=$row['date']?>" >
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Applicant</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control"   name="agent_id" >
<option value="<?=$_GET['apid']?>"><?=$_GET['name']?> </option>

</select>
</div>
</div>



<div class="form-group"  >
<label class="control-label col-md-3 col-sm-3 col-xs-12">REF #</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="or_num" value="<?=$row['or_num']?>" >
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="amount" step="any"  value="<?=$row['amount']?>" >
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Deduction</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="deduction" step="any"  value="<?=$row['deduction']?>" >
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Bank Charge</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="bankcharge" step="any"  value="<?=$row['bankcharge']?>" >
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Currency</label>
<div class="col-md-6 col-sm-6 col-xs-12">
<select class="form-control" required  name="currency">
<option><?=$row['currency']?></option>
<option>PHP</option>
<option>USD</option>

</select>
</div>
</div>


<h3 style="color:blue;text-align:center">Payment Information</h3>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Bank Accounts (Deposit)</label>
<div class="col-md-9 col-sm-9 col-xs-12">

<?php
echo'<select class="form-control" name="collect_bank_id"  required>';
echo'<option value="'.$row["collect_bank_id"].'"> '.$bank1["bank_name"].' - Account #: '.$bank1["bank_num"].' ('.$bank1["bank_currency"].')</option>';
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
<label class="control-label col-md-3 col-sm-3 col-xs-12">Type of Payment</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" name="type_payment" required>
<option><?=$row['type_payment']?></option>
<option>Full Payment</option>
<option>Cash Advance</option>
<option>Partial</option>
<option>Others</option>
</select>
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Mode of Payment</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<select class="form-control" name="method" required>
<option><?=$row['method']?></option>
<option>Fund Transfer</option>
<option>Cash</option>
<option>Cheque</option>
<option>Others</option>
<option>Credit Card</option>
<option>Direct Debit</option>
</select>
</div>
</div>


 





<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Description</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<textarea name="particular" class="col-md-9 col-sm-9 col-xs-12"><?=$row['particular']?></textarea>	
</div>
</div>




<div class="form-group">
<div class="col-md-12 col-sm-9 col-xs-12">	
<input type="hidden" class="form-control" name="tr_id" value="<?=$_GET['trid']?>">
<input type="hidden" class="form-control" name="user" value="<?=$_SESSION['user_fullname']?>">

<button type="submit" class="btn btn-info pull-right"  name="update_profile">Save</button>
</div>
</div>


</form>		


</div>



<div role="tabpanel" class="tab-pane fade  active in" id="tab_content2" aria-labelledby="profile-tab2">
<?php include'form/liqfra.php';?>
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





</body>
</html>