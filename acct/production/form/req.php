<div class="clearfix" style="clear:both;height:20px"></div>
<form class="" method="post" action=""class="col-md-12 col-sm-12 col-xs-12" style="border:0px solid black">	
<div class="col-sm-1">

<div class="form-group">

<label>Info sheet</label>
<input type="text" name="certificate_info_sheet" class="form-control"  value="<?=$certrow['certificate_info_sheet']?>">

</div>
</div>

<div class="col-sm-2">
<div class="form-group">
<label>PEOS</label>
<input type="text" name="peos" class="form-control"  value="<?=$certrow['peos']?>">
</div>
</div>


<div class="col-sm-2">
<div class="form-group">
<label>BIOMETRICS</label>
<input type="text" name="biometrics" class="form-control"  value="<?=$certrow['biometrics']?>">
</div>
</div>


<div class="col-sm-2">
<div class="form-group">
<label>Insurance</label>
<input type="text" name="certificate_insurance" class="form-control" value="<?=$certrow['certificate_insurance']?>">
</div>
</div>




<div class="col-sm-2">
<div class="form-group">
<label>Insurance No.</label>
<input type="text" name="insurance_no" class="form-control"  value="<?=$certrow['insurance_no']?>">
</div>
</div>

<div class="clearfix" style="clear:both;height:25px"></div>

<div class="col-sm-1">
<div class="form-group">
<label>TESDA </label>
<input name="certificate_tesda" type="text"  class="form-control date-picker" value="<?=$certrow['certificate_tesda']?>">
</div>
</div>

<div class="col-sm-2">
<div class="form-group">
<label>TESDA DATE</label>
<input name="certificate_tesda_date" type="date"  class="form-control date-picker" value="<?=$certrow['certificate_tesda_date']?>">
</div>
</div>

<div class="col-sm-2">

<div class="form-group">

<label>Pre-Departure PT result</label> 

<select name="certificate_pt_result" class="form-control"  style="width:100%">
<option value="">-- Select --</option>
<option value="NEGATIVE" <?php echo $certrow['certificate_pt_result'] == 'NEGATIVE' ? 'selected' : '';  ?>>NEGATIVE</option>
<option value="POSITIVE" <?php echo $certrow['certificate_pt_result'] == 'POSITIVE' ? 'selected' : '';  ?>>POSITIVE</option>
</select>
</div>
</div>

<div class="col-sm-2">
<div class="form-group">
<label>PT date result</label>
<span class="input-icon icon-right">
<div class="input-group">
<input name="certificate_pt_result_date" type="date"  class="form-control date-picker" value="<?=$certrow['certificate_pt_result_date']?>">
</div>
</span>
</div>
</div>

<div class="col-sm-2">
<div class="form-group">
<label>VACCINE</label>
<input type="text" name="certificate_mmr" class="form-control" placeholder="" value="<?=$certrow['certificate_mmr'] ?>">
</div>
</div>

<div class="clearfix" style="clear:both;height:20px"></div>



<div class="certrow">
<div class="col-sm-1 philhealth-review-form-group">
<div class="form-group">

<label>Philhealth</label>
<input type="text" name="certificate_philhealth" class="form-control" placeholder="" value="<?=$certrow['certificate_philhealth'] ?>">

</div>
</div>
<div class="col-sm-2 pag-ibig-review-form-group">
<div class="form-group">

<label>PAG-IBIG</label>
<input type="text" name="certificate_m1b" class="form-control" placeholder="" value="<?=$certrow['certificate_m1b'] ?>">
</div>
</div>	

<div class="col-sm-2">
<div class="form-group">

<label>Info sheet</label>
<input type="text" name="certificate_info_sheet" class="form-control" placeholder="" value="<?=$certrow['certificate_info_sheet'] ?>">
</div>
</div>


<div class="col-sm-2">
<div class="form-group">

<label>PDOS</label>
<input type="text" name="certificate_pdos" class="form-control" placeholder="" value="<?=$certrow['certificate_pdos'] ?>">
</div>
</div>


<div class="col-sm-2">
<div class="form-group">
<label>PDOS DATE</label>
<input name="certificate_pdos_date" type="date"  class="form-control date-picker"  value="<?=$certrow['certificate_pdos_date']?>">
</div>
</div>


<div class="dashed"></div>
<div class="clearfix" style="clear:both;height:20px"></div>


<div class="col-sm-1 owwa-col">
<div class="form-group">
<label>OWWA</label>
<input type="text" name="certificate_owwa" class="form-control" placeholder="" value="<?=$certrow['certificate_owwa'] ?>">
</div>
</div>

<div class="col-sm-2 owwa-from-col">
<div class="form-group">
<label>OWWA FROM</label>
<input type="date" name="certificate_owwa_from" class="form-control" placeholder="" value="<?=$certrow['certificate_owwa_from']?>">
</div>
</div>

<div class="col-sm-2 owwa-to-col">
<div class="form-group">
<label>OWWA TO</label>
<input type="date" name="certificate_owwa_to" class="form-control" placeholder="" value="<?=$certcertrow['certificate_owwa_to']?>">
</div>
</div>




<div class="clearfix" style="clear:both;height:30px"></div>


<div class="form-group">
<div class="col-md-10 col-sm-12 col-xs-12">
<input type="hidden" class="form-control" name="user" value="<?=$_SESSION['user_fullname']?>">
<input type="hidden" class="form-control" name="app_id" value="<?=$_GET['appid']?>">
<button type="submit" class="btn btn-danger pull-right"  name="update_requirement">Save</button>
</div>
</div>
</div>
<div class="clearfix" style="clear:both;height:30px"></div>

</form>	