<form class="form-horizontal form-label-left input_mask" method="post" action=""class="col-md-6 col-sm-6 col-xs-12">	
					
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">DATE REFERRAL</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="date" class="form-control" name="date_refferal" value="<?=$row2['date_refferal']?>">
                        </div>
                      </div>
					
					  
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Medical Date</label>
                       <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="date" class="form-control" name="date_medical" value="<?=$row2['date_medical']?>">
                        </div>
                      </div>
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Expired Date</label>
                       <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="date" class="form-control" name="med_expired" value="<?=$row2['med_expired']?>">
                        </div>
                      </div>
					  
					
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Result</label>
                       <div class="col-md-6 col-sm-6 col-xs-12">
								<select class="form-control"   name="result">
								<option><?=$row['result']?></option>
								<option>PENDING</option>
								<option>FIT TO WORK</option>
								</select>
                        </div>
                      </div>
					  
					    
						<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Vaccine Date</label>
                       <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="date" class="form-control" name="vaccine" value="<?=$row2['vaccine']?>">
                        </div>
                      </div>
						
					 <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Remarks</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control" name="remarks" value="<?=$row2['remarks']?>">
                        </div>
                      </div>
					  
					  
					  
					  
					  	  <div class="form-group">
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input type="hidden" class="form-control" name="user" value="<?=$_SESSION['user_fullname']?>">
							<input type="hidden" class="form-control" name="app_id" value="<?=$_GET['appid']?>">
							<button type="submit" class="btn btn-danger pull-right"  name="update_medical">Save</button>
                        </div>
                      </div>
					  
					  
			</form>	