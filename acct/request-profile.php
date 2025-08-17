<?php include'inc/header1.php';
include'tracking.php';
include'inc/expenses.php';
if(isset($_POST['delete'])){
$app11 = mysql_query("delete  FROM email_address where e_id=".$_GET['e_id']." ");
echo'<h2>deleted</h2>';
}

$app = mysql_query("SELECT * FROM email_address where e_id=".$_GET['e_id']." ");
$row=mysql_fetch_array($app);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);

$staff = mysql_query("SELECT * FROM user where user_id=".$row['staff_id']." ");
$staffr=mysql_fetch_array($staff);

$sup = mysql_query("SELECT * FROM  suppliers where sup_id=".$row['sup_id']." ");
$sup1=mysql_fetch_array($sup);


if(isset($_POST['delete_orders'])){
foreach ($_POST['delall'] as $delall) {
mysql_query("UPDATE e_liq
SET l_status = 1 
WHERE l_id = ".$delall)
or die ("cannot Update data");
}
echo"<p style='color:Red'>Succesfully Deleted</p>";
}

?>

<style>
#customers {
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 5px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #4CAF50;
  color: white;
}
</style>


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

<table  cellspacing="0" id="customers">
<tr>
<td>Cient/Suppliers :   <b><?=$sup1["name"]?> </td>
<td>Code # : <?=$sup1["codes"]?></td>
<td>Bank Name : <?=$sup1["bank_name"]?></td>
<td>Bank Account : <?=$sup1["bank_number"]?></td>
<td>Agreement : <?=$sup1["bank_remarks"]?></td>
<td><a href="viewtransaction.php?client=<?=$sup1['sup_id']?>" onclick="popUp(this.href,'console',700,1200);return false;" target="newWin">
<button type="submit" class="btn btn-warning btn-xs" style="">View Accounts</button></b></a></td>
</tr>

<tr>
<td>Agent:   <?=$row1["agent_last"]?>, <b><?=$row1["agent_first"]?></td>
<td>Code # : <?=$row1["agent_string_id"]?></td>
<td>agent_contacts : <?=$row1["agent_contacts"]?></td>
<td>agent_email : <?=$row1["agent_email"]?></td>
<td>Agreement : <?=$row1["agent_remarks"]?></td>
<td><a href="profile.php?agent=<?=$row1['agent_id']?>" onclick="popUp(this.href,'console',700,1200);return false;" target="newWin">
<button type="submit" class="btn btn-warning btn-xs" style="">View Accounts</button></b></a></td>
</tr>

</table>



<DIV style="clear:both;height:30px"></DIV>


<div class="" role="tabpanel" data-example-id="togglable-tabs">
<ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
<li role="presentation" class="active"><a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Expeses Info</a>
</li>
<li role="presentation" ><a href="#tab_content2" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Liquidation Details</a>
</li>

<li role="presentation" class=""><a href="#tab_content3" role="tab" id="profile-tab3" data-toggle="tab" aria-expanded="false">Uplaod Receipt/Docs </a>
</li>

</ul>
<div id="myTabContent" class="tab-content">
<div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">

<form class="form-horizontal form-label-left input_mask" method="post" action="">	

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">DATE APPLIED</label>
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="date" class="form-control" name="date_ad" value="<?=$row['date_ad']?>" readonly>
</div>
</div>

<div class="form-group"  style="display:none">
<label class="control-label col-md-3 col-sm-3 col-xs-12">REF #</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="ref"  value="<?=$row['ref']?>" readonly>
</div>
</div>




<div class="form-group" style="display:none">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Applicant</label>
<div class="col-md-9 col-sm-9 col-xs-12">
<?php
echo'<select class="form-control" name="app_id" required readonly>
<option> '.$row["app_id"].'</option>
<option value="9999999">Others</option>
';

$applicant = mysql_query("SELECT * FROM applicant ORDER BY applicant_date_applied asc");
while($rows1=mysql_fetch_array($applicant))
{
echo'<option value="'.$rows1["applicant_id"].'">'.$rows1["applicant_last"].'   '.$rows1["applicant_first"].' -'.$rows1["applicant_id"].'</option>';
}
ECHO'</select>';
?>

</div>
</div>



<div class="form-group" style="">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Agent </label>
<div class="col-md-4 col-sm-6 col-xs-12">
<select class="form-control" required  name="agent_id" required readonly>
<option value="<?=$row1['agent_id']?>"><?=$row1['agent_first']?>  <?=$row1['agent_last']?> </option>
<?php
$usertype = mysql_query("SELECT * FROM recruitment_agent ORDER BY agent_first asc");

