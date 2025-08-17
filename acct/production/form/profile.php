<form class="form-horizontal form-label-left input_mask" method="post" action="">	
					
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">DATE APPLIED</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="date" class="form-control" name="date_applied" value="<?=$row['date_applied']?>">
                        </div>
                      </div>
					 
					  
					<div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">FIRST NAME</label>
                       <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control" name="name" value="<?=$row['name']?>">
                        </div>
                      </div>
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">MIDDLE NAME</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control" name="mname" value="<?=$row['mname']?>">
                        </div>
                      </div>
					  
					  <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">LAST NAME</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control" name="lname" value="<?=$row['lname']?>">
                        </div>
                      </div>
					  
				
					  
					 <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Contact #</label>
                       <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control" name="contact" value="<?=$row['contact']?>">
                        </div>
                      </div>
					  
					  
					   <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Passport #</label>
                       <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" class="form-control" name="passport" value="<?=$row['passport']?>">
                        </div>
                      </div>
					  
					   <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">PPT Expired</label>
                       <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="date" class="form-control" name="ppt_expired" value="<?=$row['ppt_expired']?>">
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
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">SOURCE</label>
                       <div class="col-md-6 col-sm-6 col-xs-12">
								<select class="form-control"   name="agent_id" >
								<option value="<?=$row['agent_id']?>"><?=$row1['agent_first']?> <?=$row1['agent_last']?> </option>
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
						<div class="col-md-9 col-sm-6 col-xs-12">
							<input type="hidden" class="form-control" name="user" value="<?=$_SESSION['user_fullname']?>">
							<input type="hidden" class="form-control" name="app_id" value="<?=$_GET['appid']?>">
							<button type="submit" class="btn btn-danger pull-right"  name="update_profile">Save</button>
                        </div>
                      </div>
					  
					  
			</form>	