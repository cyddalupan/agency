<?php
if($_SESSION['user_type']==4 || $_SESSION['user_type']==2 ){
$test='';
$test1='';
}

else{
$test='readonly';
$test1='disabled';
}


if($row['branch_type']==0){$branchme="N/A";}
if($row['branch_type']==7){$branchme="ILO ILO BRANCH";}
if($row['branch_type']==8){$branchme="CDO BRANCH";}
if($row['branch_type']==9){$branchme="DAVAO BRANCH";}
if($row['branch_type']==10){$branchme="BINANGON BRANCH";}
if($row['branch_type']==11){$branchme="CAVITE BRANCH";}
if($row['branch_type']==14){$branchme="MAIN MANILA BRANCH";}
if($row['branch_type']==15){$branchme="TACLOBAN BRANCH";}
if($row['branch_type']==13){$branchme="CEBU BRANCH";}



if($row['country']==5){$countryme="KUW";}
if($row['country']==7){$countryme="KSA";}
if($row['country']==11){$countryme="QATAR";}
if($row['country']==12){$countryme="DUBAI";}


?>


<form class="form-horizontal form-label-left input_mask" method="post" action="">	

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
<div class="col-md-4 col-sm-6 col-xs-12">
<select class="form-control" name="request_payment" required>
<option value="<?=$idrequest?>"><?=$request?></option>
<option value="0">Pending</option>

<option value="1" >Approved</option>
<option value="2">For Release</option>
<option value="3">Released</option>
<option value="4">Cancelled</option>

</select>
</div>
</div>





<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12" STYLE="COLOR:RED">Bank & accounts (CASH OUT)</label>
<div class="col-md-4 col-sm-6 col-xs-12">

<?php
echo'<select class="form-control" name="fund_source_id"  required  '.$test1.'>';
echo'<option value="'.$row["fund_source_id"].'">ACCOUNT ID: '.$row["fund_source_id"].'</option>';
$bank = mysql_query("SELECT * FROM bank_accounts ORDER BY bank_name asc");
while($bank1=mysql_fetch_array($bank))
{
echo'<option value="'.$bank1["bank_id"].'">  '.$bank1["bank_name"].' - Account #: '.$bank1["bank_num"].' ('.$bank1["bank_currency"].')-ACCOUNT ID: '.$bank1["bank_id"].' </option>';
}
ECHO'</select>';
?>

</div>
</div>





<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">FRA</label>
<div class="col-md-4 col-sm-6 col-xs-12">

<?php
echo'<select class="form-control" name="emp_id" onchange="showapplicant(this.value)"  '.$test1.'>';
echo'<option>'.$row["emp_id"].'</option>
<option value="0">N/A</option>';
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
<label class="control-label col-md-3 col-sm-3 col-xs-12">DATE REQUEST</label>
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="date" class="form-control" name="date_ad" value="<?=$row['date_ad']?>" required <?=$test?>>
</div>
</div>

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">DUE DATE </label>
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="date" class="form-control" name="duedate" value="<?=$row['duedate']?>" <?=$test?>>
</div>
</div>


<div class="form-group"  style="">
<label class="control-label col-md-3 col-sm-3 col-xs-12">REF #</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="ref"  value="<?=$row['ref']?>" <?=$test?>>
</div>
</div>







<div class="form-group" style="">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Applicant </label>
<div class="col-md-4 col-sm-6 col-xs-12">
<select class="selectpicker" single data-live-search="true" name="app_id" >
<?php
echo'<option value="'.$row["app_id"].'">'.$row["applicant_last"].' 
'.$row["applicant_first"].' -'.$row["app_id"].'</option>';

$applicant = mysql_query("SELECT * FROM applicant 
LEFT JOIN  	recruitment_agent
ON applicant.applicant_source =recruitment_agent.agent_id
ORDER BY applicant_last desc");
while($rows12222=mysql_fetch_array($applicant))
{
echo'<option value="'.$rows12222["applicant_id"].'">'.$rows12222["applicant_last"].'  '.$rows12222["applicant_middle"].'  '.$rows12222["applicant_first"].' '.$rows12222["applicant_first"].'</option>';
}
?>
</select>
</div>
</div>




<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12" style="color:red">Cient / Suppliers</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<?php
echo'<select class="form-control" name="sup_id" required  '.$test1.'>
<option>'.$row["sup_id"].'</option>
<option value=0>N/A</option>
';

$usertype = mysql_query("SELECT * FROM  suppliers where sup_type='Clients' ORDER BY name asc");
echo'<optgroup label="Clients">';
while($rows=mysql_fetch_array($usertype))
{
echo'<option value="'.$rows["sup_id"].'"> '.$rows["name"].'</option>';
}
echo'</optgroup>';

$sup = mysql_query("SELECT * FROM  suppliers where sup_type='Suppliers' ORDER BY name asc");
echo'<optgroup label="Suppliers">';
while($sup1=mysql_fetch_array($sup))
{
echo'<option value="'.$sup1["sup_id"].'"> '.$sup1["name"].'</option>';
}
echo'</optgroup>';

ECHO'</select>';
?>

</div>
</div>


<div class="form-group" style="">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Agent </label>
<div class="col-md-4 col-sm-6 col-xs-12">
<select class="form-control"   name="agent_id" <?=$test1?>>
<option value="<?=$row1['agent_id']?>"><?=$row1['agent_first']?>  <?=$row1['agent_last']?> </option>
<option value=0>N/A</option>
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
<label class="control-label col-md-3 col-sm-3 col-xs-12">Type of Payment</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<select class="form-control" name="type_payment" <?=$test1?>>
<option><?=$row['type_payment']?></option>
<option>FulL Payment</option>
<option>Cash Advance</option>
<option>Partial</option>
<option>Others</option>
</select>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Account</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<?php echo'
<select class="form-control" name="account" required  '.$test1.'>
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
<select class="form-control" name="currency"   <?=$test1?>>
<option><?=$row['currency']?></option>
<option>PHP</option>
<option>USD</option>
</select>
</div>
</div>


<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="amount" required step="any"  value="<?=$row['amount']?>" <?=$test?>>
</div>
</div>



<div class="form-group"  style="">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Description</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="text" class="form-control" name="description"  value="<?=$row['description']?>">
</div>
</div>







<div class="form-group">
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="hidden" class="form-control" name="e_id" value="<?=$_GET['e_id']?>">
<button type="submit" class="btn btn-danger pull-right"  name="update_profile">Save</button>
</div>
</div>





</form>	
