<div class="clearfix" style="clear:both;height:40px"></div>
<form class="" method="post" action=""class="col-md-12 col-sm-12 col-xs-12" style="border:0px solid black">	

<div class="clearfix" style="clear:both;height:10px"></div>

<h3 style="margin-left:40px;color:blue;font-size:18px">Job Offer</h3>


<div class="col-sm-2">
<div class="form-group">

<label>J.O Received </label>
<span class="input-icon icon-right">
<div class="input-group">
<input name="requirement_job_received" type="date" class="form-control date-picker" value="<?php echo $reqrow['requirement_job_received']; ?>">
</div>
</span>
</div>
</div>

<div class="col-sm-2">
<div class="form-group">
<label>J.O Accepted </label>
<span class="input-icon icon-right">
<div class="input-group">
<input name="requirement_job_accepted" type="date" class="form-control" value="<?php echo $reqrow['requirement_job_accepted']; ?>">
</div>
</span>
</div>
</div>

<div class="col-sm-2">
<div class="form-group">
<label>Offer Letter</label>
<input type="text" name="offer_letter" class="form-control" value="<?php echo $reqrow['offer_letter']; ?>">
</div>
</div>

<div class="col-sm-2">

<div class="form-group">
<label>Offer Salary</label>
<input type="text" name="requirement_offer_salary" class="form-control"  value="<?php echo $reqrow['requirement_offer_salary']; ?>" >
</div>
</div>
<div class="clearfix" style="clear:both;height:40px"></div>

<h3 style="margin-left:40px;color:blue;font-size:18px">Visa</h3>

<div class="col-sm-1">
<div class="checkbox">

<label>Visa</label>
<input type="text" name="requirement_visa" class="form-control" value="<?php echo $reqrow['requirement_visa']; ?>">
</div>
</div>



<div class="col-sm-2">
<div class="form-group">
<label>Visa #</label>
<input type="text" name="requirement_visa_no" class="form-control" value="<?php echo $reqrow['requirement_visa_no']; ?>">
</div>
</div>



<div class="col-sm-2">

<div class="form-group">
<label>Category</label>
<input type="text" name="requirement_visa_category" class="form-control" placeholder="" value="<?php echo $reqrow['requirement_visa_category']; ?>">
</div>
</div>



<div class="col-sm-2">
<div class="form-group">
<label>VISA Date</label>
<span class="input-icon icon-right">
<div class="input-group">
<input name="requirement_visa_date" type="date"  class="form-control date-picker" value="<?php echo $reqrow['requirement_visa_date'];?>">
</div>
</span>
</div>
</div>

<div class="col-sm-2 date-release-col">
<div class="form-group">
<label>Date release</label>
<span class="input-icon icon-right">
<div class="input-group">
<input name="requirement_visa_release_date" type="date"  class="form-control date-picker"  value="<?php echo $reqrow['requirement_visa_release_date']; ?>">
</div>
</span>
</div>
</div>

<div class="col-sm-2">
<div class="form-group">
<label>Date expired</label>
<span class="input-icon icon-right">
<div class="input-group">
<input name="requirement_visa_expiration" type="date" class="form-control visa-date-expired" value="<?php echo  $reqrow['requirement_visa_expiration']; ?>">
</div>
</span>
</div>
</div>


<div class="clearfix" style="clear:both;height:40px"></div>
<h3 style="margin-left:40px;color:blue;font-size:18px">Contract</h3>
<div class="col-sm-3">
<div class="form-group">
<label>Contract Received</label>
<span class="input-icon icon-right">
<div class="input-group">
<input name="requirement_contract" type="date"  class="form-control date-picker" value="<?php echo $reqrow['requirement_contract']; ?>">
</div>
</span>
</div>
</div>



<div class="col-sm-3 coordination-col">
<div class="form-group">
<label>Contract Sign</label>
<input type="date" name="requirement_remarks" class="form-control"  value="<?php echo $reqrow['requirement_remarks']; ?>">
</div>
</div>


<div class="clearfix" style="clear:both;height:40px"></div>
<h3 style="margin-left:40px;color:blue;font-size:18px">OEC Information</h3>
<div class="col-sm-2">
<div class="form-group">
<label>OEC #</label>
<input type="text" name="requirement_oec_number" class="form-control" placeholder="" value="<?php echo $reqrow['requirement_oec_number']; ?>">
</div>
</div>

<div class="col-sm-2">
<div class="form-group">
<label>OEC submission</label>
<span class="input-icon icon-right">
<div class="input-group">
<input name="requirement_oec_submission_date" type="date" class="form-control date-picker" value="<?php echo $reqrow['requirement_oec_submission_date']; ?>">
</div>
</span>
</div>
</div>

<div class="col-sm-2">
<div class="form-group">
<label>OEC release</label>
<span class="input-icon icon-right">
<div class="input-group">
<input name="requirement_oec_release_date" type="date"  class="form-control date-picker" value="<?php echo $reqrow['requirement_oec_release_date']; ?>">
</div>
</span>
</div>
</div>

<div class="col-sm-2">
<div class="form-group">
<label>RFP</label>
<input type="text" name="applicant_requirement_rfp" class="form-control" placeholder="" value="<?php echo $reqrow['applicant_requirement_rfp']; ?>">
</div>
</div>



<div class="clearfix" style="clear:both;height:40px"></div>
<h3 style="margin-left:40px;color:blue;font-size:18px">E Code</h3>
<div class="col-sm-2">
<div class="form-group">
<label>ECODE</label>
<input type="text" name="applicant_requirement_ecode" class="form-control"  value="<?php echo $reqrow['applicant_requirement_ecode']; ?>">
</div>
</div>
<div class="col-sm-2 paid-col">
<div class="form-group">
<label>PAID</label>
<input type="text" name="applicant_requirement_paid" class="form-control"  value="<?php echo $reqrow['applicant_requirement_paid']; ?>">
</div>
</div>










<div class="clearfix" style="clear:both;height:30px"></div>

<div class="form-group">
<div class="col-md-10 col-sm-12 col-xs-12">
<input type="hidden" class="form-control" name="user" value="<?=$_SESSION['user_fullname']?>">
<input type="hidden" class="form-control" name="app_id" value="<?=$_GET['appid']?>">
<button type="submit" class="btn btn-danger pull-right"  name="update_process">Save</button>
</div
</div>
<div class="clearfix" style="clear:both;height:30px"></div>


</form>	