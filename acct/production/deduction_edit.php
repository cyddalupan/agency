<?php include'inc/header1.php';
include'tracking.php';
include'inc/expenses.php';



//include'db.php';

if(isset($_POST['deduct_edit'])){
$app11 = mysql_query("UPDATE  deduction
SET 
deduction_date = '".$_POST['deduction_date']."'
,agent_id = ".$_POST['agent_id']."
,app_id = ".$_POST['app_id']."
,deduction_remarks = '".$_POST['deduction_remarks']."'
,deduction_amount = ".$_POST['deduction_amount']."

Where deduction_id=".$_GET['e_id']." ");
echo'<h2 style="color:red">DONE SAVE....</h2>';
}



$app = mysql_query("SELECT  deduction.*
,applicant.applicant_first
,applicant.applicant_middle
,applicant.applicant_last
FROM  deduction
LEFT JOIN  	applicant
ON deduction.app_id = applicant.applicant_id

 where deduction_id=".$_GET['e_id']." ");
$row=mysql_fetch_array($app);

$app1 = mysql_query("SELECT * FROM recruitment_agent where agent_id=".$row['agent_id']." ");
$row1=mysql_fetch_array($app1);

$staff = mysql_query("SELECT * FROM user where user_id=".$row['staff_id']." ");
$staffr=mysql_fetch_array($staff);


$sup = mysql_query("SELECT * FROM  suppliers where sup_id=".$row['sup_id']." ");
$sup1=mysql_fetch_array($sup);


$bank = mysql_query("SELECT * FROM  bank_accounts where bank_id=".$row['fund_source_id']." ");
$bank1=mysql_fetch_array($bank);

if($row["request_payment"]==0){$request='Pending';$idrequest=0;$rcolor='black';}
if($row["request_payment"]==1){$request='Approved';$idrequest=1;$rcolor='green';}
if($row["request_payment"]==2){$request='For Relassse';$idrequest=2;$rcolor='green';}
if($row["request_payment"]==3){$request='Released';$idrequest=3;$rcolor='green';}
if($row["request_payment"]==4){$request='Declined';$idrequest=4;$rcolor='red';}
if($row["request_payment"]==5){$request='Return Maker';$idrequest=5;$rcolor='red';}



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
<div class="col-md-12 col-sm-12 col-xs-12">    


		
</DIV>

<div class="clearfix"></div>

<div class="row" style="width:90%;margin:0 auto;margin-top:20px;margin-bottom:20px">
<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">

<div class="x_content">

<a href="<?=$actual_link;?>" style="color:Red;text-align:right">Refresh</a></br></br>



<table  cellspacing="0" id="customers">



</table>


<DIV style="clear:both;height:30px"></DIV>


<div class="" role="tabpanel" data-example-id="togglable-tabs">
<ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
<li role="presentation" class="active"><a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">Deduction Info</a>
</li>


</ul>
<div id="myTabContent" class="tab-content">
<div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">








<form class="form-horizontal form-label-left input_mask" method="post" action="">	

<div class="form-group">
<label class="control-label col-md-3 col-sm-3 col-xs-12">DATE REQUEST</label>
<div class="col-md-4 col-sm-6 col-xs-12">
<input type="date" class="form-control" name="deduction_date" value="<?=$row['deduction_date']?>" required <?=$test?>>
</div>
</div>

<div class="form-group" style="">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Agent </label>
<div class="col-md-4 col-sm-6 col-xs-12">
<select class="form-control"   name="agent_id" >
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
<label class="control-label col-md-3 col-sm-3 col-xs-12">Amount</label>
<div class="col-md-4 col-sm-9 col-xs-12">
<input type="number" class="form-control" name="deduction_amount" required step="any"  value="<?=$row['deduction_amount']?>" <?=$test?>>
</div>
</div>



<div class="form-group"  style="">
<label class="control-label col-md-3 col-sm-3 col-xs-12">Description</label>
<div class="col-md-4 col-sm-9 col-xs-12">

<textarea name="deduction_remarks" style="width:600px;height:300px"><?=$row['deduction_remarks']?></textarea>


</div>
</div>




<div class="form-group">
<div class="col-md-4 col-sm-6 col-xs-12">

<button type="submit" class="btn btn-danger pull-right"  name="deduct_edit">Save</button>
</div>
</div>


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



</body>
</html>