

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/css/bootstrap-select.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/js/bootstrap-select.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>


<form class="form-horizontal form-label-left input_mask" method="post" action="">	

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">DATE TRANSACTION</label>
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="date" class="form-control" name="l_date" value="<?=$row['date']?>" readonly>
</div>
</div>


<?php
if ($row1['agent_id']==0){$getme="";}
if ($row1['agent_id']==3){$getme="";}
if ($row1['agent_id']!=0){$getme="where applicant_employer=".$row['emp_id']."";}
?>



<div class="form-group" style="display:<?=$hide?>">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Applicant</label>
<div class="col-md-4 col-sm-6 col-xs-12">


<select class="selectpicker" single data-live-search="true" name="app_id" >
<?php
echo'<option></option>
<option value="9999999">Others</option>
';

$applicant = mysql_query("SELECT * FROM applicant $getme ORDER BY applicant_date_applied asc");
while($rows12222=mysql_fetch_array($applicant))
{
echo'<option value="'.$rows12222["applicant_id"].'">  '.$rows12222["applicant_first"].' '.$rows12222["applicant_middle"].'  '.$rows12222["applicant_last"].'  </option>';
}
?>
</select>





</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="fra_amount" step="any"  required value="">
</div>
</div>



<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Description</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="fra_remarks" required value="">
</div>
</div>

<div class="form-group">
<div class="col-md-4 col-sm-6 col-xs-12">

<input type="hidden" class="form-control" name="fra_tr_id" value="<?=$_GET['trid']?>">
<input type="hidden" class="form-control" name="fra_user" value="<?=$_SESSION['user_fullname']?>">
<input type="hidden" class="form-control" name="emp_id" value="<?=$row['emp_id']?>">



<button type="submit" class="btn btn-danger pull-right"  name="liqfra">Save</button>
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
<th STYLE="width:3%">DATE</th>
<th STYLE="width:5%">Applicant </th>
<th STYLE="width:5%">Amount</th>
<th STYLE="width:5%">Description</th>
<th STYLE="width:5%">User</th>
</tr>
</thead>
<tbody>
<?php	



$app = mysql_query("SELECT * FROM liq_fra where fra_tr_id=".$_GET['trid']."
order by fra_id desc");

while($row22222=mysql_fetch_array($app))
{
    
$applicant = mysql_query("SELECT * FROM applicant where applicant_id=".$row22222['app_id']." ");
$rowp=mysql_fetch_array($applicant);

echo'<tr>';
echo "<td><input type='checkbox' name='delall[]' value='".$row22222['fra_id']."' ></td> ";
echo'<td>'; echo date('Y-M-d', strtotime($row22222['l_date']));echo'</td>';
echo'<td>'.$rowp["applicant_first"].' '.$rowp["applicant_last"].'</td>';
echo'<td>'.$row22222["fra_amount"].'</td>';
echo'<td>'.$row22222["fra_remarks"].'</td>';
echo'<td>'.$row22222["fra_user"].'</td>';

echo'</tr>';
}
?>



</tbody>
</table>
</br></br>





<div class="row">
<div class="col-5" style="width:100%,float:right">		

<a href="delete_methode_link" onclick="return confirm('Are you sure you want to Remove no BACK UP after deleted');">
<input type="submit" value="Delete All" name="delete_ordersfra" style="width:130px;background:red;padding:5px;margin-left:20px;margin-top:-1px;color:white">
</a>
</div>



</div>



</form>		
		




</form> 



