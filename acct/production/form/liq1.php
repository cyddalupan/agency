

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/css/bootstrap-select.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/js/bootstrap-select.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>


<form class="form-horizontal form-label-left input_mask" method="post" action="">	

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">DATE TRANSACTION</label>
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="date" class="form-control" name="l_date" value="<?=$row['date_ad']?>" required>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Account Type</label>
<div class="col-md-4 col-sm-9 col-xs-12" required>
<select class="form-control" name="type_applicant"   required>
<option value="">------</option>
<option>FTW- PARTIAL</option>
<option>FTW- FULL PAYMENT</option>
<option>SIGN CONTRACT</option>
<option>DEPLOYED</option>
<option>OFFICE DEDUCTION</option>
<option>APPLICANT EXPENSES</option>
</select>
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Type</label>
<div class="col-md-4 col-sm-9 col-xs-12" readonly>
<select class="form-control" name="l_type"   required>
<option VALUE="Agent">Agent Commission</option>
</select>
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Currency</label>
<div class="col-md-4 col-sm-9 col-xs-12" required>
<select class="form-control" name="l_currency"   >
<option>PHP</option>
</select>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Staff </label>
<div class="col-md-4 col-sm-6 col-xs-12">
<select class="form-control" required  name="l_staff_id" required>
<option value="<?=$staffr['user_id']?>"> <?=$staffr['user_fullname']?> </option>
<option value="0">N/A </option>
<?php
$usertype1 = mysql_query("SELECT * FROM user ORDER BY user_fullname asc");
while($rows1=mysql_fetch_array($usertype1))
{
echo'<option value="'.$rows1["user_id"].'">  '.$rows1["user_fullname"].'</option>';
}
?>
</select>
</div>
</div>




<?php
if ($row1['agent_id']==0){$getme="";}
if ($row1['agent_id']==3){$getme="";}
if ($row1['agent_id']!=0){$getme="where applicant_source=".$row1['agent_id']."";}
?>



<div class="form-group" style="display:<?=$hide?>">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Applicant</label>
<div class="col-md-4 col-sm-6 col-xs-12">


<select class="selectpicker" single data-live-search="true" name="liq_app" >
<?php
echo'<option></option>
<option value="9999999">Others</option>
';

$applicant = mysql_query("SELECT * FROM applicant $getme ORDER BY applicant_date_applied asc");
while($rows12222=mysql_fetch_array($applicant))
{
echo'<option value="'.$rows12222["applicant_id"].'">'.$rows12222["applicant_last"].'  '.$rows12222["applicant_middle"].'  '.$rows12222["applicant_first"].'</option>';
}
?>
</select>





</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="l_amount" step="any"  required value="">
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Description</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="l_remarks" required value="">
</div>
</div>


<div class="form-group">
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="hidden" class="form-control" name="l_e_d" value="<?=$_GET['e_id']?>">
<button type="submit" class="btn btn-danger pull-right"  name="liq">Save</button>
</div>
</div>
</form>	

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



</br>
<h3>Liquidation </h3>

  <form action="" method="post">

<table  cellspacing="0" id="customers">
<thead>
<tr>
<th STYLE="width:2%"></th>

<th STYLE="width:2%">Ref #</th>
<th STYLE="width:3%">DATE</th>
<th STYLE="width:3%">Account Type</th>
<th STYLE="width:5%">Applicant </th>
<th STYLE="width:5%">Req.By</th>
<th STYLE="width:5%">Account Type</th>
<th STYLE="width:5%">Category</th>
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

while($row22222=mysql_fetch_array($app))
{
    
$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$row22222['liq_app']." ");
$rowp=mysql_fetch_array($applicant);

$USER = mysql_query("SELECT * FROM  user where user_id=".$row22222['l_staff_id']." ");
$rowu=mysql_fetch_array($USER);

$total1=$row22222['l_amount']+$total1;
$amt = number_format($row22222["l_amount"], 2);


echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$row22222['l_id']."' ></td> ";
echo'<td>000'.$row22222["l_e_d"].'</td>';
echo'<td>'; echo date('Y-M-d', strtotime($row22222['l_date']));echo'</td>';
echo'<td>'.$row22222['type_applicant'].'</td>';
echo'<td>'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$row['requestby'].'</td>';
echo'<td>'.$row22222["type_applicant"].'</td>';
echo'<td>'.$row22222["l_type"].'</td>';
echo'<td>'.$row22222["l_remarks"].'</td>';
echo'<td>'.$row22222["l_currency"].'</td>';
echo'<td>'.$amt.'</td>';
echo'</tr>';
}
$tot1 = number_format($total1, 2);
?>

<tr>
<td  colspan="10"  style="font-weight:bold;text-align:right;font-size:14px;color:red">Total Liquidation:</td>
<td  style="font-weight:bold;text-align:left;font-size:14px;color:red"> <?=$tot1;?> </td>
</TR>

</tbody>
</table>
</br></br>

<?php
if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){



?>


<div class="row">
<div class="col-6" style="width:100%,float:right">		

<a href="delete_methode_link" onclick="return confirm('Are you sure you want to Remove?');">
<input type="submit" value="Delete" name="delete_orders" style="width:130px;background:blue;padding:5px;margin-left:20px;margin-top:-1px;color:white">
</a>
</div>
</div>

<?php }  ?>
</form>		
		






