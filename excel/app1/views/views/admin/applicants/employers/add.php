<div class="modal-header">
	<button aria-hidden="true" data-dismiss="modal" class="bootbox-close-button close" type="button">×</button>
    <h4 class="modal-title">New Employer</h4>
</div>

<form method="post" class="form" role="form" action="<?php echo site_url( 'admin/employers/add' ); ?>">
<div class="modal-body">
    <div class="bootbox-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                	<label for="employer[country]">Preferred country</label>
                    <select name="employer[country]" style="width:100%" required>
                    	<option value="" >-- Select --</option>
                        <?php foreach ( $countries as $country ): ?>
                        <option value="<?php echo $country['country_id']; ?>" ><?php echo $country['country_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                	<label for="employer[number]">Employer number</label>
                    <input name="employer[number]" type="text" placeholder="" class="form-control" value="<?php echo isset( $post['employer']['number'] ) ? $post['employer']['number'] : ''; ?>" style="text-transform:uppercase"  required>
                </div>
                <div class="form-group">
                	<label for="employer[name]">Employer / Agency name</label>
                    <input name="employer[name]" type="text" placeholder="" class="form-control" value="<?php echo isset( $post['employer']['name'] ) ? $post['employer']['name'] : ''; ?>">
                </div>
                <div class="form-group">
                	<label for="employer[address]">Address <small><em>(optional)</em></small></label>
                    <input name="employer[address]" type="text" placeholder="" class="form-control" value="<?php echo isset( $post['employer']['address'] ) ? $post['employer']['address'] : ''; ?>">
                </div>
                <div class="form-group">
                	<label for="employer[email]">Email address <small><em>(optional)</em></small></label>
                    <input name="employer[email]" type="text" placeholder="example@domain.com" class="form-control" value="<?php echo isset( $post['employer']['email'] ) ? $post['employer']['email'] : ''; ?>" style="text-transform:lowercase">
                </div>
                <div class="form-group">
                	<label for="employer[contact_person]">Contact person</label>
                    <input name="employer[contact_person]" type="text" placeholder="" class="form-control" value="<?php echo isset( $post['employer']['contact_person'] ) ? $post['employer']['contact_person'] : ''; ?>">
                </div>
                <div class="form-group">
                	<label for="employer[contact]">Contact number</label>
                    <input name="employer[contact]" type="text" placeholder="" class="form-control" value="<?php echo isset( $post['employer']['contact'] ) ? $post['employer']['contact'] : ''; ?>">
                </div>
                
                <hr class="wide" />
                <div class="form-group">
                	<label for="employer[user][name]">User name</label>
                    <input name="employer[user][name]" type="text" placeholder="" class="form-control" value="<?php echo isset( $post['employer']['user']['name'] ) ? $post['employer']['user']['name'] : ''; ?>" style="text-transform:lowercase">
                </div>
                <div class="form-group">
                	<label for="employer[user][password]">Password</label>
                    <input name="employer[user][password]" type="password" placeholder="" class="form-control"  required>
                </div>
                <div class="form-group">
                	<label for="employer[user][password2]">Confirm password</label>
                    <input name="employer[user][password2]" type="password" placeholder="" class="form-control" required>
                </div>
                <hr class="wide" />
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-6"> 
                            <label> Source (Marketing Agency):</label>
                            <select name="employer[source_agency]" style="width:100%">
                                <option value="0" >----</option>
                                <?php foreach ( $agencies as $agency ): ?>
                                <option value="<?php echo $agency['agency_id']; ?>" ><?php echo $agency['agency_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <br><br>
                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <label>Commission (%)</label>                                        
                                    <div class="input-group">
                                      <input name="employer[source_agency_commission]" type="text" class="form-control" placeholder="0" min="0" max="100">
                                      <span class="input-group-addon">%</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label>From</label>
                                    <select name="employer[source_agency_commission_from]" class="form-control">
                                        <option value="Placement Fee" selected="selected">Placement Fee</option>
                                        <option value="Service Fee">Service Fee</option>
                                    </select>
                                </div>
                            </div>
                            
                        </div>
                        <div class="col-sm-6" style="border-left:1px dashed #999">
                            <label> Source (Marketing Agent):</label>
                            <select name="employer[source_agent]" style="width:100%">
                                <option value="0" >----</option>
                                <?php foreach ( $agents as $agent ): ?>
                                <option value="<?php echo $agent['agent_id']; ?>" ><?php echo $agent['agent_first'].' '.$agent['agent_last']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            
                            <br><br>
                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <label>Commission (%)</label>                                        
                                    <div class="input-group">
                                      <input name="employer[source_agent_commission]" type="text" class="form-control" placeholder="0" min="0" max="100">
                                      <span class="input-group-addon">%</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label>From</label>
                                    <select name="employer[source_agent_commission_from]" class="form-control">
                                        <option value="Placement Fee" selected="selected">Placement Fee</option>
                                        <option value="Service Fee">Service Fee</option>
                                    </select> 
                                </div>
                            </div>
                             
                        </div>
                    </div>                             	
                </div>
                <hr class="wide" />
                <div class="form-group">
                	<label for="employer[remarks]">Remarks</label>
                    <textarea name="employer[remarks]" rows="2" placeholder="" class="form-control"><?php echo isset( $post['employer']['remarks'] ) ? $post['employer']['remarks'] : ''; ?></textarea>
                </div>
                
            </div>
        </div> 
        
    </div>
</div>
<div class="modal-footer">
	<button class="btn btn-default" data-dismiss="modal"  type="button">Cancel</button>
    <button class="btn btn-primary" type="submit">Add</button>
</div>
</form>