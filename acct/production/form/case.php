	<form class="form-horizontal form-label-left input_mask" method="post" action="">	
					

					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">DATE HEARING</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="date" class="form-control" name="date_hearing" value="<?=$row['date_hearing']?>" >
                        </div>
                      </div>
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">TIME HEARING</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="time" class="form-control" name="timess" value="<?=$row['timess']?>" >
                        </div>
                      </div>
					
					  
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Applicant Name</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="text" class="form-control" name="applicant_name" value="<?=$row['applicant_name']?>">
                        </div>
                      </div>
					 
					 <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Case</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
						<textarea name="caso" class="col-md-9 col-sm-9 col-xs-12"><?=$row['caso']?></textarea>	
                        </div>
                      </div>
					  
					   <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Remarks</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
						<textarea name="remarks" class="col-md-9 col-sm-9 col-xs-12"> <?=$row['remarks']?></textarea>	
                        </div>
                      </div>
					  
					     <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Remarks</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
						<textarea name="remarks" class="col-md-9 col-sm-9 col-xs-12"> <?=$row['remarks']?></textarea>	
                        </div>
                      </div>
					  
					  
					    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">STATUS</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
								<select class="form-control" required  name="status">
								<option><?=$row['status']?></option>
								<option>LOW</option>
								<option>PRIOTIRY</option>
								<option>HIGH PRIORITY</option>
								<option>DELETED</option>
								
								</select>
                        </div>
                      </div>
					  	 
					  
					  
					  	  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">AGENT</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
								<select class="form-control" required  name="agent_id" required>
								<option value="<?=$row1['agent_id']?>"><?=$row1['agent_first']?> <?=$row1['agent_last']?></option>
								
								<?php
								$usertype = mysql_query("SELECT * FROM recruitment_agent ORDER BY agent_first asc");

								while($rows=mysql_fetch_array($usertype))
								{
								echo'<option value="'.$rows["agent_id"].'">'.$rows["agent_first"].' '.$rows["agent_last"].'</option>';
								}
								?>
								</select>
                        </div>
                      </div>
					  
					  
					   <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">NOTIFICATION DATE (FOLLOW UP)</label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                          <input type="date" class="form-control" name="noti" value="<?=$row['noti']?>" >
                        </div>
                      </div>
					  
					  
					  	  <div class="form-group">
						<div class="col-md-12 col-sm-9 col-xs-12">	
						<input type="hidden" class="form-control" name="case_id" value="<?=$_GET['case']?>">
							
							<button type="submit" class="btn btn-danger pull-right"  name="update_profile">Save</button>
                        </div>
                      </div>
					  
					  
			</form>		