while($rows=mysql_fetch_array($usertype))
{
echo'<option value="'.$rows["agent_id"].'">'.$rows["agent_first"].'  '.$rows["agent_last"].'</option>';
}
?>
</select>
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Requesting Party </label>
<div class="col-md-4 col-sm-6 col-xs-12">
<select class="form-control" required  name="staff_id" required readonly>
<option value="<?=$staffr['user_id']?>"> <?=$staffr['user_fullname']?> </option>
<option value=0>N/A</option>
<?php
$usertype1 = mysql_query("SELECT * FROM user 
where user_type NOT IN(12,5,17)
ORDER BY user_fullname asc");
while($rows1=mysql_fetch_array($usertype1))
{
echo'<option value="'.$rows1["user_id"].'">  '.$rows1["user_fullname"].'</option>';
}
ECHO'</select>';
?>
</div>
</div>




<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Type Payment</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<select class="form-control" name="method" required readonly>
<option><?=$row['method']?></option>
<option>Fund Transfer</option>
<option>Cash</option>
<option>Cheque</option>
<option>Credit Card</option>
<option>Direct Debit</option>
</select>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Type of Expenses</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<?php echo'
<select class="form-control" name="account" required readonly>
<option>'.$row["account"].'</option>';
$set_ = mysql_query("SELECT * FROM set_ ORDER BY account asc");
while($setr=mysql_fetch_array($set_))
{
echo'<option value="'.$setr["account"].'">'.$setr["account"].'</option>';
}
echo'</select>';
?>
</div>
</div>	  

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Currency</label>
<div class="col-md-4 col-sm-9 col-xs-12" required>
<select class="form-control" name="currency"  readonly>
<option><?=$row['currency']?></option>
<option>PHP</option>
<option>USD</option>
</select>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="amount" required step="any"  value="<?=$row['amount']?>" readonly>
</div>
</div>



<div class="form-group"  style="">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Description</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="description"  value="<?=$row['description']?>" readonly>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Payment to</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="payment_to" required   value="<?=$row['payment_to']?>" readonly>
</div>
</div>



</form>	


</div>




<div role="tabpanel" class="tab-pane fade" id="tab_content2" aria-labelledby="profile-tab2">
<table  cellspacing="0" id="customers">
<thead>
<tr>
<th STYLE="width:2%"></th>

<th STYLE="width:2%">Ref #</th>
<th STYLE="width:3%">DATE</th>
<th STYLE="width:5%">Applicant </th>
<th STYLE="width:5%">Req.By</th>
<th STYLE="width:5%">Type</th>
<th STYLE="width:5%">Description</th>
<th STYLE="width:5%">Currency</th>
<th STYLE="width:5%">Amount</th>
</tr>
</thead>
<tbody>
<?php	



$app = mysql_query("SELECT * FROM e_liq where l_status=0
AND l_e_d=".$_GET['e_id']."
order by l_id desc");

while($row=mysql_fetch_array($app))
{
    
$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$row['liq_app']." ");
$rowp=mysql_fetch_array($applicant);

$USER = mysql_query("SELECT * FROM  user where user_id=".$row['l_staff_id']." ");
$rowu=mysql_fetch_array($USER);

$total1=$row['l_amount']+$total1;
$amt = number_format($row["l_amount"], 2);


echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$row['l_id']."' ></td> ";
echo'<td>000'.$row["l_e_d"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row['l_date']));echo'</td>';
echo'<td>'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$rowu["user_fullname"].'</td>';
echo'<td>'.$row["l_type"].'</td>';
echo'<td>'.$row["l_remarks"].'</td>';
echo'<td>'.$row["l_currency"].'</td>';
echo'<td>'.$amt.'</td>';
echo'</tr>';
}
$tot1 = number_format($total1, 2);
?>

<tr>
<td  colspan="8"  style="font-weight:bold;text-align:right;font-size:14px;color:red">Total Liquidation:</td>
<td  style="font-weight:bold;text-align:left;font-size:14px;color:red"> <?=$tot1;?> </td>
</TR>

</tbody>
</table>
</div>












<div role="tabpanel" class="tab-pane fade" id="tab_content3" aria-labelledby="profile-tab2">
<?php
@set_time_limit(3600); //1 hour
@ini_set('max_execution_time', 3600); //1 hour
?>
  
    <div style="clear:both;height:10px"></div>


  <div style="clear:both;height:10px"></div>


 
 
<form action="" method="post">
 <table  cellspacing="0" id="customers">
<thead>
<tr>


<th STYLE="width:30%">Path</th>
</tr>
</thead>
<tbody>
<?php	

$app11111 = mysql_query("SELECT * FROM resibo where resibo_connect=".$_GET['e_id']."
order by resibo_id desc");

while($row111=mysql_fetch_array($app11111))
{



echo'<tr>';

echo'<td><a  href="'.$row111["resibo_path"].'" target="_blank"><img src="'.$row111["resibo_path"].'" style="width:300px"></a></td>';

echo'</tr>';
}
$tot1 = number_format($total1, 2);
?>



</tbody>
</table>
 
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