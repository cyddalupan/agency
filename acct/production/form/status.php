		<form class="form-horizontal form-label-left input_mask" method="post" action="">	

		<div class="form-group">
		<label class="control-label col-md-3 col-sm-3 col-xs-12">CODE</label>
		<div class="col-md-6 col-sm-6 col-xs-12">
		<input type="text" class="form-control" name="code" value="<?=$row['code']?>">
		</div>
		</div>
		
		
		<div class="form-group">
		<label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
		<div class="col-md-6 col-sm-6 col-xs-12">
		<select class="form-control" required  name="status">
		<option><?=$row['status']?></option>
		<option>Available</option>
		<option>Selected</option>
		<option>For Booking</option>
		<option>For Deployment</option>
		<option>Deployed</option>
		<option>Backout</option>
		<option>Repat</option>
		<option>Cancelled</option>
		<option>UNFIT</option>
		</select>
		</div>
		</div>


		<div class="form-group">
		<label class="control-label col-md-3 col-sm-3 col-xs-12">Sub Status</label>
		<div class="col-md-6 col-sm-6 col-xs-12">
		<select class="form-control"   name="sub_status">
		<option><?=$row['sub_status']?></option>
		<option>To Undergo Medical</option>
		<option>Medical Under Process</option>
		<option>Fit to Work</option>
		<option>For Insurance</option>
		<option>For Encoding Enjaz</option>
		<option>Waiting for job Offer</option>
		<option>For job offer Signing</option>
		<option>For  Offer Letter</option>
		<option>Incoming Contract</option>
		<option>With Contract</option>
		<option>Waiting for Contract Signing</option>
		<option>Contract Sign</option>
		<option>With labor Contract (Awaiting Visa)</option>
		<option>For Visa Applicantion</option>
		<option>Waiting For Visa</option>
		<option>Visa in Process</option>
		<option>Visa Approved</option>
		<option>Visa Stamping</option>
		<option>OEC FILING</option>
		<option>OWWA Training</option>
		<option>Unfit</option>
		<option>Canceled/backout</option>
		</select>
		</div>
		</div>


		<div class="form-group">
		<label class="control-label col-md-3 col-sm-3 col-xs-12">Status Date</label>
		<div class="col-md-6 col-sm-6 col-xs-12">
		<input type="date" class="form-control" name="status_date" value="<?=$row['status_date']?>" required>
		</div>
		</div>



		<div class="form-group">
		<label class="control-label col-md-3 col-sm-3 col-xs-12">COUNTRY</label>
		<div class="col-md-6 col-sm-6 col-xs-12">
		<select class="form-control" required  name="country">
		<option><?=$row['country']?></option>
		<option>KSA</option>
		<option>KUWIAT</option>
		<option>QATAR</option>
		<option>OMAN</option>
		</select>
		</div>
		</div>


		<div class="form-group">
		<label class="control-label col-md-3 col-sm-3 col-xs-12">Employer</label>
		<div class="col-md-6 col-sm-6 col-xs-12">
		<select class="form-control" required  name="counter_part" >
		<option value="<?=$row['counter_part']?>"><?=$row['counter_part']?></option>
		<?php
		$usertype = mysql_query("SELECT * FROM employer ORDER BY employer_name asc");

		while($rows=mysql_fetch_array($usertype))
		{
		echo'<option value="'.$rows["employer_id"].'">'.$rows["employer_name"].'</option>';
		}
		?>
		</select>
		</div>
		</div>


		<div class="form-group">
		<label class="control-label col-md-3 col-sm-3 col-xs-12">Sponsor Name</label>
		<div class="col-md-6 col-sm-6 col-xs-12">
		<input type="text" class="form-control" name="sponsor" value="<?=$row['sponsor']?>">
		</div>
		</div>

		<div class="form-group">
		<label class="control-label col-md-3 col-sm-3 col-xs-12">Sponsor Contact #</label>
		<div class="col-md-6 col-sm-6 col-xs-12">
		<input type="text" class="form-control" name="sponsor_con" value="<?=$row['sponsor_con']?>">
		</div>
		</div>




		<div class="form-group">
		<label class="control-label col-md-3 col-sm-3 col-xs-12">Remarks</label>
		<div class="col-md-8 col-sm-6 col-xs-12">
		<TEXTarea name="app_remakrs" class="col-md-8 col-sm-6 col-xs-12" required><?=$row['app_remakrs']?></textarea>
		</div>
		</div>


		<div class="form-group">
		<div class="col-md-9 col-sm-6 col-xs-12">
		<input type="hidden" class="form-control" name="user" value="<?=$_SESSION['user_fullname']?>">
		<input type="hidden" class="form-control" name="app_id" value="<?=$_GET['appid']?>">
		<button type="submit" class="btn btn-danger pull-right"  name="update_profile">Save</button>
		</div>
		</div>


		</form>	